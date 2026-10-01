<?php

namespace App\Jobs;

use Illuminate\Support\Facades\Storage;
use PhpOffice\PhpSpreadsheet\IOFactory;

class JohnBullFileImportJob extends ProductImportJob
{
    // The file has no stock/quantity column at all — 99 (not the parent's generic 999)
    // is the agreed in-stock default for John Bull, same as Banners Ink.
    protected int $defaultQuantity = 99;

    // John Bull's own column headers -> the canonical keys ProductImportJob::groupRows()
    // reads. Left-hand keys are what parent::normalizeHeaders() slugifies the raw
    // headers down to. "Main Category" and "Sub Sub Sub Category" already slugify to
    // the canonical 'main_category' / 'sub_sub_sub_category' keys as-is — no rename
    // needed for those.
    const HEADER_RENAME = [
        'product_description' => 'description_html',
        'price'                => 'default_price',
        'sub_category'         => 'sub_sub_category',    // Sub Category      -> mid level
        'sub_sub_category'     => 'sub_sub_category_2',  // Sub Sub Category  -> bottom level
        'product_image'        => 'image_1',
    ];

    protected function normalizeHeaders(array $headers): array
    {
        $slugs = parent::normalizeHeaders($headers);

        return array_map(fn($slug) => self::HEADER_RENAME[$slug] ?? $slug, $slugs);
    }

    // The export is a Shopify-style flat file: a "variable" row per product carries the
    // shared Item Name/Description/Image (Price always blank — it's never itself
    // sellable), normally followed by one "variation" row per size (own SKU/Price, SKU =
    // "{parent SKU} {SIZE}", Item Name = "{parent name} - {SIZE}"), or a standalone
    // "simple" row for a product with no size options. The real file does NOT
    // consistently keep a "variable" row immediately before its own "variation" rows —
    // e.g. "Comfort Arch Footbed"'s FBEDCA variation rows appear two rows *above* their
    // own "variable" row — so parent lookup can't rely on file order (unlike
    // Ph7FileImportJob's cascade). Instead the whole file is scanned once to index every
    // "variable"/"simple" row by its own SKU, then each "variation" row finds its parent
    // by splitting its own SKU on the first space ("4161 5" -> parent SKU "4161", size
    // "5") — reliable regardless of row order, since a child's SKU is always
    // "{parent SKU} {size}". Only rows in this job's assigned line range are returned.
    protected function readRows(): array
    {
        if (!Storage::disk('local')->exists($this->path)) {
            throw new \RuntimeException("File not found: {$this->path}");
        }

        $filePath  = Storage::disk('local')->path($this->path);
        $extension = strtolower(pathinfo($filePath, PATHINFO_EXTENSION));

        $allRows = $extension === 'csv'
            ? $this->readAllCsvRows($filePath)
            : $this->readAllSpreadsheetRows($filePath);

        return $this->buildChunkRows($allRows);
    }

    // [line number => mapped row] for every data row in the file, in order.
    protected function readAllCsvRows(string $filePath): array
    {
        $handle = fopen($filePath, 'r');
        if (!$handle) {
            throw new \RuntimeException("Cannot open file: {$this->path}");
        }

        $headers    = [];
        $lineNumber = 0;
        $rows       = [];

        while (($raw = fgetcsv($handle, 0, ',')) !== false) {
            $lineNumber++;
            $raw = array_map(fn($v) => $this->toUtf8((string) $v), $raw);

            if ($lineNumber === 1) {
                $headers = $this->normalizeHeaders($raw);
                continue;
            }

            $rows[$lineNumber] = $this->mapRowToHeaders($headers, $raw);
        }

        fclose($handle);
        return $rows;
    }

    protected function readAllSpreadsheetRows(string $filePath): array
    {
        $reader = IOFactory::createReaderForFile($filePath);
        $reader->setReadDataOnly(true);
        $spreadsheet = $reader->load($filePath);
        $sheet       = $spreadsheet->getActiveSheet();

        $highestRow    = $sheet->getHighestDataRow();
        $highestColumn = $sheet->getHighestDataColumn();
        $headerRow     = $sheet->rangeToArray("A1:{$highestColumn}1", null, true, false)[0] ?? [];
        $headers       = $this->normalizeHeaders($headerRow);

        $rows = [];
        for ($lineNumber = 2; $lineNumber <= $highestRow; $lineNumber++) {
            $row = $sheet->rangeToArray("A{$lineNumber}:{$highestColumn}{$lineNumber}", null, true, false)[0] ?? [];
            $rows[$lineNumber] = $this->mapRowToHeaders($headers, $row);
        }

        $spreadsheet->disconnectWorksheets();
        unset($spreadsheet, $sheet, $reader);

        return $rows;
    }

