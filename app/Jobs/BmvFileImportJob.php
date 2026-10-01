<?php

namespace App\Jobs;

use Illuminate\Support\Facades\Storage;

class BmvFileImportJob extends ProductImportJob
{
    // BMV's own column headers -> the canonical keys ProductImportJob::groupRows() reads.
    // "Colour" and "Sizes" are left as real attributes (colour -> attribute1, size ->
    // attribute2) so each SKU imports as one variable product with a colour variant and a
    // size sub-variant per row, instead of one standalone product per row — "Attache Code"
    // (verified 1:1 with SKU+Colour, constant across every size row of that colour) makes
    // this reliable without guessing colour abbreviations out of the description text.
    const HEADER_RENAME = [
        'attache_description' => 'item_name',
        'price_1'             => 'default_price',
        'quantity'            => 'inventory',
        'sizes'               => 'size',
    ];

    protected function normalizeHeaders(array $headers): array
    {
        $slugs = parent::normalizeHeaders($headers);

        return array_map(fn($slug) => self::HEADER_RENAME[$slug] ?? $slug, $slugs);
    }

    protected function readRows(): array
    {
        $rows = parent::readRows();

        $titles = $this->buildSkuTitles();

        foreach ($rows as &$row) {
            $sku = trim((string) ($row['sku'] ?? ''));
            $row['group_name'] = $titles[$sku] ?? trim((string) ($row['item_name'] ?? ''));
        }
        unset($row);

        return $rows;
    }

    // Scanned across the WHOLE file every chunk (not just this chunk's assigned line
    // range) — a SKU's colour/size rows can be split across different chunks, and every
    // one of them must resolve to the identical title or Products::firstOrNew() (keyed on
    // title) would silently fork one style into several products.
    //
    // Title = the word tokens every row of that SKU has in common (Attache Description
    // always ends in "... <colour> <size>", so the shared prefix is exactly the base style
    // name) — mechanical, not a guess at BMV's colour abbreviations. Where two distinct
    // SKUs still end up with the identical title (e.g. two accessory SKUs both just named
    // "A4 COMPENDIUM" with no colour/size to tell them apart, or a "(Y)" SKU whose rows are
    // byte-identical to its adult counterpart), the SKU is appended so neither is dropped.
    protected function buildSkuTitles(): array
    {
        if (!Storage::disk('local')->exists($this->path)) {
            return [];
        }

        $handle = fopen(Storage::disk('local')->path($this->path), 'r');
        if (!$handle) {
            return [];
        }

        $headers = [];
        $lineNumber = 0;
        $prefixTokens = [];

        while (($raw = fgetcsv($handle, 0, ',')) !== false) {
            $lineNumber++;

            if ($lineNumber === 1) {
                $headers = $this->normalizeHeaders($raw);
                continue;
            }

            $mapped = $this->mapRowToHeaders($headers, $raw);
            $sku    = trim((string) ($mapped['sku'] ?? ''));
            $name   = trim((string) ($mapped['item_name'] ?? ''));

            if ($sku === '' && $name === '') continue;

            $tokens = $name === '' ? [] : preg_split('/\s+/', $name);

            $prefixTokens[$sku] = array_key_exists($sku, $prefixTokens)
                ? $this->commonPrefix($prefixTokens[$sku], $tokens)
                : $tokens;
        }

        fclose($handle);

        $titles = [];
        foreach ($prefixTokens as $sku => $tokens) {
            $titles[$sku] = trim(implode(' ', $tokens));
        }

        $seenCount = array_count_values($titles);
        foreach ($titles as $sku => $title) {
            if ($title === '' || $seenCount[$title] > 1) {
                $titles[$sku] = $title !== '' ? "{$title} ({$sku})" : "({$sku})";
            }
        }

        return $titles;
    }

    protected function commonPrefix(array $a, array $b): array
    {
        $len = min(count($a), count($b));
        $result = [];

        for ($i = 0; $i < $len; $i++) {
            if ($a[$i] !== $b[$i]) break;
            $result[] = $a[$i];
        }

        return $result;
    }
}
