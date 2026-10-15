<?php

namespace App\Jobs;

use App\Support\ProductTextCleaner;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Storage;
use PhpOffice\PhpSpreadsheet\IOFactory;

class FoxAndGunnFileImportJob extends ProductImportJob
{
    // Product images: store the supplier's image URL as-is (products.image / image_url) and
    // display it from there — nothing is downloaded or checked. Remove these two lines to go
    // back to downloading into public/uploads/Product.
    protected bool $downloadProductImages = false;
    protected bool $verifyImageUrls       = false;

    // The file has no stock/quantity column — 99 is the agreed in-stock default.
    protected int $defaultQuantity = 99;

    // Fox and Gunn's own column headers -> the canonical keys ProductImportJob::groupRows()
    // reads. "SKU", "Group Name", "Description", "My Price", "Main Category", "Image 1" and
    // "Sub Sub Sub Category" already slugify to the canonical keys — no rename needed.
    const HEADER_RENAME = [
        'sub_category'     => 'sub_sub_category',   // Sub Category      -> mid level
        'sub_sub_category' => 'sub_sub_category_2', // Sub Sub Category  -> bottom level
    ];

    protected function normalizeHeaders(array $headers): array
    {
        $slugs = parent::normalizeHeaders($headers);

        return array_map(fn($slug) => self::HEADER_RENAME[$slug] ?? $slug, $slugs);
    }

    protected function readRows(): array
    {
        $rows = parent::readRows();

        // Skip rows whose "My Price" is missing or not a positive number.
        $rows = array_values(array_filter($rows, function ($row) {
            $price = str_replace([',', '$'], '', trim((string) ($row['my_price'] ?? '')));
            return is_numeric($price) && (float) $price > 0;
        }));

        // The file is all "simple" rows, but a few Group Names are shared by several SKUs
        // (e.g. the JOSCO brushes, each SKU with its own price). Without a size value the
        // parent's groupRows() would keep only the first SKU of such a group, so a Group
        // Name that appears more than once in the WHOLE file (a group can span chunks) is
        // tagged with each row's SKU as the 'size' — they import as one variable product.
        // A name that appears once stays untagged and imports as a simple product.
        // The whole-file scan is done once per import and cached (not once per chunk —
        // with 10,000+ rows that re-scan was most of every chunk's run time).
        $repeated = array_flip(Cache::remember(
            "import_repeated_names_{$this->importId}",
            now()->addHours(24),
            fn() => array_keys(array_filter(array_count_values($this->scanGroupNames()), fn($n) => $n > 1))
        ));

        foreach ($rows as &$row) {
            $name = ProductTextCleaner::title((string) ($row['group_name'] ?? ''));
            if ($name !== '' && isset($repeated[$name])) {
                $row['size'] = trim((string) ($row['sku'] ?? ''));
            }
        }
        unset($row);

        return $rows;
    }

    // Every cleaned Group Name in the file (price-valid rows only, same as readRows()).
    protected function scanGroupNames(): array
    {
        if (!Storage::disk('local')->exists($this->path)) {
            return [];
        }

        $filePath = Storage::disk('local')->path($this->path);
        $names    = [];

        $collect = function (array $headers, iterable $rows) use (&$names) {
            foreach ($rows as $raw) {
                $mapped = $this->mapRowToHeaders($headers, $raw);
                $price  = str_replace([',', '$'], '', trim((string) ($mapped['my_price'] ?? '')));
                $name   = ProductTextCleaner::title((string) ($mapped['group_name'] ?? ''));
                if ($name !== '' && is_numeric($price) && (float) $price > 0) {
                    $names[] = $name;
                }
            }
        };

        if (strtolower(pathinfo($filePath, PATHINFO_EXTENSION)) === 'csv') {
            $handle = fopen($filePath, 'r');
            if (!$handle) return [];
            $headers = $this->normalizeHeaders((array) fgetcsv($handle, 0, ','));
            $collect($headers, (function () use ($handle) {
                while (($raw = fgetcsv($handle, 0, ',')) !== false) yield $raw;
            })());
            fclose($handle);
            return $names;
        }

        $reader = IOFactory::createReaderForFile($filePath);
        $reader->setReadDataOnly(true);
        $spreadsheet = $reader->load($filePath);
        $sheet       = $spreadsheet->getActiveSheet();
        $last        = $sheet->getHighestDataColumn();
        $headers     = $this->normalizeHeaders($sheet->rangeToArray("A1:{$last}1", null, true, false)[0] ?? []);
        $collect($headers, $sheet->rangeToArray("A2:{$last}" . $sheet->getHighestDataRow(), null, true, false));
        $spreadsheet->disconnectWorksheets();

        return $names;
    }
}
