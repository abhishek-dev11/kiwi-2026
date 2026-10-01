<?php

namespace App\Jobs;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;

class PremiumApparelProductImportJob extends ProductImportJob
{
    // GET /api/products has no page param — it returns the entire active catalogue in
    // one response (confirmed live: 442 products, ~12.4k SKU rows across colours/sizes).
    // Processing that many rows' category/product/variant upserts in one job took ~795s
    // end-to-end (confirmed live) — well past the import page's 5-minute no-progress
    // "hard stop", which showed a false "Import failed" even though the job was still
    // legitimately running. So each job instance now only processes one CHUNK of
    // already-fetched products and self-dispatches the next, same idiom as Trends/Fashion
    // Biz's page-by-page self-chaining — real completed_chunks progress every ~60-90s
    // instead of a single silent 13-minute step.
    public $timeout = 300;

    const PRODUCTS_PER_CHUNK = 30;

    protected int $chunkIndex;

    public function __construct(string $importId, int $supplierId, int $chunkIndex = 0)
    {
        $this->importId   = $importId;
        $this->supplierId = $supplierId;
        $this->chunkIndex = $chunkIndex;

        // Only used for log messages in the parent class — not read from anywhere functionally.
        $this->path        = "premiumapparel:chunk={$chunkIndex}";
        $this->startRow    = $chunkIndex;
        $this->endRow      = $chunkIndex;
        $this->totalChunks = 0;
    }

    // =========================================================
    // SLICE THE (CACHED) FULL CATALOGUE INTO ONE CHUNK
    // =========================================================
    protected function readRows(): array
    {
        // Fetched once per import run and cached to a local file so every later chunk
        // just slices from it instead of re-hitting the API. Deliberately a FILE, not
        // the (database-backed) cache() store: an earlier version cached the fully
        // flattened ~12.4k-row array there and it blew past MySQL's max_allowed_packet
        // (confirmed live: "SQLSTATE[HY000]: General error: 2006 MySQL server has gone
        // away" while writing it), because flattening repeats each product's full HTML
        // description once per SKU row. Caching the raw (un-flattened) products list to
        // a file sidesteps both problems — no DB packet limit, and no duplication, since
        // flattening now happens per-chunk on a small slice instead of once up front.
        $cachePath = "imports/premiumapparel_{$this->importId}.json";

        if (Storage::disk('local')->exists($cachePath)) {
            $products = json_decode(Storage::disk('local')->get($cachePath), true) ?? [];
        } else {
            $products = $this->fetchProducts();
            Storage::disk('local')->put($cachePath, json_encode($products));
        }

        $totalChunks = max(1, (int) ceil(count($products) / self::PRODUCTS_PER_CHUNK));

        // The controller seeds total_chunks=1 before the real product count is known (the
        // fetch happens in here, not in the controller) — correct it now that chunk 0
        // has the real figure, same idea as Trends/Fashion Biz growing total_chunks as
        // more pages turn up, just resolved in one shot instead of incrementally.
        if ($this->chunkIndex === 0) {
            $this->correctTotalChunks($totalChunks);
        }

        if ($this->chunkIndex + 1 < $totalChunks) {
            //dispatch(new self($this->importId, $this->supplierId, $this->chunkIndex + 1))->onQueue('imports');
            dispatch(new self($this->importId, $this->supplierId, $this->chunkIndex + 1))->handle();
        } else {
            // Last chunk — done with the cached catalogue, clean up the temp file.
            Storage::disk('local')->delete($cachePath);
        }

        $slice = array_slice($products, $this->chunkIndex * self::PRODUCTS_PER_CHUNK, self::PRODUCTS_PER_CHUNK);

        return $this->flattenProductsToRows($slice);
    }

    protected function correctTotalChunks(int $totalChunks): void
    {
        $metaKey = "import_meta_{$this->importId}";
        $lock    = cache()->lock("import_lock_{$this->importId}", 15);

        try {
            $lock->block(30);

            $meta = cache()->get($metaKey, []);
            if (!empty($meta)) {
                $meta['total_chunks'] = $totalChunks;
                cache()->put($metaKey, $meta, now()->addHours(3));
            }
        } catch (\Illuminate\Contracts\Cache\LockTimeoutException $e) {
            \Log::warning("PremiumApparelProductImportJob: Could not acquire lock to set total_chunks [{$this->importId}]: " . $e->getMessage());
        } finally {
            optional($lock)->release();
        }
    }

