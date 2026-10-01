<?php

namespace App\Jobs;

use Illuminate\Support\Facades\Http;

class BanburyProductImportJob extends ProductImportJob
{
    // Safety cap so a mis-detected "more pages" heuristic can never loop forever.
    const MAX_PAGES = 500;

    protected int $page;
    protected int $perPage;

    public function __construct(string $importId, int $supplierId, int $page, int $perPage = 30)
    {
        $this->importId   = $importId;
        $this->supplierId = $supplierId;
        $this->page       = $page;
        $this->perPage    = $perPage;

        // Only used for log messages in the parent class — not read from anywhere functionally.
        $this->path        = "banbury:page={$page}";
        $this->startRow    = $page;
        $this->endRow      = $page;
        $this->totalChunks = 0;
    }

    // =========================================================
    // FETCH ONE PAGE FROM THE BANBURY API
    // =========================================================
    protected function readRows(): array
    {
        if ($this->page > self::MAX_PAGES) {
            \Log::warning("BanburyProductImportJob [{$this->importId}]: page {$this->page} exceeds MAX_PAGES safety cap — stopping.");
            return [];
        }

        $baseUrl  = rtrim(config('services.banbury.base_url'), '/');
        $username = config('services.banbury.username');
        $password = config('services.banbury.password');

        $response = Http::withBasicAuth($username, $password)
            ->acceptJson()
            ->timeout(60)
            ->get("{$baseUrl}/api/banbury/products", [
                'pageNum'  => $this->page,
                'pageSize' => $this->perPage,
            ]);

        if ($response->failed()) {
            throw new \RuntimeException(
                "Banbury API request failed (page {$this->page}): HTTP {$response->status()} — {$response->body()}"
            );
        }

        $body = $response->json() ?? [];

        // Documented/example shape is a bare JSON array of products (no pagination wrapper).
        // Defensively unwrap in case a live response ever nests it under "data".
        $products = isset($body['data']) && is_array($body['data']) ? $body['data'] : $body;

        if ($this->hasMorePages($body, $products)) {
            $this->dispatchNextPage();
        }

        return $this->flattenProductsToRows($products);
    }

    protected function hasMorePages(array $body, array $products): bool
    {
        // In case a live response ever adds pagination metadata alongside "data".
        $meta = $body['meta'] ?? $body['pagination'] ?? null;
        if (is_array($meta) && isset($meta['current_page'], $meta['total_pages'])) {
            return (int) $meta['current_page'] < (int) $meta['total_pages'];
        }
        if (is_array($meta) && isset($meta['current_page'], $meta['last_page'])) {
            return (int) $meta['current_page'] < (int) $meta['last_page'];
        }
        if (is_array($meta) && array_key_exists('next', $meta)) {
            return !empty($meta['next']);
        }

        // No pagination metadata at all — assume a full page means there could be more.
        return count($products) >= $this->perPage;
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
            \Log::warning("BanburyProductImportJob: Could not acquire lock to grow total_chunks [{$this->importId}]: " . $e->getMessage());
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
            $styleCode = $product['styleCode'] ?? '';
            $name      = $product['name'] ?? '';

            // groupRows() saves this verbatim as the product title — bake the style code
            // in so two different styles that happen to share a name don't get merged.
            $groupName = $styleCode !== '' && stripos($name, $styleCode) === false
                ? trim($name . ' - ' . $styleCode)
                : ($name ?: $styleCode);

            [$mainCategory, $subCategory] = $this->splitCategory($product['categories'][0] ?? '');

            $productImages = array_values(array_filter((array) ($product['images'] ?? [])));
            $options       = $product['productOptions'] ?? [];

            if (empty($options)) {
                $rows[] = $this->buildRow($product, $groupName, [], $mainCategory, $subCategory, $productImages);
                continue;
            }

            foreach ($options as $option) {
                $rows[] = $this->buildRow($product, $groupName, $option, $mainCategory, $subCategory, $productImages);
            }
        }

        return $rows;
    }

    protected function buildRow(
        array $product, string $groupName, array $option,
        string $mainCategory, string $subCategory, array $productImages
    ): array {
        $images = $this->extractImageUrls($option['image'] ?? null, $productImages);

        $row = [
            'sku'              => $option['sizeCode'] ?? ($option['code'] ?? ($product['styleCode'] ?? '')),
            'item_name'        => $product['name'] ?? '',
            'colour'           => $option['optionValue'] ?? ($option['optionLabel'] ?? ''),
            'size'             => $option['size'] ?? '',
            'group_name'       => $groupName,
            'description_html' => $product['description'] ?? '',
            'main_category'    => $mainCategory,
            'sub_sub_category' => $subCategory,
            'inventory'        => $option['stockAvailable'] ?? 0,
            'default_price'    => $option['price'] ?? 0,
        ];

        foreach ($images as $index => $url) {
            $row['image_' . ($index + 1)] = $url;
        }

        return $row;
    }

    // "Kids > Kids Hoodies & Sweats" → ["Kids", "Kids Hoodies & Sweats"]; "Custom" → ["Custom", ""]
    protected function splitCategory(string $category): array
    {
        if ($category === '') {
            return ['', ''];
        }

        $parts = array_map('trim', explode('>', $category, 2));

        return [$parts[0] ?? '', $parts[1] ?? ''];
    }

    protected function extractImageUrls(?string $variantImage, array $productImages): array
    {
        $urls = [];

        foreach (array_merge([$variantImage], $productImages) as $url) {
            if (!empty($url) && !in_array($url, $urls, true)) {
                $urls[] = $url;
            }
            if (count($urls) >= 6) break;
        }

        return $urls;
    }
}
