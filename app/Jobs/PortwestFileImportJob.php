<?php

namespace App\Jobs;

// Portwest's file is WooCommerce-style (see WooCommerceFileImportJob for the shared
// variable / variation handling). Its only category is "Safety", so each product is placed
// in its most relevant sub-category worked out from its name (see CATEGORY_RULES).
class PortwestFileImportJob extends WooCommerceFileImportJob
{
    protected function indexName(): string
    {
        return 'portwest';
    }

    // The file only says "Safety" — every product is placed in its most relevant
    // sub-category (and type, where one fits) worked out from its name.
    protected function prepareRows(array $rows, array $index): array
    {
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
}
