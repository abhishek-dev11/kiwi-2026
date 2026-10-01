<?php

namespace App\Jobs;

use Illuminate\Support\Facades\Storage;
use PhpOffice\PhpSpreadsheet\IOFactory;

class BannersInkFileImportJob extends ProductImportJob
{
    // The file has no stock/quantity column at all — 99 (not the parent's generic 999)
    // is the agreed in-stock default for Banners Ink specifically.
    protected int $defaultQuantity = 99;

    // Banners Ink's own column headers -> the canonical keys ProductImportJob::groupRows()
    // reads. Left-hand keys are what parent::normalizeHeaders() slugifies the raw headers
    // down to. "Main Category" and "Description" already slugify to the canonical
    // 'main_category' / 'description' keys as-is — no rename needed.
    const HEADER_RENAME = [
        'item_nmae'        => 'item_name',          // "Item Nmae" (typo in the source file)
        'sub_category'     => 'sub_sub_category',    // Sub Category      -> mid level
        'sub_sub_category' => 'sub_sub_category_2',  // Sub Sub Category  -> bottom level
        // Sub Sub Sub Category already slugifies to 'sub_sub_sub_category', which matches
        // the parent's fourth-level key as-is — no rename needed.
        'price_1'          => 'default_price',
    ];

    // Position of the main image URL column (0-based, i.e. spreadsheet column W). Its
    // header cell is blank in the raw file, so — unlike every other column above — it
    // can't be reached by the text-based HEADER_RENAME map and needs a direct index fix.
    const IMAGE_URL_COLUMN_INDEX = 22;

    protected function normalizeHeaders(array $headers): array
    {
        $slugs = parent::normalizeHeaders($headers);
        $slugs = array_map(fn($slug) => self::HEADER_RENAME[$slug] ?? $slug, $slugs);

        if (array_key_exists(self::IMAGE_URL_COLUMN_INDEX, $slugs)) {
            $slugs[self::IMAGE_URL_COLUMN_INDEX] = 'image_1';
        }

        return $slugs;
    }

    protected function readRows(): array
    {
        $rows = parent::readRows();

        // "If Price_1 Column is not like valid value then skip those product" — price_1
        // maps to 'default_price'; ~16 rows in the real file carry "POA" (price on
        // application) instead of a number. A non-numeric or non-positive value means
        // there's no real sellable price, so drop the row entirely rather than importing
        // it at $0.
        $rows = array_values(array_filter($rows, function ($row) {
            $price = str_replace([',', '$'], '', trim((string) ($row['default_price'] ?? '')));
            return is_numeric($price) && (float) $price > 0;
        }));

        // The parent's groupRows() groups rows AND derives products.title from the same
        // string ('item_name', since we never set a separate 'group_name') — but "Item
        // Nmae" holds a full marketing paragraph for most rows (400+ characters), which
        // overflows products.title (varchar(255)); MySQL rejects the row and the
        // per-product try/catch in ProductImportJob::processChunk() silently swallows it
        // (product just never appears). Grouping/counting below is done on the FULL,
        // untouched text first (so two genuinely different products never merge just
        // because their clipped titles happen to collide), then every row is rewritten
        // to a disambiguated, DB-safe title. The untouched full text still lives in
        // 'description' regardless.
        $index = $this->buildNameIndex();

        foreach ($rows as &$row) {
            $fullName = trim((string) ($row['item_name'] ?? ''));
            if ($fullName === '') continue;

            $row['item_name'] = $index['titles'][$fullName] ?? $this->clipToWordBoundary($fullName, 240);

            // The file has no colour/size column to tell the parent's groupRows() that
            // several SKUs sharing one Item Name are variants of one product rather than
            // duplicates — without this, only the first SKU per Item Name survives (see
            // ProductImportJob::upsertProduct's 'simple' path) and the rest are silently
            // dropped. Item Names that appear more than once in the WHOLE file (not just
            // this chunk — a product's rows can land in different chunks) are tagged with
            // their own SKU as the 'size' value so the parent treats them as separate
            // variants instead; an Item Name that appears only once stays untagged and
            // imports as a simple product.
            if (($index['counts'][$fullName] ?? 0) > 1) {
                $row['size'] = trim((string) ($row['sku'] ?? ''));
            }
        }
        unset($row);

        return $rows;
    }

