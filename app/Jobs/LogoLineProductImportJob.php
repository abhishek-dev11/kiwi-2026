<?php

namespace App\Jobs;

use App\Services\LogoLineAuthService;
use Illuminate\Support\Facades\Http;

class LogoLineProductImportJob extends ProductImportJob
{
    // API sync: keep serving the supplier's image URLs — see ProductImportJob::$downloadProductImages.
    protected bool $downloadProductImages = false;

    // GET /v4/Products has no page param — it returns the entire catalogue in one
    // response (confirmed live: ~960 products, ~7 MB). Combined with the documented
    // per-function rate limit (24 requests/day, 1 per 10-minute window), there is no
    // pagination to self-chain — this job fetches once and processes everything.
    // Bump past the parent's 600 s default to leave headroom for the ~7 MB fetch
    // plus DB upserts across the full catalogue.
    public $timeout = 900;

    public function __construct(string $importId, int $supplierId)
    {
        $this->importId   = $importId;
        $this->supplierId = $supplierId;

        // Only used for log messages in the parent class — not read from anywhere functionally.
        $this->path        = 'logoline:full-catalogue';
        $this->startRow    = 1;
        $this->endRow      = 1;
        $this->totalChunks = 1;
    }

    // =========================================================
    // FETCH THE FULL CATALOGUE FROM THE LOGO LINE API
    // =========================================================
    protected function readRows(): array
    {
        $baseUrl     = rtrim(config('services.logoline.base_url'), '/');
        $accessToken = app(LogoLineAuthService::class)->getAccessToken();

        $response = Http::withToken($accessToken)
            ->acceptJson()
            ->timeout(180)
            ->get("{$baseUrl}/v4/Products", [
                'stock' => 'true',
            ]);

        if ($response->failed()) {
            throw new \RuntimeException(
                "Logo Line API request failed: HTTP {$response->status()} — {$response->body()}"
            );
        }

        $body     = $response->json() ?? [];
        $products = $body['data'] ?? [];

        return $this->flattenProductsToRows($products);
    }

