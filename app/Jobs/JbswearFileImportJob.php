<?php

namespace App\Jobs;

class JbswearFileImportJob extends ProductImportJob
{
    // Colours in this file are sometimes abbreviated inside the Product Name text
    // (e.g. colour "Light Blue" but the name says "...TEE LT BLUE - 08"). Confirmed
    // via an offline dry-run against the real "2026 JBswear SKU - NZ Reseller" file —
    // these three are the only systematic abbreviations found across 194 distinct colours.
    const COLOUR_ABBREVIATIONS = [
        '/\bLight\b/i'  => 'Lt',
        '/\bDark\b/i'   => 'Dk',
        '/\bJunior\b/i' => 'Jnr',
    ];

    // JBswear's own column headers -> the canonical keys ProductImportJob::groupRows()
    // reads. Left-hand keys are what parent::normalizeHeaders() slugifies the raw
    // "Product Name" / "Category" / etc. headers down to.
    // const HEADER_RENAME = [
    //     'product_name'     => 'item_name',
    //     'category'         => 'main_category',
    //     'sub_category'     => 'sub_sub_category',
    //     'net_unit_price'   => 'default_price',
    //     'colour_image_url' => 'image_1',
    // ];


    const HEADER_RENAME = [
        // "Style" (e.g. "C of C LADIES 3/4 SLEEVE BOAT NECK TEE") is shared across every
        // colour/size of a style and is already the clean product title — unlike
        // "Item Name"/"GroupName", which both bake the colour + size into the string
        // (e.g. "...BOAT NECK TEE SAGE - 06") and would otherwise make every colour/size
        // its own product instead of a variant.
        'style'            => 'group_name',

        // The file carries 4 category levels (Main/Sub/Sub Sub/Sub Sub Sub Category) but
        // the parent's groupRows() reads the mid/bottom levels under the (historical,
        // oddly-named) keys 'sub_sub_category' / 'sub_sub_category_2'.
        'sub_category'     => 'sub_sub_category',   // Sub Category      -> mid level
        'sub_sub_category'  => 'sub_sub_category_2', // Sub Sub Category  -> bottom level
        // Sub Sub Sub Category already slugifies to 'sub_sub_sub_category', which matches
        // the parent's fourth-level key as-is — no rename needed.

        // "Image 1" is actually a link to the product's detail page, not a photo — moved
        // out of the way so it isn't mistaken for the main product image. "Image 2" is
        // the real full-URL photo, promoted to 'image_1' (the key the parent uses for the
        // main product image). "Image 3"/"Image 4" are bare filenames with no domain
        // (e.g. "1BT3_F1_01.jpg") — moved out of the parent's image_2..image_6 gallery
        // scan range rather than guessing a base URL to make them resolvable.
        'image_1'          => 'product_page_url',
        'image_2'          => 'image_1',
        'image_3'          => 'image_3_filename',
        'image_4'          => 'image_4_filename',

        // Always blank in this file (checked across all ~12,700 rows). Renamed so the
        // parent's array_key_exists('inventory', ...) check finds nothing and falls back
        // to its 999 (in-stock) default, instead of reading an empty value as "0 in stock".
        'inventory'        => 'inventory_unused',
    ];

    protected function normalizeHeaders(array $headers): array
    {
        $slugs = parent::normalizeHeaders($headers);

        return array_map(fn($slug) => self::HEADER_RENAME[$slug] ?? $slug, $slugs);
    }

    protected function readRows(): array
    {
        $rows = parent::readRows();

        foreach ($rows as &$row) {
            $row['group_name'] = $this->buildGroupName(
                (string) ($row['item_name'] ?? ''),
                (string) ($row['colour']    ?? ''),
                (string) ($row['size_code'] ?? ''),
                (string) ($row['size']      ?? '')
            );

            // No description_html column exists in this file — Style Details carries the
            // feature-bullet copy; Style Blurb (a shorter tagline) fills the gap on the
            // ~30% of rows where Details is blank.
            $details = trim((string) ($row['style_details'] ?? ''));
            $row['description'] = $details !== '' ? $details : trim((string) ($row['style_blurb'] ?? ''));
        }
        unset($row);

        return $rows;
    }

    // Product Name bakes the colour + size into the tail of the string (e.g.
    // "...BOAT NECK TEE SAGE - 06"), which would otherwise make every colour/size its
    // own product instead of a variant. Stripping that trailing "COLOUR - SIZE" lets
    // same-style rows regroup into one product, same as the other suppliers' jobs.
    // Confirmed via dry-run: this matches ~95% of rows; the remainder (mostly adult/kids
    // sharing one style code, or a handful of source-side spelling inconsistencies) just
    // keep their full name and import as their own standalone product — never lost or
    // wrong data, just a slightly less-than-ideal grouping for that handful of rows.
    protected function buildGroupName(string $itemName, string $colour, string $sizeCode, string $size): string
    {
        $name     = $this->normalizeSpace($itemName);
        $sizeCode = $this->normalizeSpace($sizeCode);
        $size     = $this->normalizeSpace($size);
        $colour   = $this->normalizeSpace($colour);

        foreach ($this->colourCandidates($colour) as $c) {
            if ($c === '') continue;
            foreach ([$sizeCode, $size] as $s) {
                if ($s === '') continue;
                $pattern  = '/\s*' . preg_quote($c, '/') . '\s*-?\s*' . preg_quote($s, '/') . '\s*$/i';
                $stripped = preg_replace($pattern, '', $name, -1, $count);
                if ($count > 0) return $this->normalizeSpace($stripped);
            }
        }

        // No size suffix at all (one-size items) — just strip the trailing colour.
        foreach ($this->colourCandidates($colour) as $c) {
            if ($c === '') continue;
            $pattern  = '/\s*' . preg_quote($c, '/') . '\s*$/i';
            $stripped = preg_replace($pattern, '', $name, -1, $count);
            if ($count > 0) return $this->normalizeSpace($stripped);
        }

        return $name;
    }

    protected function colourCandidates(string $colour): array
    {
        if ($colour === '') return [];

        $candidates = [$colour];

        $abbrev = preg_replace(array_keys(self::COLOUR_ABBREVIATIONS), array_values(self::COLOUR_ABBREVIATIONS), $colour);
        if ($abbrev !== $colour) $candidates[] = $abbrev;

        // "Black/Black", "Navy/Navy" — a two-tone garment's main/contrast colour both the
        // same shade — is only ever printed once in the Product Name.
        if (preg_match('~^(.+)/\1$~i', $colour, $m)) $candidates[] = $m[1];

        return array_unique($candidates);
    }

    protected function normalizeSpace(string $s): string
    {
        $s = str_replace("\xC2\xA0", ' ', $s); // NBSP -> regular space
        $s = preg_replace('/\s*\/\s*/', '/', $s); // "A / B" -> "A/B"

        return trim(preg_replace('/\s+/', ' ', $s));
    }
}
