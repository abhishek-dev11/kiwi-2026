<?php

namespace App\Jobs;

use App\Support\ProductTextCleaner;

// Lynn River's file is WooCommerce-style (see WooCommerceFileImportJob for the shared
// variable / variation handling) and carries its own categories (Safety > Gloves > ...),
// which are used as given.
class LynnRiverFileImportJob extends WooCommerceFileImportJob
{
    protected function indexName(): string
    {
        return 'lynnriver';
    }

    // Many products sell several SKUs under the same size name — "57281 L" and "57281 L+",
    // "62147 L+X3" (3-pack), "DC25300 B/G/R/W" (colours) — and a few size names in the file
    // are wrong ("62570 M" is named "Large"). A size name used by more than one SKU of the
    // product is therefore labelled with each SKU's own code ("Large (L)", "Large (L+)",
    // "Large (L+X3)"), falling back to the whole SKU, so every SKU stays its own variant.
    protected function labelSizes(array $entries): array
    {
        return $this->labelSizesWithSkuCodes($entries);
    }

    // A few "simple" rows share one Group Name (e.g. the same safety glasses with a clear,
    // smoke or amber lens, each its own SKU). Imported as-is, only the first would survive
    // (parent::upsertProduct's "simple" path); instead they become one product with a
    // variant per SKU. The first row's image is the product image, the others' images go
    // into its gallery.
    protected function prepareRows(array $rows, array $index): array
    {
        foreach ($rows as &$row) {
            if (strtolower(trim((string) ($row['type'] ?? ''))) !== 'simple') continue;

            $name = ProductTextCleaner::title((string) ($row['group_name'] ?? ''));
            [$count, $firstImage] = $index['simpleNames'][$name] ?? [0, ''];
            if ($count < 2) continue;

            $ownImage    = trim((string) ($row['image_1'] ?? ''));
            $row['size'] = trim((string) ($row['sku'] ?? ''));
            if ($firstImage !== '') {
                $row['image_1'] = $firstImage;
            }
            $this->addGalleryImages($row, [$ownImage]);
        }
        unset($row);

        return $rows;
    }
}
