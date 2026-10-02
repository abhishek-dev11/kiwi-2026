<?php

namespace App\Jobs;

// Blundstone Footwear's file has the same flat variable / variation / simple layout as
// John Bull's, so the parent-by-SKU lookup, price-skip, and 99 default quantity are all
// inherited from JohnBullFileImportJob::buildChunkRows(). Only the column headers differ.
class BlundstoneFootwearFileImportJob extends JohnBullFileImportJob
{
    // Blundstone's own column headers -> the keys JohnBullFileImportJob::buildChunkRows()
    // reads. Left-hand keys are what parent::normalizeHeaders() slugifies the raw headers
    // down to. "Sku", "Item Name", "Main Category" and "Sub Sub Sub Category" already
    // slugify to canonical keys — no rename needed.
    const BLUNDSTONE_HEADER_RENAME = [
        'column'           => 'product_type',        // first column, blank header: variable / variation / simple
        'description'      => 'description_html',
        'my_price'         => 'default_price',        // "My Price"
        'sub_category'     => 'sub_sub_category',     // Sub Category      -> mid level
        'sub_sub_category' => 'sub_sub_category_2',   // Sub Sub Category  -> bottom level
        // 'image_1' already matches "Image 1"
    ];

    protected function normalizeHeaders(array $headers): array
    {
        // Skip JohnBull's own rename map: call the grandparent's slugifier directly.
        $slugs = ProductImportJob::normalizeHeaders($headers);

        return array_map(fn($slug) => self::BLUNDSTONE_HEADER_RENAME[$slug] ?? $slug, $slugs);
    }
}
