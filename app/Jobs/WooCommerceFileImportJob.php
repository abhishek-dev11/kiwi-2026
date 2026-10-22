<?php

namespace App\Jobs;

use App\Jobs\Concerns\ScansImportFile;
use App\Models\Categories;
use App\Support\ProductTextCleaner;
use Illuminate\Support\Facades\Cache;

// Shared import logic for supplier files laid out WooCommerce-style (Portwest, Lynn River):
//  - "variable"  : a product header row — name, description, image; never a price
//  - "variation" : one sellable size of the variable row above it — own SKU and price,
//                  name "{parent name} - {SIZE}", no description
//  - "simple"    : a standalone product
// Columns: Type, SKU, Group Name, Description, My Price, Main/Sub/Sub Sub/Sub Sub Sub
// Category, Image 1 (+ Image 2 / Image 3 gallery columns, if a file has them).
// A variation's parent can sit in an earlier chunk, so the parent details come from a
// whole-file index built once per import (see buildIndex()).
abstract class WooCommerceFileImportJob extends ProductImportJob
{
    use ScansImportFile;

    // These files have no stock/quantity column — 99 is the agreed in-stock default.
    protected int $defaultQuantity = 99;

    // The file's own column headers -> the canonical keys ProductImportJob::groupRows()
    // reads. "SKU", "Group Name", "Description", "My Price", "Main Category", "Sub Sub Sub
    // Category" and "Image 1".."Image 3" already slugify to the canonical keys. The main
    // product image is downloaded into public/uploads/Product by the parent; the original
    // URL is kept in products.image_url.
    const HEADER_RENAME = [
        'sub_category'     => 'sub_sub_category',   // Sub Category      -> mid level
        'sub_sub_category' => 'sub_sub_category_2', // Sub Sub Category  -> bottom level
    ];

    // Gallery columns parent::groupRows() reads (image_1 is the main image).
    const GALLERY_KEYS = ['image_2', 'image_3', 'image_4', 'image_5', 'image_6'];

    // Short supplier name for this import's cache key, e.g. "portwest".
    abstract protected function indexName(): string;

    protected function normalizeHeaders(array $headers): array
    {
        $slugs = parent::normalizeHeaders($headers);

        return array_map(fn($slug) => self::HEADER_RENAME[$slug] ?? $slug, $slugs);
    }

    protected function readRows(): array
    {
        $rows = parent::readRows();

        // Skip rows whose "My Price" is missing or invalid — this also drops every
        // "variable" header row, which never carries a price itself.
        $rows = array_values(array_filter($rows, fn($row) => $this->hasValidPrice($row)));

        // Stored compressed: the database cache keeps it in one row, and MySQL's
        // max_allowed_packet (1 MB by default) is smaller than the raw index.
        $index = unserialize(gzuncompress(base64_decode(Cache::remember(
            "import_{$this->indexName()}_index_{$this->importId}",
            now()->addHours(24),
            fn() => base64_encode(gzcompress(serialize($this->buildIndex()), 6))
        ))));

        foreach ($rows as &$row) {
            if (strtolower(trim((string) ($row['type'] ?? ''))) !== 'variation') {
                continue; // simple product — imported as-is
            }

            [$parentSku, $size] = $index['variations'][trim((string) ($row['sku'] ?? ''))] ?? [null, null];
            $family = $parentSku !== null ? ($index['parents'][$parentSku] ?? null) : null;
            if ($family === null) {
                continue; // no parent found — imported standalone rather than lost
            }

            $mainImage = $index['groupImage'][$family['group']] ?? $family['image'];
            $ownImage  = trim((string) ($row['image_1'] ?? ''));

            $row['group_name']  = $family['group'];
            $row['parent_sku']  = $index['groupSku'][$family['group']] ?? $parentSku;
            $row['size']        = $size;
            $row['description'] = $family['description'];
            // Every row of a product carries the product's main image. A second parent
            // family's own (different) photo, and a variation's own photo (e.g. a 3-pack
            // shot), are added to the product gallery after any Image 2/3 from the file.
            $row['image_1'] = $mainImage;
            $this->addGalleryImages($row, [$family['image'], $ownImage]);
        }
        unset($row);

        return $this->prepareRows($rows, $index);
    }