    // =========================================================
    // FETCH THE FULL CATALOGUE FROM THE PREMIUM APPAREL API
    // =========================================================
    protected function fetchProducts(): array
    {
        $baseUrl = rtrim(config('services.premiumapparel.base_url'), '/');
        $token   = config('services.premiumapparel.token');

        $request = Http::acceptJson()->timeout(180);
        if (!empty($token)) {
            $request = $request->withToken($token);
        }

        $response = $request->get("{$baseUrl}/products");

        if ($response->failed()) {
            throw new \RuntimeException(
                "Premium Apparel API request failed: HTTP {$response->status()} — {$response->body()}"
            );
        }

        $body = $response->json() ?? [];

        // Docs confirm error responses come back as {"error":true,"error_reason":"..."}
        // rather than a non-2xx status, so the payload shape needs its own check too.
        if (!empty($body['error'])) {
            throw new \RuntimeException(
                'Premium Apparel API returned an error: ' . ($body['error_reason'] ?? 'unknown error')
            );
        }

        return $body['products'] ?? [];
    }

    // =========================================================
    // MAP API RESPONSE → THE ROW SHAPE ProductImportJob EXPECTS
    // =========================================================
    protected function flattenProductsToRows(array $products): array
    {
        $rows = [];

        foreach ($products as $product) {
            $colours = $product['colours'] ?? [];

            // No colour/size breakdown at all — confirmed live (~67 of 442 products,
            // e.g. relabel/sample-only accessories): these carry no SKU/stock/price data
            // whatsoever, so there's nothing sellable to import.
            if (empty($colours)) {
                continue;
            }

            $style       = (string) ($product['style'] ?? '');
            $title       = trim((string) ($product['title'] ?? ''));
            $groupName   = $title ?: $style;
            $description = $product['html_description'] ?? ($product['text_description'] ?? '');

            // Confirmed live: 'category' is a real, populated field (e.g. "Headwear",
            // "T-Shirt", "Netball", "Hi-Vis") mixing garment types with sport/use-case
            // buckets — not a clean apparel-only hierarchy, so it's used verbatim as the
            // top-level category rather than nested under a fixed "Apparel" bucket (same
            // approach as AS Colour's productType). A handful of "relabel" service items
            // have no category at all — bucketed separately rather than dropped.
            $category = trim((string) ($product['category'] ?? '')) ?: 'Uncategorised';

            $heroImages = array_values(array_filter(array_map('trim', $product['hero'] ?? [])));

            foreach ($colours as $colour) {
                $colourName = trim((string) ($colour['description'] ?? ($colour['name'] ?? ''))) ?: 'Default';
                $images     = $this->extractImageUrls(array_merge($colour['images'] ?? [], $heroImages));

                foreach ($colour['sizes'] ?? [] as $sizeRow) {
                    // Confirmed live: price is per-SKU (varies within a colour, e.g. S vs
                    // 2XL), not a single per-product figure — read it off each size row.
                    // A small number of rows (~6 of 12,388 confirmed live) have no price;
                    // skip just that SKU rather than the whole product/colour.
                    $price = (float) ($sizeRow['price'] ?? 0);
                    if ($price <= 0) {
                        continue;
                    }

                    // Confirmed live: sizes[].sku is the real style-colour-size code
                    // (e.g. "A101CVC2BL-LS") — used as-is rather than synthesized.
                    $sku = $sizeRow['sku'] ?? ($style . '-' . ($sizeRow['size'] ?? ''));

                    $rows[] = $this->buildRow(
                        $sku, $title, $colourName, $sizeRow['size'] ?? 'Default', $groupName,
                        $description, $category, $price, $images, (int) ($sizeRow['stock'] ?? 0)
                    );
                }
            }
        }

        return $rows;
    }

    protected function buildRow(
        string $sku, string $itemName, string $colour, string $size, string $groupName,
        string $description, string $category, float $price, array $images, int $inventory
    ): array {
        $row = [
            'sku'              => $sku,
            'item_name'        => $itemName,
            'colour'           => $colour,
            'size'             => $size,
            'group_name'       => $groupName,
            'description_html' => $description,
            'main_category'    => $category,
            'default_price'    => $price,
            'inventory'        => $inventory,
        ];

        foreach ($images as $index => $url) {
            if ($index >= 6) break;
            $row['image_' . ($index + 1)] = $url;
        }

        return $row;
    }

    protected function extractImageUrls(array $images): array
    {
        $urls = [];
        foreach ($images as $url) {
            $url = trim((string) $url);
            if ($url !== '' && !in_array($url, $urls, true)) {
                $urls[] = $url;
            }
            if (count($urls) >= 6) break;
        }

        return $urls;
    }
}