    // Clips to <= $max characters on a word boundary — never mid-word — leaving headroom
    // under products.title's 255-char limit for the " (N)" disambiguation suffix that
    // buildNameIndex() appends on a collision.
    protected function clipToWordBoundary(string $name, int $max): string
    {
        if ($name === '' || mb_strlen($name) <= $max) {
            return $name;
        }

        $clipped   = mb_substr($name, 0, $max);
        $lastSpace = mb_strrpos($clipped, ' ');

        if ($lastSpace !== false && $lastSpace > 0) {
            $clipped = mb_substr($clipped, 0, $lastSpace);
        }

        return rtrim($clipped, " -,.");
    }

    // Scans the WHOLE file once (not just this chunk) to build:
    //  - counts: full (untruncated) Item Name -> how many rows share it (drives the
    //    simple/variable split above)
    //  - titles: full Item Name -> a DB-safe (<=255 char) title, guaranteed unique per
    //    distinct full name even when two unrelated products share enough boilerplate
    //    marketing copy that their first ~240 characters are identical
    protected function buildNameIndex(): array
    {
        $fullNames = $this->scanFullItemNames();
        $counts    = array_count_values($fullNames);

        $titles       = [];
        $occurrences  = [];
        foreach (array_keys($counts) as $fullName) {
            $base = $this->clipToWordBoundary($fullName, 240);
            $n    = ($occurrences[$base] ?? 0) + 1;
            $occurrences[$base] = $n;
            $titles[$fullName]  = $n === 1 ? $base : "{$base} ({$n})";
        }

        return ['counts' => $counts, 'titles' => $titles];
    }

    protected function scanFullItemNames(): array
    {
        if (!Storage::disk('local')->exists($this->path)) {
            return [];
        }

        $filePath  = Storage::disk('local')->path($this->path);
        $extension = strtolower(pathinfo($filePath, PATHINFO_EXTENSION));

        return $extension === 'csv'
            ? $this->scanCsvItemNames($filePath)
            : $this->scanSpreadsheetItemNames($filePath);
    }

    protected function scanCsvItemNames(string $filePath): array
    {
        $handle = fopen($filePath, 'r');
        if (!$handle) return [];

        $headers    = [];
        $lineNumber = 0;
        $names      = [];

        while (($raw = fgetcsv($handle, 0, ',')) !== false) {
            $lineNumber++;

            if ($lineNumber === 1) {
                $headers = $this->normalizeHeaders($raw);
                continue;
            }

            $mapped = $this->mapRowToHeaders($headers, $raw);
            $name   = trim((string) ($mapped['item_name'] ?? ''));
            if ($name !== '') $names[] = $name;
        }

        fclose($handle);
        return $names;
    }

    protected function scanSpreadsheetItemNames(string $filePath): array
    {
        $reader = IOFactory::createReaderForFile($filePath);
        $reader->setReadDataOnly(true);
        $spreadsheet = $reader->load($filePath);
        $sheet       = $spreadsheet->getActiveSheet();

        $highestRow    = $sheet->getHighestDataRow();
        $highestColumn = $sheet->getHighestDataColumn();
        $headerRow     = $sheet->rangeToArray("A1:{$highestColumn}1", null, true, false)[0] ?? [];
        $headers       = $this->normalizeHeaders($headerRow);

        $names = [];
        for ($r = 2; $r <= $highestRow; $r++) {
            $row    = $sheet->rangeToArray("A{$r}:{$highestColumn}{$r}", null, true, false)[0] ?? [];
            $mapped = $this->mapRowToHeaders($headers, $row);
            $name   = trim((string) ($mapped['item_name'] ?? ''));
            if ($name !== '') $names[] = $name;
        }

        $spreadsheet->disconnectWorksheets();
        unset($spreadsheet, $sheet, $reader);

        return $names;
    }
}
