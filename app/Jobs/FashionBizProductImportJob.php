<?php

namespace App\Jobs;

use Illuminate\Support\Facades\Http;

class FashionBizProductImportJob extends ProductImportJob
{
    // API sync: keep serving the supplier's image URLs — see ProductImportJob::$downloadProductImages.
    protected bool $downloadProductImages = false;

    // Safety cap so a mis-detected "more pages" heuristic can never loop forever.
    const MAX_PAGES = 500;

    protected int $page;
    protected int $perPage;

    // Confirmed live: 50 products/page (~371 KB) takes ~5-6s — keep pages modest
    // rather than risk a timeout on a slower day.
    public function __construct(string $importId, int $supplierId, int $page, int $perPage = 50)
    {
        $this->importId   = $importId;
        $this->supplierId = $supplierId;
        $this->page       = $page;
        $this->perPage    = $perPage;

        // Only used for log messages in the parent class — not read from anywhere functionally.
        $this->path        = "fashionbiz:page={$page}";
        $this->startRow    = $page;
        $this->endRow      = $page;
        $this->totalChunks = 0;
    }

    // =========================================================
    // FETCH ONE PAGE FROM THE FASHION BIZ API
    // =========================================================
    protected function readRows(): array
    {
        if ($this->page > self::MAX_PAGES) {
            \Log::warning("FashionBizProductImportJob [{$this->importId}]: page {$this->page} exceeds MAX_PAGES safety cap — stopping.");
            return [];
        }

        $baseUrl = rtrim(config('services.fashionbiz.base_url'), '/');
        $token   = config('services.fashionbiz.token');
        $brand   = config('services.fashionbiz.brand');
        $branch  = config('services.fashionbiz.branch');

        $response = Http::withHeaders([
                'Authorization' => 'Token ' . $token,
                'Content-Type'  => 'application/json',
            ])
            ->acceptJson()
            ->timeout(60)
            ->get("{$baseUrl}/api/v3/products/{$brand}/{$branch}/", [
                'page'     => $this->page,
                'per_page' => $this->perPage,
            ]);

        if ($response->failed()) {
            throw new \RuntimeException(
                "Fashion Biz API request failed (page {$this->page}): HTTP {$response->status()} — {$response->body()}"
            );
        }

        $body     = $response->json() ?? [];
        $products = $body['products'] ?? [];

        if ($this->hasMorePages($body)) {
            $this->dispatchNextPage();
        }

        return $this->flattenProductsToRows($products);
    }

    protected function hasMorePages(array $body): bool
    {
        // Confirmed live shape: {"current","next","links":{"next","previous"},"total_count","products":[...]}
        if (array_key_exists('links', $body) && is_array($body['links'])) {
            return !empty($body['links']['next'] ?? null);
        }

        return !empty($body['next'] ?? null);
    }

    protected function dispatchNextPage(): void
    {
        $metaKey = "import_meta_{$this->importId}";
        $lock    = cache()->lock("import_lock_{$this->importId}", 15);

        try {
            $lock->block(30);

            $meta = cache()->get($metaKey, []);
            if (!empty($meta)) {
                $meta['total_chunks'] = (int) ($meta['total_chunks'] ?? 1) + 1;
                cache()->put($metaKey, $meta, now()->addHours(3));
            }
        } catch (\Illuminate\Contracts\Cache\LockTimeoutException $e) {
            \Log::warning("FashionBizProductImportJob: Could not acquire lock to grow total_chunks [{$this->importId}]: " . $e->getMessage());
        } finally {
            optional($lock)->release();
        }

        //dispatch(new self($this->importId, $this->supplierId, $this->page + 1, $this->perPage))->onQueue('imports');
        dispatch(new self($this->importId, $this->supplierId, $this->page + 1, $this->perPage))->handle();
    }

