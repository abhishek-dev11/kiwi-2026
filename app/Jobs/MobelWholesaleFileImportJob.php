<?php

namespace App\Jobs;

use App\Support\ProductTextCleaner;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Storage;
use PhpOffice\PhpSpreadsheet\IOFactory;

class ModelWholesaleFileImportJob extends ProductImportJob
{
    // The file has no stock/quantity column — 99 is the agreed in-stock default.
    protected int $defaultQuantity = 99;

    // Model Wholesale's own column headers -> the canonical keys ProductImportJob::groupRows()
    // reads. "SKU", "Group Name", "Description", "My Price", "Main Category", "Sub Sub Sub
    // Category" and "Image 1".."Image 3" (main image + gallery) already slugify to the
    // canonical keys — no rename needed. The product images are downloaded into
    // public/uploads/Product by the parent (the original URL is kept in products.image_url).
    const HEADER_RENAME = [
        'sub_category'     => 'sub_sub_category',   // Sub Category      -> mid level
        'sub_sub_category' => 'sub_sub_category_2', // Sub Sub Category  -> bottom level
    ];

    // Longest "Dimensions" text used as a variant label (products variant value is varchar(255)).
    const MAX_LABEL_LENGTH = 120;

    protected function normalizeHeaders(array $headers): array
    {
        $slugs = parent::normalizeHeaders($headers);

        return array_map(fn($slug) => self::HEADER_RENAME[$slug] ?? $slug, $slugs);
    }

    protected function readRows(): array
    {
        $rows = parent::readRows();

        // Skip rows whose "My Price" is missing or not a positive number.
        $rows = array_values(array_filter($rows, fn($row) => $this->hasValidPrice($row)));

        // The whole-file scan is done once per import and cached (a product's rows can land
        // in different chunks, so a chunk can't work this out from its own rows).
        $index = Cache::remember(
            "import_mw_index_{$this->importId}",
            now()->addHours(24),
            fn() => $this->buildIndex()
        );

        foreach ($rows as &$row) {
            $group = ProductTextCleaner::title((string) ($row['group_name'] ?? ''));
            $dims  = $this->label((string) ($row['dimensions'] ?? ''));
            $sku   = trim((string) ($row['sku'] ?? ''));

            // The same SKU on several rows (the source repeats a few): the first keeps it, the
            // later ones get -2, -3 ... so every variant has its own SKU. Rows identical in
            // every respect are the same variant and stay as one.
            if ($sku !== '' && isset($index['dupSkus'][$sku])) {
                $position = array_search($group . '|' . $dims, $index['dupSkus'][$sku], true);
                if ($position !== false && $position > 0) {
                    $sku .= '-' . ($position + 1);
                }
                $row['sku'] = $sku;
            }

            // A Group Name shared by several rows is one variable product; each row is a
            // "size" variant, labelled by its Dimensions — or by its SKU when the dimensions
            // aren't unique within the group. A name used once stays a simple product.
            if (isset($index['groups'][$group])) {
                $row['size'] = $index['groups'][$group] === 'dimensions' && $dims !== '' ? $dims : $sku;
            }
        }
        unset($row);

        return $rows;
    }

    protected function hasValidPrice(array $row): bool
    {
        $price = str_replace([',', '$'], '', trim((string) ($row['my_price'] ?? '')));

        return is_numeric($price) && (float) $price > 0;
    }

    // "1800w x 900d\n(each desktop)" -> one tidy line, clipped to a safe length.
    protected function label(string $dimensions): string
    {
        $label = trim(preg_replace('/\s+/u', ' ', $dimensions));

        return mb_strlen($label) > self::MAX_LABEL_LENGTH ? rtrim(mb_substr($label, 0, self::MAX_LABEL_LENGTH)) : $label;
    }

    // One pass over the valid rows of the whole file:
    //  groups  - Group Name used by more than one row => 'dimensions' when every row has its
    //            own non-blank Dimensions, else 'sku'
    //  dupSkus - SKU used by more than one row => the "group|dimensions" keys in file order
    protected function buildIndex(): array
    {
        $byGroup = [];
        $bySku   = [];

        foreach ($this->scanRows() as $mapped) {
            if (!$this->hasValidPrice($mapped)) continue;

            $group = ProductTextCleaner::title((string) ($mapped['group_name'] ?? ''));
            if ($group === '') continue;

            $dims = $this->label((string) ($mapped['dimensions'] ?? ''));
            $sku  = trim((string) ($mapped['sku'] ?? ''));

            $byGroup[$group][] = $dims;
            if ($sku !== '') $bySku[$sku][] = $group . '|' . $dims;
        }

        $groups = [];
        foreach ($byGroup as $group => $dimensions) {
            if (count($dimensions) < 2) continue;
            $unique = !in_array('', $dimensions, true) && count(array_unique($dimensions)) === count($dimensions);
            $groups[$group] = $unique ? 'dimensions' : 'sku';
        }

        $dupSkus = [];
        foreach ($bySku as $sku => $keys) {
            if (count($keys) > 1) $dupSkus[$sku] = $keys;
        }

        return ['groups' => $groups, 'dupSkus' => $dupSkus];
    }

    // Every data row of the file as a header-keyed array (CSV or XLSX).
    protected function scanRows(): \Generator
    {
        if (!Storage::disk('local')->exists($this->path)) {
            return;
        }

        $filePath = Storage::disk('local')->path($this->path);

        if (strtolower(pathinfo($filePath, PATHINFO_EXTENSION)) === 'csv') {
            $handle = fopen($filePath, 'r');
            if (!$handle) return;

            $headers = $this->normalizeHeaders((array) fgetcsv($handle, 0, ','));
            while (($raw = fgetcsv($handle, 0, ',')) !== false) {
                yield $this->mapRowToHeaders($headers, $raw);
            }
            fclose($handle);
            return;
        }

        $reader = IOFactory::createReaderForFile($filePath);
        $reader->setReadDataOnly(true);
        $spreadsheet = $reader->load($filePath);
        $sheet       = $spreadsheet->getActiveSheet();
        $last        = $sheet->getHighestDataColumn();
        $headers     = $this->normalizeHeaders($sheet->rangeToArray("A1:{$last}1", null, true, false)[0] ?? []);

        foreach ($sheet->rangeToArray("A2:{$last}" . $sheet->getHighestDataRow(), null, true, false) as $raw) {
            yield $this->mapRowToHeaders($headers, $raw);
        }

        $spreadsheet->disconnectWorksheets();
    }
}