    protected function buildChunkRows(array $allRows): array
    {
        // Index every "variable"/"simple" row by its own SKU first — a variation row's
        // parent is found by lookup, not by file position (see readRows() docblock).
        $parentBySku = [];
        foreach ($allRows as $row) {
            $type = strtolower(trim((string) ($row['product_type'] ?? '')));
            if ($type !== 'variable' && $type !== 'simple') continue;

            $sku = trim((string) ($row['sku'] ?? ''));
            if ($sku === '') continue;

            $parentBySku[$sku] = [
                'name'        => trim((string) ($row['item_name'] ?? '')),
                'description' => trim((string) ($row['description_html'] ?? '')),
                'image'       => trim((string) ($row['image_1'] ?? '')),
            ];
        }

        $rows = [];

        foreach ($allRows as $lineNumber => $row) {
            $type = strtolower(trim((string) ($row['product_type'] ?? '')));

            // Never itself a purchasable line (Price is always blank) — only indexed
            // above so its "variation" rows can find it.
            if ($type === 'variable') continue;

            if ($lineNumber < $this->startRow || $lineNumber > $this->endRow) {
                continue;
            }

            $sku  = trim((string) ($row['sku'] ?? ''));
            $name = trim((string) ($row['item_name'] ?? ''));
            if ($sku === '' && $name === '') continue;

            // "If Price Column is not a valid value then skip those product" — a chunk of
            // variation rows in the real file carry no price at all (discontinued sizes),
            // meaning there's no real sellable price for that row.
            $price = str_replace([',', '$'], '', trim((string) ($row['default_price'] ?? '')));
            if (!is_numeric($price) || (float) $price <= 0) continue;

            if ($type === 'variation') {
                // Child SKU is always "{parent SKU} {size}" — split on the first space.
                $spacePos  = strpos($sku, ' ');
                $parentSku = $spacePos !== false ? substr($sku, 0, $spacePos) : '';
                $parent    = $parentSku !== '' ? ($parentBySku[$parentSku] ?? null) : null;

                if ($parent !== null) {
                    $groupName = $parent['name'];
                    $size      = trim(substr($sku, $spacePos + 1));
                    $desc      = $row['description_html'] !== '' ? $row['description_html'] : $parent['description'];
                    $image     = $row['image_1'] !== '' ? $row['image_1'] : $parent['image'];
                } else {
                    // No parent found (stray variation row with no space in its SKU, or
                    // an unmatched parent SKU) — import standalone rather than lose the
                    // row.
                    $groupName = $name;
                    $size      = '';
                    $desc      = trim((string) ($row['description_html'] ?? ''));
                    $image     = trim((string) ($row['image_1'] ?? ''));
                }
            } else {
                // 'simple' row — already standalone, own values as-is.
                $groupName = $name;
                $size      = '';
                $desc      = trim((string) ($row['description_html'] ?? ''));
                $image     = trim((string) ($row['image_1'] ?? ''));
            }

            $row['item_name']        = $groupName;
            $row['size']             = $size;
            $row['description_html'] = $desc;
            $row['image_1']          = $image;
            $row['sku']              = $sku;

            $rows[] = $row;
        }

        return $rows;
    }

    // A raw CSV export of this file may not be UTF-8 (mirrors Ph7FileImportJob's
    // defence) — re-decode only fields that aren't already valid UTF-8; leave genuinely
    // UTF-8 fields untouched. Not needed for XLSX (PhpSpreadsheet already returns UTF-8).
    protected function toUtf8(string $value): string
    {
        if ($value === '' || mb_check_encoding($value, 'UTF-8')) {
            return $value;
        }

        return mb_convert_encoding($value, 'UTF-8', 'Windows-1252');
    }
}