    // Supplier-specific last step over this chunk's rows (categories, labels, ...).
    protected function prepareRows(array $rows, array $index): array
    {
        return $rows;
    }

    // Appends images to the row's free gallery slots, skipping blanks, the main image and
    // ones already in the gallery.
    protected function addGalleryImages(array &$row, array $images): void
    {
        $taken = [trim((string) ($row['image_1'] ?? '')) => true];
        foreach (self::GALLERY_KEYS as $key) {
            $value = trim((string) ($row[$key] ?? ''));
            if ($value !== '') $taken[$value] = true;
        }

        foreach ($images as $image) {
            $image = trim((string) $image);
            if ($image === '' || isset($taken[$image])) continue;

            foreach (self::GALLERY_KEYS as $key) {
                if (trim((string) ($row[$key] ?? '')) === '') {
                    $row[$key]      = $image;
                    $taken[$image]  = true;
                    break;
                }
            }
        }
    }

    // Only categories this import creates are marked as this supplier's. Existing shared
    // ones (e.g. Safety, Footwear and its branches) are reused as they are — stamping them
    // with this supplier's id would make them look like this supplier's own categories.
    protected function assignSupplier(Categories $category): Categories
    {
        return $category->wasRecentlyCreated ? parent::assignSupplier($category) : $category;
    }

    protected function hasValidPrice(array $row): bool
    {
        $price = str_replace([',', '$'], '', trim((string) ($row['my_price'] ?? '')));

        return is_numeric($price) && (float) $price > 0;
    }

    // One pass over the whole file:
    //  parents     - parent SKU => its product name (Group Name), description and image
    //  variations  - SKU of every priced "variation" row (unique in the file) =>
    //                [parent SKU, size label] — e.g. "XL" from "{parent name} - XL"
    //  groupImage  - product name => its main image (the first parent family's image)
    //  groupSku    - product name => its product SKU (the first parent family's SKU, e.g.
    //                "AP210-BLACK"); each size keeps its own variation SKU
    //  simpleNames - product name of every priced "simple" row => [how many such rows,
    //                the first one's image]
    // A few products are split over two parent rows with the same Group Name (e.g. small
    // and large sizes under different style codes). They become one product with all the
    // sizes; labelSizes() keeps each size label unique within the product.
    protected function buildIndex(): array
    {
        $parents     = [];
        $entries     = []; // product name => [[sku, parent sku, size label], ...] in file order
        $groupImage  = [];
        $groupSku    = [];
        $simpleNames = [];
        $parent      = null;

        foreach ($this->scanRows() as $mapped) {
            $type = strtolower(trim((string) ($mapped['type'] ?? '')));

            if ($type === 'variable') {
                $parent = [
                    'sku'         => trim((string) ($mapped['sku'] ?? '')),
                    'name'        => trim((string) ($mapped['group_name'] ?? '')),
                    'group'       => ProductTextCleaner::title((string) ($mapped['group_name'] ?? '')),
                    'description' => (string) ($mapped['description'] ?? ''),
                    'image'       => trim((string) ($mapped['image_1'] ?? '')),
                ];
                if ($parent['group'] !== '') {
                    $groupImage[$parent['group']] ??= $parent['image'];
                    if ($parent['sku'] !== '') {
                        $groupSku[$parent['group']] ??= $parent['sku'];
                    }
                    $parents[$parent['sku']] = [
                        'group'       => $parent['group'],
                        'description' => $parent['description'],
                        'image'       => $parent['image'],
                    ];
                }
                continue;
            }

            if ($type !== 'variation') {
                $parent = null; // a simple row ends the previous variable product
                if ($type === 'simple' && $this->hasValidPrice($mapped)) {
                    $name = ProductTextCleaner::title((string) ($mapped['group_name'] ?? ''));
                    if ($name !== '') {
                        $simpleNames[$name] ??= [0, trim((string) ($mapped['image_1'] ?? ''))];
                        $simpleNames[$name][0]++;
                    }
                }
                continue;
            }

            $sku = trim((string) ($mapped['sku'] ?? ''));
            if ($parent === null || $parent['group'] === '' || $sku === '' || !$this->hasValidPrice($mapped)) {
                continue;
            }

            $entries[$parent['group']][] = [
                $sku,
                $parent['sku'],
                $this->sizeLabel($parent, (string) ($mapped['group_name'] ?? ''), $sku),
            ];
        }

        $variations = [];
        foreach ($entries as $groupEntries) {
            foreach ($this->labelSizes($groupEntries) as $sku => $variation) {
                $variations[$sku] = $variation;
            }
        }

        return [
            'parents'     => $parents,
            'variations'  => $variations,
            'groupImage'  => $groupImage,
            'groupSku'    => $groupSku,
            'simpleNames' => $simpleNames,
        ];
    }

