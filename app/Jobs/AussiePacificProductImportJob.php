<?php

namespace App\Jobs;

use Illuminate\Support\Facades\Http;

class AussiePacificProductImportJob extends ProductImportJob
{
    // Safety cap so a mis-detected "more pages" heuristic can never loop forever.
    const MAX_PAGES = 500;

    protected int $page;
    protected int $perPage;

    // The API gets exponentially slower per product as page size grows (limit=250 times
    // out entirely; limit=25 takes ~5s, limit=100 takes ~25s) — keep pages small.
    public function __construct(string $importId, int $supplierId, int $page, int $perPage = 25)
    {
        $this->importId   = $importId;
        $this->supplierId = $supplierId;
        $this->page       = $page;
        $this->perPage    = $perPage;

        // Only used for log messages in the parent class — not read from anywhere functionally.
        $this->path        = "aussiepacific:page={$page}";
        $this->startRow    = $page;
        $this->endRow      = $page;
        $this->totalChunks = 0;
    }

    // =========================================================
    // FETCH ONE PAGE FROM THE AUSSIE PACIFIC API
    // =========================================================
    protected function readRows(): array
    {
        if ($this->page > self::MAX_PAGES) {
            \Log::warning("AussiePacificProductImportJob [{$this->importId}]: page {$this->page} exceeds MAX_PAGES safety cap — stopping.");
            return [];
        }

        $baseUrl = rtrim(config('services.aussiepacific.base_url'), '/');
        $token   = config('services.aussiepacific.token');

        $response = Http::withToken($token)
            ->acceptJson()
            ->timeout(60)
            ->get("{$baseUrl}/api/v1/products", [
                'page'    => $this->page,
                'limit'   => $this->perPage,
                'include' => 'variants,images',
            ]);

        if ($response->failed()) {
            throw new \RuntimeException(
                "Aussie Pacific API request failed (page {$this->page}): HTTP {$response->status()} — {$response->body()}"
            );
        }

        $body     = $response->json() ?? [];
        $products = $body['data'] ?? [];

        // Docs show a single object as the example for a list endpoint — normalize just in case.
        if (isset($products['style_code']) || isset($products['name'])) {
            $products = [$products];
        }

        if ($this->hasMorePages($body, $products)) {
            $this->dispatchNextPage();
        }

        return $this->flattenProductsToRows($products);
    }

    protected function hasMorePages(array $body, array $products): bool
    {
        // Confirmed live shape: {"meta":{"pagination":{"current_page","total_pages","links":{"next"}}}}
        $pagination = $body['meta']['pagination'] ?? null;
        if (is_array($pagination) && isset($pagination['current_page'], $pagination['total_pages'])) {
            return (int) $pagination['current_page'] < (int) $pagination['total_pages'];
        }
        if (is_array($pagination) && array_key_exists('links', $pagination)) {
            return !empty($pagination['links']['next'] ?? null);
        }

        // Fallbacks in case the shape ever changes.
        $meta = $body['meta'] ?? null;
        if (is_array($meta) && isset($meta['current_page'], $meta['last_page'])) {
            return (int) $meta['current_page'] < (int) $meta['last_page'];
        }

        $links = $body['links'] ?? null;
        if (is_array($links) && array_key_exists('next', $links)) {
            return !empty($links['next']);
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
            \Log::warning("AussiePacificProductImportJob: Could not acquire lock to grow total_chunks [{$this->importId}]: " . $e->getMessage());
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
            // ProductImportJob::groupRows() saves this verbatim as the product title, so this
            // must be the human-readable name — the API already bakes the style code into it
            // (e.g. "ENDEAVOUR LADY POLOS - 2310"), which also keeps it unique per style.
            $groupName = ($product['name'] ?? '') ?: ($product['style_code'] ?? '') ?: ($product['style'] ?? '');
            $variants  = $product['variants']['data'] ?? [];

            if (empty($variants)) {
                $rows[] = $this->buildRow($product, $groupName, []);
                continue;
            }

            foreach ($variants as $variant) {
                $rows[] = $this->buildRow($product, $groupName, $variant);
            }
        }

        return $rows;
    }

    protected function buildRow(array $product, string $groupName, array $variant): array
    {
        $images     = $this->extractImageUrls($variant['images']['data'] ?? []);
        $row        = [
            'sku'               => $variant['sku']    ?? '',
            'item_name'         => $product['name']   ?? '',
            'colour'            => $variant['colour'] ?? '',
            'size'              => $variant['size']   ?? '',
            'group_name'        => $groupName,
            'description_html'  => $product['description'] ?? '',
            'main_category'     => $product['main_category'] ?? '',
            'sub_sub_category'  => $product['sub_category']  ?? '',
            'inventory'         => $variant['stock_level']   ?? '0',
            'default_price'     => $variant['price']         ?? 0,
        ];

        foreach ($images as $index => $url) {
            $row['image_' . ($index + 1)] = $url;
        }

        return $row;
    }

    protected function extractImageUrls(array $images): array
    {
        $urls = [];

        foreach (array_slice($images, 0, 6) as $image) {
            if (is_string($image)) {
                $urls[] = $image;
                continue;
            }

            if (is_array($image)) {
                // Confirmed live field: "filename" (already a full CDN URL). Others kept as fallbacks.
                foreach (['filename', 'url', 'image_url', 'path', 'src', 'image'] as $key) {
                    if (!empty($image[$key])) {
                        $urls[] = $image[$key];
                        continue 2;
                    }
                }
            }
        }

        return $urls;
    }
}
