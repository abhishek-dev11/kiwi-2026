<?php

namespace App\Jobs;

use Illuminate\Support\Facades\Storage;

class Ph7FileImportJob extends ProductImportJob
{
    // pH7's export is a flat WooCommerce-style CSV with only 6 columns (Type, SKU, Name,
    // Short description, Regular price, Images) — no category columns, no size/colour
    // columns, and no inventory column (falls back to the 999-default in the parent
    // groupRows()). "Variable" products are exported as a header row (Type=variable,
    // holding the shared name/description/images, price always blank) immediately
    // followed by one "variation" row per sellable size/spec (Type=variation, its own
    // SKU/price, name = "{parent name} - {SIZE}"). "Simple" rows are already standalone
    // products. This doesn't fit the generic HEADER_RENAME + parent::readRows() pattern
    // (a variation row's product-level fields live on a *different* row than the row
    // itself), so this overrides readRows() entirely instead of just remapping columns.
    protected function readRows(): array
    {
        if (!Storage::disk('local')->exists($this->path)) {
            throw new \RuntimeException("File not found: {$this->path}");
        }

        $handle = fopen(Storage::disk('local')->path($this->path), 'r');
        if (!$handle) {
            throw new \RuntimeException("Cannot open file: {$this->path}");
        }

        // Scanned across the WHOLE file every chunk (not just this chunk's line range) —
        // a variation row's parent "variable" row can land in an earlier chunk, so the
        // parent's name/description/images must be tracked from line 1 regardless of
        // which lines this job instance is actually assigned to emit.
        $lineNumber = 0;
        $currentParent = null;
        $rows = [];

        while (($raw = fgetcsv($handle, 0, ',')) !== false) {
            $lineNumber++;
            if ($lineNumber === 1) continue; // header row

            $type        = trim($this->toUtf8((string) ($raw[0] ?? '')));
            $sku         = trim($this->toUtf8((string) ($raw[1] ?? '')));
            $name        = trim($this->toUtf8((string) ($raw[2] ?? '')));
            $description = trim($this->toUtf8((string) ($raw[3] ?? '')));
            $price       = trim($this->toUtf8((string) ($raw[4] ?? '')));
            $images      = trim($this->toUtf8((string) ($raw[5] ?? '')));

            if ($type === 'variable') {
                // Never itself a purchasable line (no SKU/price of its own) — only its
                // name/description/images cascade down to the "variation" rows below it.
                $currentParent = [
                    'name'        => $name,
                    'description' => $description,
                    'images'      => $images,
                ];
                continue;
            }

            if ($lineNumber < $this->startRow || $lineNumber > $this->endRow) {
                continue;
            }

            if ($sku === '' && $name === '') continue;

            if ($type === 'variation' && $currentParent !== null) {
                $groupName = $currentParent['name'];
                $size      = $this->extractVariationLabel($currentParent['name'], $name);
                $rowImages = $images !== '' ? $images : $currentParent['images'];
                $desc      = $currentParent['description'];
            } else {
                // 'simple', or a stray 'variation' with no parent context — import
                // standalone rather than lose the row.
                $groupName = $name;
                $size      = '';
                $rowImages = $images;
                $desc      = $description;
            }

            $mapped = [
                'sku'               => $sku,
                'item_name'         => $name,
                'group_name'        => $groupName,
                'size'              => $size,
                'default_price'     => $price,
                'description_html'  => $desc,
            ];

            foreach ($this->splitImages($rowImages) as $index => $url) {
                $mapped['image_' . ($index + 1)] = $url;
            }

            $rows[] = $mapped;
        }

        fclose($handle);

        return $rows;
    }

    // A variation's Name is normally "{parent name} - {SIZE}" — strip the shared prefix
    // to isolate the size label. ~15% of rows have minor wording drift from the parent
    // name, so fall back to the text after the last " - ", and finally to the full
    // variation name — every row still gets a distinct, non-empty size label this way.
    protected function extractVariationLabel(string $parentName, string $variationName): string
    {
        if ($parentName !== '' && stripos($variationName, $parentName) === 0) {
            $label = substr($variationName, strlen($parentName));
            return trim(ltrim(trim($label), '-'));
        }

        $pos = strrpos($variationName, ' - ');
        if ($pos !== false) {
            return trim(substr($variationName, $pos + 3));
        }

        return $variationName;
    }

    // The export is Windows-1252 (e.g. curly quotes / degree signs come through as raw
    // 0x93/0x92/0xB0 bytes), not UTF-8 — inserted as-is these bytes are invalid UTF-8 and
    // MySQL rejects the whole row ("Incorrect string value"). Re-decode only the fields
    // that aren't already valid UTF-8; leave genuinely UTF-8 fields untouched.
    protected function toUtf8(string $value): string
    {
        if ($value === '' || mb_check_encoding($value, 'UTF-8')) {
            return $value;
        }

        return mb_convert_encoding($value, 'UTF-8', 'Windows-1252');
    }

    // "img1.jpg, img2.jpg, ..." -> up to 6 URLs (image_1 = main, image_2..image_6 =
    // gallery, matching the cap parent::groupRows() already applies).
    protected function splitImages(string $images): array
    {
        if ($images === '') return [];

        $urls = array_values(array_filter(array_map('trim', explode(',', $images)), fn($u) => $u !== ''));

        return array_slice($urls, 0, 6);
    }
}