    // =========================================================
    // MAP API RESPONSE → THE ROW SHAPE ProductImportJob EXPECTS
    // =========================================================
    protected function flattenProductsToRows(array $products): array
    {
        $rows = [];

        foreach ($products as $product) {
            $code = $product['code'] ?? '';
            $name = $product['name'] ?? '';

            // groupRows() saves this verbatim as the product title — bake the product
            // code in so two products that happen to share a name don't get merged
            // (same convention as Banbury/Logo Line).
            $groupName = $code !== '' && stripos($name, $code) === false
                ? trim($name . ' - ' . $code)
                : ($name ?: $code);

            // The API's own category data is just a flat, unordered tags bag (mixes
            // product type/fabric/gender/industry with no consistent order — confirmed
            // live, e.g. "polos, focus, short sleeve, polyester, school education, sports
            // teams"), unusable as a category hierarchy. Every Biz Collection product name
            // instead reliably encodes gender + garment type (e.g. "Venture Womens Short
            // Sleeve Polo"), so the 4-level category tree is built from the name instead,
            // reusing the exact top/mid/bottom/fourth scheme already used for other apparel
            // suppliers (top_level "Apparel" id=1 → mid = garment type → bottom = gender →
            // fourth = sleeve length).
            $garmentType = $this->extractGarmentType($name);
            $gender      = $this->extractGender($name);
            $sleeve      = $this->extractSleeve($name);

            $price = $this->extractBasePrice($product['prices'] ?? []);

            $productImages = $this->extractImageUrls($product['images'] ?? []);
            $colors        = $product['colors'] ?? [];

            if (empty($colors)) {
                $rows[] = $this->buildRow(
                    $code, $name, 'Default', 'Default', $groupName,
                    $garmentType, $gender, $sleeve, $price, $productImages
                );
                continue;
            }

            foreach ($colors as $color) {
                $colourName   = $color['name'] ?? 'Default';
                $colourImages = $this->extractImageUrls($color['images'] ?? []);
                $images       = $this->mergeImages($colourImages, $productImages);
                $sizes        = $color['sizes'] ?? [];

                if (empty($sizes)) {
                    $rows[] = $this->buildRow(
                        $code, $name, $colourName, 'Default', $groupName,
                        $garmentType, $gender, $sleeve, $price, $images
                    );
                    continue;
                }

                foreach ($sizes as $sizeRow) {
                    // Confirmed live: sizes[].sku is the product's real barcode, unique
                    // per colour/size — used as-is rather than synthesized.
                    $sku = $sizeRow['sku'] ?? ($code . '-' . ($sizeRow['size'] ?? ''));

                    $rows[] = $this->buildRow(
                        $sku, $name, $colourName, $sizeRow['size'] ?? 'Default', $groupName,
                        $garmentType, $gender, $sleeve, $price, $images
                    );
                }
            }
        }

        return $rows;
    }

    protected function buildRow(
        string $sku, string $itemName, string $colour, string $size, string $groupName,
        string $garmentType, string $gender, string $sleeve, float $price, array $images
    ): array {
        $row = [
            'sku'                   => $sku,
            'item_name'             => $itemName,
            'colour'                => $colour,
            'size'                  => $size,
            'group_name'            => $groupName,
            'description_html'      => '', // not present in this list endpoint (out of scope — see readRows())
            'main_category'         => 'Apparel',
            'sub_sub_category'      => $garmentType,
            'sub_sub_category_2'    => $gender,
            'sub_sub_sub_category'  => $sleeve,
            'default_price'         => $price,
            // 'inventory' intentionally omitted — this endpoint carries no stock data,
            // so ProductImportJob::groupRows() defaults to 999 (treated as in-stock),
            // same as Augmento/JBswear's price-list-only suppliers.
        ];

        foreach ($images as $index => $url) {
            $row['image_' . ($index + 1)] = $url;
        }

        return $row;
    }

    protected function extractBasePrice(array $prices): float
    {
        foreach ($prices as $tier) {
            if (($tier['tier'] ?? '') === '1-99') {
                return (float) ($tier['price'] ?? 0);
            }
        }

        return (float) ($prices[0]['price'] ?? 0);
    }

