<?php

namespace App\Jobs;

// C-Force's file is WooCommerce-style (see WooCommerceFileImportJob for the shared
// variable / variation handling) and carries its own categories (Apparel > ...), which are
// used as given. Its images are Google Drive file links, which ProductImageDownloader
// fetches through Drive's direct-download URL.
class CForceFileImportJob extends WooCommerceFileImportJob
{
    protected function indexName(): string
    {
        return 'cforce';
    }

    // A few rows carry an image value that isn't a web address — a relative path into
    // C-Force's own catalogue folders ("../../C-Force Catalogue/...") that this site can't
    // reach. The parent would store it as if it were a local upload (a broken image), so
    // it's cleared and the product shows the placeholder instead.
    protected function prepareRows(array $rows, array $index): array
    {
        foreach ($rows as &$row) {
            foreach (array_merge(['image_1'], self::GALLERY_KEYS) as $key) {
                $value = trim((string) ($row[$key] ?? ''));
                if ($value !== '' && !preg_match('#^https?://#i', $value)) {
                    $row[$key] = '';
                }
            }

            // The file gives no third category level — every product goes under Kids,
            // Ladies or Mens of its sub-category (e.g. Apparel > Polos > Kids).
            if (trim((string) ($row['sub_sub_category_2'] ?? '')) === '') {
                $row['sub_sub_category_2'] = $this->audience(
                    (string) ($row['group_name'] ?? ''),
                    (string) ($row['sub_sub_category'] ?? '')
                );
            }
        }
        unset($row);

        return $rows;
    }

    // Kids / Ladies / Mens from the product name (first match wins), checked against the
    // file's size runs (kids 2–18, womens 8–24). School Wear without a keyword is the
    // Cardrona school range (kids' sizes). "Adults" / unisex items — a straight men's-cut
    // in the product photos — and unisex accessories (draw cords, zip pullers) go to Mens.
    protected function audience(string $name, string $subCategory): string
    {
        if (preg_match('/\b(kids?|youth|junior|jnr|child(ren)?|boys?|girls?|toddler|infant|baby)\b/i', $name)) {
            return 'Kids';
        }
        if (preg_match('/\b(ladies|lady|women|womens|women\'?s|wmns?)\b/i', $name)) {
            return 'Ladies';
        }
        if (preg_match('/\b(mens|men|men\'?s)\b/i', $name)) {
            return 'Mens';
        }
        if (strcasecmp(trim($subCategory), 'School Wear') === 0) {
            return 'Kids';
        }

        return 'Mens';
    }
}