    // =========================================================
    // MAP API RESPONSE → THE ROW SHAPE ProductImportJob EXPECTS
    // =========================================================
    protected function flattenProductsToRows(array $products): array
    {
        $rows = [];

        foreach ($products as $product) {
            // Only the BASE row carries the undecorated unit price; products with
            // none (discontinued/delisted — confirmed live: ~170 of ~960) have
            // nothing sellable to import.
            $baseRows = array_values(array_filter(
                $product['prices'] ?? [],
                fn($row) => ($row['rowType'] ?? '') === 'BASE'
            ));
            if (empty($baseRows)) {
                continue;
            }
            // Lowest-quantity tier = the standard "starting from" unit price. Sort
            // defensively rather than trust response order, since qty tiers drive price.
            $tiers = $baseRows[0]['prices'] ?? [];
            usort($tiers, fn($a, $b) => ($a['qty'] ?? 0) <=> ($b['qty'] ?? 0));
            $defaultPrice = (float) ($tiers[0]['price'] ?? 0);
            if ($defaultPrice <= 0) {
                continue;
            }

            $code = $product['code'] ?? '';
            $name = $product['name'] ?? '';

            // groupRows() saves this verbatim as the product title — bake the product
            // code in so two products that happen to share a name don't get merged.
            $groupName = $code !== '' && stripos($name, $code) === false
                ? trim($name . ' - ' . $code)
                : ($name ?: $code);

            $mainCategory = $product['collection'] ?: ($product['categories'][0]['name'] ?? '');
            $subCategory  = $product['category'] ?: ($product['categories'][0]['categories'][0]['name'] ?? '');

            $imageBase = rtrim((string) ($product['imageBaseUrl'] ?? ''), '/');
            $mainImage = ($imageBase !== '' && !empty($product['imageFilename']))
                ? "{$imageBase}/{$product['imageFilename']}"
                : '';

            // Per-colour swatch photos, keyed by colour name so each stock-level
            // row below can pick its own — falls back to the main product photo
            // when a colour has no dedicated image (confirmed live: happens when
            // colourImages is empty but stockLevels/coloursAvailable is not).
            $colourImages = [];
            foreach ($product['colourImages'] ?? [] as $ci) {
                if ($imageBase !== '' && !empty($ci['name']) && !empty($ci['filename'])) {
                    $colourImages[strtolower(trim($ci['name']))] = "{$imageBase}/{$ci['filename']}";
                }
            }

            $otherImages = [];
            foreach ($product['otherImages'] ?? [] as $oi) {
                if ($imageBase !== '' && !empty($oi['filename'])) {
                    $otherImages[] = "{$imageBase}/{$oi['filename']}";
                }
            }

            $description = $product['description'] ?? '';
            $stockLevels = $product['stockLevels'] ?? [];

            // No stock/colour breakdown at all — import as a single simple-product row.
            if (empty($stockLevels)) {
                $rows[] = $this->buildRow(
                    $code, $name, 'Default', 0, $defaultPrice, $groupName, $description,
                    $mainCategory, $subCategory, $mainImage, $colourImages, $otherImages, null
                );
                continue;
            }

            // stockLevels[].code is Logo Line's internal stock/component code, not a
            // per-product SKU — confirmed live that it's frequently shared across dozens
            // of unrelated products (e.g. one generic packaging-sleeve code reused on 60+
            // different pens). Build the SKU from this product's own unique `code` instead,
            // suffixed by colour so each line stays distinct within the product.
            $skuCounts = [];
            foreach ($stockLevels as $stockLevel) {
                $colour = $stockLevel['description'] ?: 'Default';
                $sku    = count($stockLevels) > 1
                    ? $code . '-' . (preg_replace('/[^A-Za-z0-9]+/', '', $colour) ?: 'VAR')
                    : $code;

                $skuCounts[$sku] = ($skuCounts[$sku] ?? 0) + 1;
                if ($skuCounts[$sku] > 1) {
                    $sku .= '-' . $skuCounts[$sku];
                }

                $rows[] = $this->buildRow(
                    $sku, $name, $colour, (int) ($stockLevel['qty'] ?? 0),
                    $defaultPrice, $groupName, $description, $mainCategory, $subCategory,
                    $mainImage, $colourImages, $otherImages, $colour
                );
            }
        }

        return $rows;
    }

    protected function buildRow(
        string $sku, string $itemName, string $colour, int $inventory, float $price,
        string $groupName, string $description, string $mainCategory, string $subCategory,
        string $mainImage, array $colourImages, array $otherImages, ?string $matchColour
    ): array {
        $row = [
            'sku'              => $sku,
            'item_name'        => $itemName,
            'colour'           => $colour,
            'size'             => 'Default',
            'group_name'       => $groupName,
            'description_html' => $description,
            'main_category'    => $mainCategory,
            'sub_sub_category' => $subCategory,
            'inventory'        => $inventory,
            'default_price'    => $price,
        ];

        $images = $this->extractImageUrls($mainImage, $matchColour, $colourImages, $otherImages);
        foreach ($images as $index => $url) {
            $row['image_' . ($index + 1)] = $url;
        }

        return $row;
    }

    // image_1 (the row's "main" photo) prefers this variant's own colour swatch,
    // falling back to the shared product photo; image_2..6 (gallery) fill in with
    // the rest of the colour swatches and other-images, deduped.
    protected function extractImageUrls(
        string $mainImage, ?string $matchColour, array $colourImages, array $otherImages
    ): array {
        $urls = [];

        $ownColourImage = $matchColour ? ($colourImages[strtolower(trim($matchColour))] ?? null) : null;

        $candidates = array_merge([$ownColourImage, $mainImage], array_values($colourImages), $otherImages);

        foreach ($candidates as $url) {
            if (!empty($url) && !in_array($url, $urls, true)) {
                $urls[] = $url;
            }
            if (count($urls) >= 6) break;
        }

        return $urls;
    }
}