    // One product's variations, in file order: [[sku, parent sku, size label], ...] ->
    // [sku => [parent sku, final size label]]. A size label already used in the product
    // gets the parent's style code ("L (2852NVR)"), so no SKU is dropped.
    protected function labelSizes(array $entries): array
    {
        $used   = [];
        $result = [];

        foreach ($entries as [$sku, $parentSku, $size]) {
            if (isset($used[mb_strtolower($size)])) {
                $size = "{$size} ({$parentSku})";
            }
            $used[mb_strtolower($size)] = true;

            $result[$sku] = [$parentSku, $size];
        }

        return $result;
    }

    // For suppliers that sell several SKUs under one size / colour name (Lynn River:
    // "57281 L" and "57281 L+"; Knight: "Black Fabric" assembled and knock-down). Every
    // label shared by more than one SKU of the product gets each SKU's own code — the part
    // after the parent's SKU ("Large (L+)"), or the whole SKU when the variation SKU doesn't
    // start with the parent's — so every SKU stays its own variant.
    protected function labelSizesWithSkuCodes(array $entries): array
    {
        $labels = [];
        foreach ($entries as [$sku, $parentSku, $size]) {
            $labels[$sku] = $size;
        }

        foreach ($this->duplicateLabels($labels) as $sku) {
            [, $parentSku] = $entries[array_search($sku, array_column($entries, 0), true)];
            $code          = $this->skuCode($parentSku, $sku);
            $labels[$sku]  = $labels[$sku] . ' (' . ($code !== '' ? $code : $sku) . ')';
        }

        // Still clashing (the same code under two parent rows of one product).
        foreach ($this->duplicateLabels($labels) as $sku) {
            $labels[$sku] = preg_replace('/\([^()]*\)$/', "({$sku})", $labels[$sku]);
        }

        $result = [];
        foreach ($entries as [$sku, $parentSku]) {
            $result[$sku] = [$parentSku, $labels[$sku]];
        }

        return $result;
    }

    // SKUs whose label (case-insensitive) is shared with another SKU.
    protected function duplicateLabels(array $labels): array
    {
        $counts = array_count_values(array_map('mb_strtolower', $labels));

        return array_keys(array_filter($labels, fn($label) => $counts[mb_strtolower($label)] > 1));
    }

    // "Baker's Pants White - 4XL" -> "4XL"; falls back to the SKU's suffix
    // ("2208WHR 4XL" -> "4XL") and finally to the whole SKU.
    protected function sizeLabel(array $parent, string $variationName, string $sku): string
    {
        $variationName = trim($variationName);
        $prefix        = $parent['name'] . ' - ';

        if ($parent['name'] !== '' && stripos($variationName, $prefix) === 0) {
            $label = trim(substr($variationName, strlen($prefix)));
            if ($label !== '') return $label;
        }

        $code = $this->skuCode($parent['sku'], $sku);

        return $code !== '' ? $code : $sku;
    }

    // The part of a variation's SKU after its parent's SKU: ("2208WHR", "2208WHR 4XL") ->
    // "4XL"; '' when the SKU doesn't start with the parent's.
    protected function skuCode(string $parentSku, string $sku): string
    {
        if ($parentSku !== '' && stripos($sku, $parentSku . ' ') === 0) {
            return trim(substr($sku, strlen($parentSku)));
        }

        return '';
    }
}
