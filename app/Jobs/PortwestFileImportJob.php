<?php

namespace App\Jobs;

use App\Jobs\Concerns\ScansImportFile;
use App\Models\Categories;
use App\Support\ProductTextCleaner;
use Illuminate\Support\Facades\Cache;

class PortwestFileImportJob extends ProductImportJob
{
    use ScansImportFile;

    // The file has no stock/quantity column — 99 is the agreed in-stock default.
    protected int $defaultQuantity = 99;

    // Portwest's own column headers -> the canonical keys ProductImportJob::groupRows()
    // reads. "SKU", "Group Name", "Description", "My Price", "Main Category", "Sub Sub Sub
    // Category" and "Image 1" (plus "Image 2"/"Image 3" gallery columns, if a file has them)
    // already slugify to the canonical keys. The main product image is downloaded into
    // public/uploads/Product by the parent; the original URL is kept in products.image_url.
    const HEADER_RENAME = [
        'sub_category'     => 'sub_sub_category',   // Sub Category      -> mid level
        'sub_sub_category' => 'sub_sub_category_2', // Sub Sub Category  -> bottom level
    ];

    protected function normalizeHeaders(array $headers): array
    {
        $slugs = parent::normalizeHeaders($headers);

        return array_map(fn($slug) => self::HEADER_RENAME[$slug] ?? $slug, $slugs);
    }

