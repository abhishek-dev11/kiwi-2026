<?php

namespace App\Jobs;

// Bata's file has the same flat variable / variation / simple layout as John Bull's, so
// the parent-by-SKU lookup, price-skip, and 99 default quantity are all inherited from
// JohnBullFileImportJob::buildChunkRows(). Only the column headers and the orphan
// fallback differ.
class BataFileImportJob extends JohnBullFileImportJob
{
    // Bata's own column headers -> the keys JohnBullFileImportJob::buildChunkRows()
    // reads. Left-hand keys are what parent::normalizeHeaders() slugifies the raw headers
    // down to. "SKU", "Main Category", "Sub Sub Sub Category" and "Image 1" already
    // slugify to canonical keys — no rename needed.
    const BATA_HEADER_RENAME = [
        'type'             => 'product_type',        // variable / variation / simple
        'group_name'       => 'item_name',           // the file has no "Item Name" column
        'description'      => 'description_html',
        'my_price'         => 'default_price',       // "My Price"
        'sub_category'     => 'sub_sub_category',    // Sub Category      -> mid level
        'sub_sub_category' => 'sub_sub_category_2',  // Sub Sub Category  -> bottom level
    ];

    protected function normalizeHeaders(array $headers): array
    {
        // Skip JohnBull's own rename map: call the grandparent's slugifier directly.
        $slugs = ProductImportJob::normalizeHeaders($headers);

        return array_map(fn($slug) => self::BATA_HEADER_RENAME[$slug] ?? $slug, $slugs);
    }

    // Bata keeps each product's variation rows directly under its "variable" row, but a
    // few parents carry a different SKU than their children (e.g. variable "504-88011"
    // vs variations "504-88012 5", "504-88012 6", ...). When the SKU lookup misses, the
    // preceding "variable" row is the parent.
    protected function fallbackParent(?array $lastVariable): ?array
    {
        return $lastVariable;
    }
}