    protected function extractImageUrls(array $images): array
    {
        usort($images, fn($a, $b) => ($a['index'] ?? 0) <=> ($b['index'] ?? 0));

        $urls = [];
        foreach ($images as $image) {
            $url = $image['https_attachment_url_product'] ?? ($image['https_attachment_url'] ?? '');
            if (!empty($url)) $urls[] = $url;
        }

        return $urls;
    }

    protected function mergeImages(array $primary, array $fallback): array
    {
        $urls = [];
        foreach (array_merge($primary, $fallback) as $url) {
            if (!empty($url) && !in_array($url, $urls, true)) {
                $urls[] = $url;
            }
            if (count($urls) >= 6) break;
        }

        return $urls;
    }

    // "Venture Womens Short Sleeve Polo" → "Ladies"; matched before "Mens" since "Mens"
    // only matches on a word boundary and "Womens" doesn't create one mid-word.
    protected function extractGender(string $name): string
    {
        if (preg_match('/\b(Womens?|Ladies)\b/i', $name)) return 'Ladies';
        if (preg_match('/\bUnisex\b/i', $name))            return 'Unisex';
        if (preg_match('/\bKids?\b/i', $name))             return 'Kids';
        if (preg_match('/\bMens?\b/i', $name))             return 'Mens';

        return '';
    }

    // Ordered most-specific-first so e.g. "Scrub Top" doesn't fall through to the
    // generic "Top" bucket, and "Short Sleeve X" doesn't get misread as "Shorts".
    protected function extractGarmentType(string $name): string
    {
        $patterns = [
            '/\bScrub\s+Top/i'              => 'Scrub Tops',
            '/\bScrub\s+Pant/i'             => 'Scrub Pants',
            '/\bCardigan/i'                  => 'Cardigans',
            '/\bPolo/i'                      => 'Polos',
            '/\bHoodie/i'                    => 'Hoodies',
            '/\b(Jumper|Pullover)/i'         => 'Jumpers',
            '/\bJacket/i'                    => 'Jackets',
            '/\bVest/i'                      => 'Vests',
            '/Blouse/i'                      => 'Blouses', // no leading \b — catches "Overblouse"
            '/\bTunic/i'                     => 'Tunics',
            '/\bDress/i'                     => 'Dresses',
            '/\bSkort/i'                     => 'Skorts',
            '/\bSkirt/i'                     => 'Skirts',
            '/\bSinglet/i'                   => 'Singlets',
            '/\bTrackpant/i'                 => 'Trackpants',
            '/\bTracktop/i'                  => 'Tracktops',
            '/\b(T-?Shirt|Tee)\b/i'          => 'Tees',
            '/\bShirt/i'                     => 'Shirt',
            '/\bLeggings?\b/i'               => 'Leggings',
            '/\bFleece\b/i'                  => 'Fleece',
            '/\bBelt\b/i'                    => 'Belts',
            '/\bApron\b/i'                   => 'Aprons',
            '/\bStraps?\b/i'                 => 'Aprons', // spare apron-strap accessories
            '/\b(Bandana|Cap|Hat)\b/i'       => 'Headwear',
            '/\bZippies\b/i'                 => 'Accessories',
            '/\bShorts?\b(?!\s+Sleeve)/i'    => 'Shorts',
            '/\bPant/i'                      => 'Pants',
            '/\bTop\b/i'                     => 'Tops',
        ];

        foreach ($patterns as $pattern => $category) {
            if (preg_match($pattern, $name)) return $category;
        }

        return '';
    }

    protected function extractSleeve(string $name): string
    {
        if (preg_match('#3/4\s+Sleeve#i', $name))  return '3/4 Sleeve';
        if (preg_match('/\bShort\s+Sleeve/i', $name)) return 'Short Sleeve';
        if (preg_match('/\bLong\s+Sleeve/i', $name))  return 'Long Sleeve';
        if (preg_match('/\bSleeveless\b/i', $name))   return 'Sleeveless';

        return '';
    }
}
