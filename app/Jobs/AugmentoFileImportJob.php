<?php

namespace App\Jobs;

class AugmentoFileImportJob extends ProductImportJob
{
    // Augmento's own column headers -> the canonical keys ProductImportJob::groupRows()
    // reads. Left-hand keys are what parent::normalizeHeaders() slugifies the raw
    // "stock_code" / "category1" / etc. headers down to.
    //
    // Note: the raw file's own "description" column holds the product TITLE, not a
    // description — it must be renamed to item_name so it doesn't collide with the
    // canonical 'description' key groupRows() reads for the actual body copy (which
    // lives in this file's "Product text" column instead).
    const HEADER_RENAME = [
        'stock_code'  => 'sku',
        'description' => 'item_name',
        'price_ex'    => 'default_price',
        'image_url'   => 'image_1',
        'product_text' => 'description_html',
        'category1'   => 'main_category',
        'category2'   => 'sub_sub_category',
        'category3'   => 'sub_sub_category_2',
    ];

    protected function normalizeHeaders(array $headers): array
    {
        $slugs = parent::normalizeHeaders($headers);

        return array_map(fn($slug) => self::HEADER_RENAME[$slug] ?? $slug, $slugs);
    }

    protected function readRows(): array
    {
        $rows = parent::readRows();

        // "Obsolete status (if applicable)" is blank for every row in the current file,
        // but skip anything flagged here so a future refreshed price list doesn't import
        // discontinued lines.
        return array_values(array_filter($rows, fn($row) => trim((string) ($row['obsolete_status_if_applicable'] ?? '')) === ''));
    }
}