    // The file is WooCommerce-style (like pH7's):
    //  - "variable"  : a product header row — name, description, image; never a price
    //  - "variation" : one sellable size of the variable row above it — own SKU and price,
    //                  name "{parent name} - {SIZE}", no description
    //  - "simple"    : a standalone product
    // A variation's parent can sit in an earlier chunk, so the parent details come from a
    // whole-file index built once per import (see buildIndex()).
    protected function readRows(): array
    {
        $rows = parent::readRows();

        // Skip rows whose "My Price" is missing or invalid — this also drops every
        // "variable" header row, which never carries a price itself.
        $rows = array_values(array_filter($rows, fn($row) => $this->hasValidPrice($row)));

        // Stored compressed: the database cache keeps it in one row, and MySQL's
        // max_allowed_packet (1 MB by default) is smaller than the raw index.
        $index = unserialize(gzuncompress(base64_decode(Cache::remember(
            "import_portwest_index_{$this->importId}",
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

            $row['group_name']  = $family['group'];
            $row['size']        = $size;
            $row['description'] = $family['description'];
            // Every row of a product carries the product's main image; a second parent
            // family's own (different) photo is added to the product gallery.
            $row['image_1'] = $mainImage;
            if ($family['image'] !== '' && $family['image'] !== $mainImage) {
                $row['image_2'] = $family['image'];
            }
        }
        unset($row);

        // The file only says "Safety" — every product is placed in its most relevant
        // sub-category (and type, where one fits) worked out from its name.
        foreach ($rows as &$row) {
            [$mid, $bottom, $fourth] = $this->classify((string) ($row['group_name'] ?? ''));

            $row['main_category']        = self::MAIN_CATEGORY;
            $row['sub_sub_category']     = $mid;
            $row['sub_sub_category_2']   = $bottom ?? '';
            $row['sub_sub_sub_category'] = $fourth ?? '';
        }
        unset($row);

        return $rows;
    }

    // =========================================================
    // CATEGORIES FROM THE PRODUCT NAME
    // =========================================================
    const MAIN_CATEGORY = 'Safety';

    // Sub-category rules, checked in order — the first whose pattern matches the product
    // name wins, so the more specific kinds of product come first (e.g. an FR hi-vis vest
    // is "Flame Resistant Clothing", a hi-vis rain jacket is "Hi-Vis Clothing > Rainwear").
    // Each rule lists its own type rules the same way: [pattern, type] or
    // [pattern, type, [pattern => fourth level]]; no type match leaves it at sub-category
    // level. "Footwear" (and its Lace Up / Slip On / Gumboots / Jogger / Socks branches)
    // already exists under Safety and is reused.
    const CATEGORY_RULES = [
        ['/\b(boot|wellington|wade|clog|trainer)s?\b/i', 'Footwear', [
            ['/wellington|wade/i', 'Gumboots', ['/steel/i' => 'Steel Toe', '/composite/i' => 'Composite Toe']],
            ['/trainer/i', 'Jogger', ['/steel/i' => 'Steel Toe', '/composite/i' => 'Composite Toe']],
            ['/clog|slip on|dealer/i', 'Slip On', ['/steel/i' => 'Steel Toe', '/composite/i' => 'Composite Toe']],
            ['/boot/i', 'Lace Up', ['/steel/i' => 'Steel Toe', '/composite/i' => 'Composite Toe']],
        ]],
        ['/\bwork sock/i', 'Footwear', [
            ['/sock/i', 'Socks'],
        ]],
        ['/glove|gauntlet/i', 'Gloves', [
            ['/clip/i', 'Glove Accessories'],
            ['/\bdisp\b|disposable/i', 'Disposable Gloves'],
            ['/welder/i', 'Welding Gloves'],
            ['/\bchem\b/i', 'Chemical Resistant Gloves'],
            ['/\bcold\b/i', 'Cold Resistant Gloves'],
            ['/\bcut\b/i', 'Cut Resistant Gloves'],
            ['/impact/i', 'Impact Resistant Gloves'],
            ['/leather/i', 'Leather Gloves'],
            ['/./', 'Grip & General Handling Gloves'],
        ]],
        ['/respirator/i', 'Respiratory Protection', []],
        ['/\bear (defenders?|plugs?|muffs?)\b/i', 'Hearing Protection', [
            ['/plug/i', 'Ear Plugs'],
            ['/./', 'Ear Muffs'],
        ]],
        ['/glasses|spectacles|goggles|readers/i', 'Eye Protection', [
            ['/cord/i', 'Eyewear Accessories'],
            ['/goggles/i', 'Safety Goggles'],
            ['/./', 'Safety Glasses'],
        ]],
        ['/helmet|hard hat|bump cap/i', 'Head Protection', [
            ['/hard hat brim|insect net|accessory/i', 'Head Protection Accessories'],
            ['/bump cap/i', 'Bump Caps'],
            ['/./', 'Hard Hats & Helmets'],
        ]],
        ['/\bFR\b|bizflame|modaflame|portflame|bizweld/i', 'Flame Resistant Clothing', [
            ['/coverall/i', 'FR Coveralls'],
            ['/jacket|vest|gilet/i', 'FR Jackets & Vests'],
            ['/pants|jean|legging/i', 'FR Pants'],
            ['/balaclava|neck|sock/i', 'FR Accessories'],
            ['/shirt|polo|sweatshirt/i', 'FR Shirts & Tops'],
        ]],
        ['/flexatex chem|biztex|type 5\/6/i', 'Chemical & Disposable Protection', [
            ['/coverall/i', 'Protective Coveralls'],
            ['/apron/i', 'Chemical Aprons'],
            ['/jacket|coat/i', 'Chemical Jackets & Coats'],
            ['/pants|bib and brace/i', 'Chemical Pants & Bib and Brace'],
        ]],
        ['/freezer|coldstore|heated|baselayer|thermal|merino/i', 'Cold Weather & Thermal Wear', [
            ['/freezer|coldstore/i', 'Freezer Wear'],
            ['/heated/i', 'Heated Clothing'],
            ['/./', 'Thermal & Baselayers'],
        ]],
        ['/chef|baker|butcher|apron|standard (women\'s )?coat|food industry|meshair|skull cap/i', 'Hospitality & Food Industry', [
            ['/apron/i', 'Aprons'],
            ['/skull cap/i', 'Hospitality Headwear'],
            ['/coat/i', 'Food Industry Coats'],
            ['/chef.*jacket|jacket/i', 'Chef Jackets'],
            ['/pants/i', 'Chef & Food Industry Pants'],
            ['/./', 'Hospitality Shirts & T-Shirts'],
        ]],
        ['/hi-?vis|hi vis|\bttmc\b|x[ -]back/i', 'Hi-Vis Clothing', [
            ['/\brain|sealtex/i', 'Hi-Vis Rainwear'],
            ['/vest|gilet|bodywarmer/i', 'Hi-Vis Vests'],
            ['/coverall/i', 'Hi-Vis Coveralls'],
            ['/polo|t-shirt/i', 'Hi-Vis Polo Shirts & T-Shirts'],
            ['/fleece|hoodie|sweatshirt/i', 'Hi-Vis Fleece & Hoodies'],
            ['/shirt/i', 'Hi-Vis Shirts'],
            ['/pants|trousers|shorts/i', 'Hi-Vis Pants'],
            ['/jacket|softshell|bomber|coat/i', 'Hi-Vis Jackets'],
        ]],
        ['/head ?light|cap\b|beanie|\bhat\b|knee pad|support belt|holdall|\bbag\b|cooling/i', 'Safety Accessories', [
            ['/head ?light/i', 'Head Lights'],
            ['/cooling/i', 'Cooling Products'],
            ['/knee pad/i', 'Knee Pads'],
            ['/support belt/i', 'Support Belts'],
            ['/holdall|\bbag\b/i', 'Bags'],
            ['/./', 'Headwear'],
        ]],
        ['/./', 'Workwear', [
            ['/\brain/i', 'Rainwear'],
            ['/coverall/i', 'Work Coveralls'],
            ['/shorts/i', 'Work Shorts'],
            ['/pants?\b|trousers/i', 'Work Pants'],
            ['/polo|t-shirt/i', 'Polo Shirts & T-Shirts'],
            ['/fleece|hoodie|sweatshirt/i', 'Fleece & Hoodies'],
            ['/shirt/i', 'Work Shirts'],
            ['/jacket|softshell|gilet|vest/i', 'Jackets & Softshells'],
        ]],
    ];

    // Product name -> [sub-category, type|null, fourth level|null]
    protected function classify(string $name): array
    {
        foreach (self::CATEGORY_RULES as [$pattern, $mid, $types]) {
            if (!preg_match($pattern, $name)) continue;

            foreach ($types as $type) {
                if (!preg_match($type[0], $name)) continue;

                $fourth = null;
                foreach ($type[2] ?? [] as $fourthPattern => $fourthName) {
                    if (preg_match($fourthPattern, $name)) { $fourth = $fourthName; break; }
                }

                return [$mid, $type[1], $fourth];
            }

            return [$mid, null, null];
        }

        return ['Workwear', null, null];
    }

    // Only categories this import creates are marked as Portwest's. Existing shared ones
    // (Safety, Footwear and its branches) are reused as they are — stamping them with
    // Portwest's supplier id would make them look like Portwest-only categories.
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
    //  parents    - parent SKU => its product name (Group Name), description and image
    //  variations - SKU of every priced "variation" row (unique in the file) =>
    //               [parent SKU, size label] — e.g. "XL" from "{parent name} - XL"
    //  groupImage - product name => its main image (the first parent family's image)
    // A few products are split over two parent rows with the same Group Name (e.g. small
    // and large sizes under different style codes). They become one product with all the
    // sizes; a size both parents offer keeps its plain label for the first parent and gets
    // the parent's style code for the second ("L (2852NVR)"), so no SKU is dropped.
    protected function buildIndex(): array
    {
        $parents      = [];
        $variations   = [];
        $groupImage   = [];
        $sizesInGroup = [];
        $parent       = null;

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
                continue;
            }

            $sku = trim((string) ($mapped['sku'] ?? ''));
            if ($parent === null || $parent['group'] === '' || $sku === '' || !$this->hasValidPrice($mapped)) {
                continue;
            }

            $size = $this->sizeLabel($parent, (string) ($mapped['group_name'] ?? ''), $sku);
            $key  = mb_strtolower($size);
            if (isset($sizesInGroup[$parent['group']][$key])) {
                $size = "{$size} ({$parent['sku']})";
            }
            $sizesInGroup[$parent['group']][mb_strtolower($size)] = true;

            $variations[$sku] = [$parent['sku'], $size];
        }

        return ['parents' => $parents, 'variations' => $variations, 'groupImage' => $groupImage];
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

        if ($parent['sku'] !== '' && stripos($sku, $parent['sku'] . ' ') === 0) {
            $label = trim(substr($sku, strlen($parent['sku'])));
            if ($label !== '') return $label;
        }

        return $sku;
    }
}
