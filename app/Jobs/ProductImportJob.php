<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Cache;
use App\Models\Products;
use App\Models\Attributes;
use App\Models\ProductVariants;
use App\Models\ProductVariantImages;
use App\Models\ProductSubVariants;
use App\Models\ProducSubtVariantItems;
use App\Models\Categories;
use App\Support\ProductImageDownloader;
use App\Support\ProductTextCleaner;
use PhpOffice\PhpSpreadsheet\IOFactory;

class ProductImportJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    // A chunk is retried (with backoff) before it is reported as failed. A retry is safe:
    // products are upserted by title + supplier, and a chunk already recorded as done in
    // the import's ledger (see recordChunk()) is skipped.
    public $timeout       = 900;
    public $tries         = 3;
    public $failOnTimeout = true;

    public function backoff(): array
    {
        return [15, 60];
    }

    protected string $path;
    protected string $importId;
    protected int    $supplierId;
    protected int    $startRow;
    protected int    $endRow;
    protected int    $totalChunks;

    protected int $defaultQuantity = 999;

    // ── Image storage path (relative to public/) ──
    const IMAGE_STORAGE_PATH = 'uploads/Product';

    // CSV/XLSX imports download each product's image URL into IMAGE_STORAGE_PATH and
    // store the local filename in products.image (the original URL is kept in
    // products.image_url). The API-sync subclasses turn this off — they sync thousands
    // of products per run and keep serving the supplier's own image URLs.
    protected bool $downloadProductImages = true;

    // When not downloading (API jobs): true checks that each URL is reachable before storing
    // it (blank if not). A job can turn this off to store the URL as given, with no request
    // to the supplier — see FoxAndGunnFileImportJob.
    protected bool $verifyImageUrls = true;

    // Remote URL -> local filename (or null on failure) for this job run, so a URL shared
    // by several colour groups / products is only fetched once.
    protected array $downloadedImages = [];

    public function __construct(
        string $path,
        string $importId,
        int    $supplierId,
        int    $startRow,
        int    $endRow,
        int    $totalChunks
    ) {
        $this->path        = $path;
        $this->importId    = $importId;
        $this->supplierId  = $supplierId;
        $this->startRow    = $startRow;
        $this->endRow      = $endRow;
        $this->totalChunks = $totalChunks;
    }

    // =========================================================
    public function handle(): void
    {
        // Prevent OOM kills when the queue worker processes many chunks sequentially.
        // Each PhpSpreadsheet load can consume 30-80 MB; without a raised limit the
        // worker process dies silently (fatal OOM ≠ catchable exception) around chunk 14.
        ini_set('memory_limit', '512M');

        // Imports run after the HTTP response (dispatch()->afterResponse()), i.e. inside the
        // web process — downloading / checking a chunk's product images can take longer
        // than PHP's max_execution_time, which would kill the chunk mid-way.
        @set_time_limit(0);

        // Redelivered after it already finished (e.g. a worker died after the last write) —
        // don't process or count it twice.
        if ($this->chunkState() === 'done') {
            \Log::info("ImportJob [{$this->importId}]: lines {$this->startRow}-{$this->endRow} already done — skipping");
            return;
        }

        \Log::info("=== ImportJob START [{$this->importId}] lines {$this->startRow}-{$this->endRow} (attempt {$this->attempts()}) ===");

        try {
            // ── 1. Read assigned rows ─────────────────────────
            $rows = $this->readRows();

            \Log::info("ImportJob [{$this->importId}]: Read " . count($rows) . " rows from file");

            if (empty($rows)) {
                \Log::warning("ImportJob [{$this->importId}]: No rows — skipping chunk");
                $this->recordChunk('done', 0);
                return;
            }

            // ── 2. Process ────────────────────────────────────
            $topCategoryCache    = [];
            $midCategoryCache    = [];
            $bottomCategoryCache = [];
            $fourthCategoryCache = [];
            $attributeCache      = [];

            $actualRowCount = count($rows);

            $this->processChunk(
                $rows,
                $topCategoryCache,
                $midCategoryCache,
                $bottomCategoryCache,
                $fourthCategoryCache,
                $attributeCache
            );

            // Free all large objects before updating progress so the worker's
            // memory footprint drops back down between jobs.
            unset($rows, $topCategoryCache, $midCategoryCache, $bottomCategoryCache, $fourthCategoryCache, $attributeCache);
            gc_collect_cycles();

            $this->recordChunk('done', $actualRowCount);

            \Log::info("=== ImportJob DONE [{$this->importId}] lines {$this->startRow}-{$this->endRow} | rows: {$actualRowCount} ===");

        } catch (\Throwable $e) {
            \Log::error("=== ImportJob CRASHED [{$this->importId}] ===");
            \Log::error("Message : " . $e->getMessage());
            \Log::error("File    : " . $e->getFile() . ":" . $e->getLine());
            \Log::error("Trace   : " . $e->getTraceAsString());

            // On a queue worker the chunk is retried first (failed() records it once every
            // attempt is used up). With nothing to retry it — run inline / after the
            // response, or the last attempt — record it now.
            if ($this->isFinalAttempt()) {
                $this->recordChunk('failed', 0, $e->getMessage());
            }
            throw $e;
        }
    }

    // =========================================================
    // READ ONLY ASSIGNED ROW RANGE FROM FILE
    // =========================================================
    protected function readRows(): array
    {
        if (!Storage::disk('local')->exists($this->path)) {
            throw new \RuntimeException("File not found: {$this->path}");
        }

        $filePath = Storage::disk('local')->path($this->path);
        $extension = strtolower(pathinfo($filePath, PATHINFO_EXTENSION));

        return $extension === 'csv'
            ? $this->readCsvRows($filePath)
            : $this->readSpreadsheetRows($filePath);
    }

    protected function readCsvRows(string $filePath): array
    {
        $handle = fopen($filePath, 'r');

        if (!$handle) {
            throw new \RuntimeException("Cannot open file: {$this->path}");
        }

        $headers = [];
        $rows = [];
        $lineNumber = 0;

        while (($row = fgetcsv($handle, 0, ',')) !== false) {
            $lineNumber++;

            if ($lineNumber === 1) {
                $headers = $this->normalizeHeaders($row);
                continue;
            }

            if ($lineNumber < $this->startRow) continue;
            if ($lineNumber > $this->endRow) break;

            $mapped = $this->mapRowToHeaders($headers, $row);
            if ($this->hasImportData($mapped)) {
                $rows[] = $mapped;
            }
        }

        fclose($handle);
        return $rows;
    }

    protected function readSpreadsheetRows(string $filePath): array
    {
        $startRow = $this->startRow;
        $endRow   = $this->endRow;

        // Only load header row + the rows assigned to this chunk.
        // Without this filter PhpSpreadsheet loads the entire workbook
        // (which can have thousands of ghost rows with large HTML cells)
        // and frequently exhausts PHP memory on later chunks.
        $filter = new class($startRow, $endRow) implements \PhpOffice\PhpSpreadsheet\Reader\IReadFilter {
            private int $s;
            private int $e;
            public function __construct(int $s, int $e) { $this->s = $s; $this->e = $e; }
            public function readCell(string $col, int $row, string $ws = ''): bool {
                return $row === 1 || ($row >= $this->s && $row <= $this->e);
            }
        };

        $reader = IOFactory::createReaderForFile($filePath);
        $reader->setReadDataOnly(true);
        $reader->setReadFilter($filter);

        // Keep a named reference so we can call disconnectWorksheets() after
        // reading — prevents PhpSpreadsheet's internal circular references from
        // keeping this chunk's ~40-80 MB alive inside the worker process.
        $spreadsheet = $reader->load($filePath);
        $sheet       = $spreadsheet->getActiveSheet();

        $highestColumn = $sheet->getHighestDataColumn();
        $headerRow = $sheet->rangeToArray("A1:{$highestColumn}1", null, true, false)[0] ?? [];
        $headers   = $this->normalizeHeaders($headerRow);
        \Log::info("ImportJob [{$this->importId}] headers: " . implode(', ', $headers));
        $rows      = [];

        for ($lineNumber = $startRow; $lineNumber <= $endRow; $lineNumber++) {
            $row    = $sheet->rangeToArray("A{$lineNumber}:{$highestColumn}{$lineNumber}", null, true, false)[0] ?? [];
            $mapped = $this->mapRowToHeaders($headers, $row);
            if ($this->hasImportData($mapped)) {
                $rows[] = $mapped;
            }
        }

        // Explicitly break PhpSpreadsheet's object graph so PHP's reference
        // counter can reclaim the memory before the next job runs.
        $spreadsheet->disconnectWorksheets();
        unset($spreadsheet, $sheet, $reader, $filter);
        gc_collect_cycles();

        return $rows;
    }

    protected function normalizeHeaders(array $headers): array
    {
        $seen = [];

        return array_map(function ($header) use (&$seen) {
            $key = strtolower(trim((string) $header));
            $key = preg_replace('/[^a-z0-9]+/', '_', $key);
            $key = trim($key, '_') ?: 'column';

            $seen[$key] = ($seen[$key] ?? 0) + 1;

            return $seen[$key] > 1 ? "{$key}_{$seen[$key]}" : $key;
        }, $headers);
    }

    protected function mapRowToHeaders(array $headers, array $row): array
    {
        $mapped = [];

        foreach ($headers as $index => $header) {
            $value = $row[$index] ?? '';
            $mapped[$header] = is_string($value) ? trim($value) : $value;
        }

        return $mapped;
    }

    protected function hasImportData(array $row): bool
    {
        return $this->value($row, ['sku']) !== ''
            || $this->value($row, ['item_name', 'title', 'name']) !== '';
    }

    // Grouping placeholder for a row with no colour / no size. Never stored as an
    // attribute value: processChunk() picks the product structure from which attributes
    // the supplier actually gave (colour + size, size only, colour only, or simple).
    const NO_ATTRIBUTE = 'Default';

    // Normalised spelling -> existing Color value, built once per job (see resolveColour()).
    protected ?array $existingColours = null;

    // A supplier attribute value, or '' when there is none ("Default" placeholders sent by
    // some supplier exports / API jobs count as none).
    protected function attributeValue(string $value): string
    {
        $value = trim(preg_replace('/\s+/u', ' ', $value));

        return strcasecmp($value, self::NO_ATTRIBUTE) === 0 ? '' : $value;
    }

    // The row's colour, matched to an existing Color attribute value when one is spelled
    // the same ignoring case / spacing ("black / white" -> "BLACK/WHITE"), so the same
    // colour isn't stored under several spellings. An unmatched colour is kept as the
    // supplier sent it and becomes a new Color value. '' when the row has no colour.
    protected function resolveColour(string $colour): string
    {
        $colour = $this->attributeValue($colour);
        if ($colour === '') return '';

        if ($this->existingColours === null) {
            $this->existingColours = [];

            $colorAttrId = Attributes::where('title', 'Color')->value('id');
            if ($colorAttrId) {
                // Most-used spelling wins when the same colour already exists several ways.
                $rows = ProductVariants::where('attribute_id', $colorAttrId)
                    ->where('value', '!=', self::NO_ATTRIBUTE)
                    ->select('value', DB::raw('COUNT(*) as n'))
                    ->groupBy('value')
                    ->orderByDesc('n')
                    ->get();

                foreach ($rows as $row) {
                    $this->existingColours[$this->colourKey($row->value)] ??= $row->value;
                }
            }
        }

        // A new colour is remembered too, so later rows spelling it differently in the
        // same import reuse the first spelling.
        return $this->existingColours[$this->colourKey($colour)] ??= $colour;
    }

    protected function colourKey(string $colour): string
    {
        $key = mb_strtoupper(trim($colour));
        $key = preg_replace('/\s*([\/,&+-])\s*/u', '$1', $key);

        return preg_replace('/\s+/u', ' ', $key);
    }

    // =========================================================
    // GROUP ROWS → [groupName][color][]
    // =========================================================
    protected function groupRows(array $rows): array
    {
        $grouped = [];

        foreach ($rows as $row) {
            $sku        = $this->value($row, ['sku']);
            // Cleaned before grouping, so rows whose names differ only by broken encoding /
            // escape junk still group into one product (see ProductTextCleaner).
            $itemName   = ProductTextCleaner::title($this->value($row, ['item_name', 'title', 'name']));
            $groupName  = ProductTextCleaner::title($this->value($row, ['group_name'])) ?: $itemName ?: 'unknown';
            // NO_ATTRIBUTE marks "the supplier gave no colour / no size" for grouping only —
            // it's never saved as a Color or Size value (see processChunk()).
            $attribute1 = $this->resolveColour($this->value($row, ['colour', 'color', 'colour_name', 'color_name'])) ?: self::NO_ATTRIBUTE;
            $attribute2 = $this->attributeValue($this->value($row, ['size'])) ?: self::NO_ATTRIBUTE;
            $cleanTitle = $this->cleanProductTitle($itemName, $attribute1, $attribute2) ?: $groupName;

            $galleryImages = [];
            foreach (['image_2', 'image_3', 'image_4', 'image_5', 'image_6'] as $imageKey) {
                $image = $this->value($row, [$imageKey]);
                if ($image !== '') $galleryImages[] = $image;
            }

            $myPrice      = $this->number($this->value($row, ['my_price']));
            $defaultPrice = $this->number($this->value($row, ['default_price']));
            $basePrice    = $myPrice > 0 ? $myPrice : $defaultPrice;
            $regularPrice = $this->calculateSellingPrice($basePrice);

            // API-sourced jobs (Aussie Pacific, Banbury, Logo Line) always set 'inventory'
            // themselves in buildRow(), even to '0' — so array_key_exists is false only for
            // CSV/XLSX uploads whose file genuinely has no quantity/stock column at all
            // (e.g. Augmento, JBswear). Those default to 999 (treated as in-stock) rather
            // than 0, so products aren't shown as sold-out just because the supplier's
            // price list doesn't track stock levels.
            $quantity = array_key_exists('inventory', $row)
                ? (int) $this->number($this->value($row, ['inventory']))
                : $this->defaultQuantity;

            $grouped[$groupName][$attribute1][] = [
                'sku'                   => $sku,
                'title'                 => $groupName,
                'supplier_id'           => $this->supplierId,
                'description'           => ProductTextCleaner::description($this->value($row, ['description_html', 'description'])),
                'attribute1'            => $attribute1,
                'attribute2'            => $attribute2,
                'group_name'            => $groupName,
                'top_level_category'    => $this->value($row, ['main_category']),
                'mid_level_category'    => $this->value($row, ['sub_sub_category']),
                'bottom_level_category' => $this->value($row, ['sub_sub_category_2']),
                'fourth_level_category' => $this->value($row, ['sub_sub_sub_category']),
                'quantity'              => $quantity,
                'regular_price'         => $regularPrice,
                'discount_price'        => 0,
                'product_image'         => $this->value($row, ['image_1']),
                'product_gallery_image' => $galleryImages,
            ];
        }

        return $grouped;
    }

    protected function value(array $row, array $keys): string
    {
        foreach ($keys as $key) {
            if (array_key_exists($key, $row) && $row[$key] !== null && $row[$key] !== '') {
                return trim((string) $row[$key]);
            }
        }

        return '';
    }

    protected function number(string $value): float
    {
        $value = trim(str_replace([',', '$'], '', $value));
        return is_numeric($value) ? (float) $value : 0;
    }

    protected function calculateSellingPrice(float $basePrice): float
    {
        // base cost + 70% markup + 15% GST
        return round($basePrice * 1.70, 2);
    }

    protected function cleanProductTitle(string $title, string $attribute1, string $attribute2): string
    {
        $cleanTitle = trim(str_ireplace([$attribute1, $attribute2], '', $title));
        return trim(preg_replace('/\s+/', ' ', $cleanTitle));
    }


    // Change return type from int to void
    protected function processChunk(
        array $rows,
        array &$topCategoryCache,
        array &$midCategoryCache,
        array &$bottomCategoryCache,
        array &$fourthCategoryCache,
        array &$attributeCache
    ): void {   // ← was int
        $grouped = $this->groupRows($rows);

        // API imports: check every product image URL in this chunk up front, concurrently,
        // instead of one blocking request per product inside the loop below.
        $fileImportUrls = [];
        if ($this->downloadProductImages) {
            // File imports: download the chunk's product images concurrently up front.
            foreach ($grouped as $colorGroups) {
                foreach ($colorGroups as $productItems) {
                    $url = trim((string) ($productItems[0]['product_image'] ?? ''));
                    if (preg_match('#^https?://#i', $url)) $fileImportUrls[] = $url;
                }
            }
            $this->imageDownloader()->prefetch($fileImportUrls);
        }

        if (!$this->downloadProductImages && $this->verifyImageUrls) {
            $urls = [];
            foreach ($grouped as $colorGroups) {
                foreach ($colorGroups as $productItems) {
                    $url = trim((string) ($productItems[0]['product_image'] ?? ''));
                    if (preg_match('#^https?://#i', $url)) $urls[] = $url;
                }
            }
            $this->prefetchImageUrlChecks($urls);
        }

        foreach ($grouped as $baseTitle => $colorGroups) {
            // Product structure follows the attributes the supplier actually gave — a
            // missing colour or size is never stored as a "Default" value:
            //   colour + size -> Color variants, each with Size sub-variants
            //   size only     -> single attribute: Size variants (no Color at all)
            //   colour only   -> single attribute: Color variants (no Size at all)
            //   neither       -> simple product
            $hasColour = count(array_diff(array_keys($colorGroups), [self::NO_ATTRIBUTE])) > 0;
            $hasSize   = false;
            foreach ($colorGroups as $items) {
                foreach ($items as $i) {
                    if ($i['attribute2'] !== self::NO_ATTRIBUTE) { $hasSize = true; break 2; }
                }
            }

            $isSimple = !$hasColour && !$hasSize;
            $mode     = $isSimple ? 'simple'
                : ($hasColour && $hasSize ? 'colour_size' : ($hasSize ? 'size' : 'colour'));

            if ($mode === 'colour_size' && isset($colorGroups[self::NO_ATTRIBUTE])) {
                // Some rows of a coloured product came without a colour — they can't be
                // placed under a real colour, so they're left out rather than shown as a
                // "Default" colour option.
                \Log::warning("ImportJob: [{$baseTitle}] skipped " . count($colorGroups[self::NO_ATTRIBUTE]) . " row(s) with no colour (other rows of this product have one)");
                unset($colorGroups[self::NO_ATTRIBUTE]);
            }

            foreach ($colorGroups as $colorKey => $productItems) {
                try {
                    DB::transaction(function () use (
                        $isSimple, $mode, $colorKey, $productItems,
                        &$topCategoryCache, &$midCategoryCache,
                        &$bottomCategoryCache, &$fourthCategoryCache,
                        &$attributeCache
                    ) {
                        $first = $productItems[0];

                        $topId = $this->getOrCreateTopCategory(
                            $first['top_level_category'], $topCategoryCache
                        );
                        $midId = $this->getOrCreateMidCategory(
                            $first['mid_level_category'],
                            $first['top_level_category'],
                            $topId,
                            $midCategoryCache
                        );
                        $bottomId = $this->getOrCreateBottomCategory(
                            $first['bottom_level_category'],
                            $topId,
                            $midId,
                            $bottomCategoryCache
                        );
                        $fourthId = $this->getOrCreateFourthCategory(
                            $first['fourth_level_category'],
                            $topId,
                            $midId,
                            $bottomId,
                            $fourthCategoryCache
                        );

                        if ($isSimple) {
                            // Simple product — no variants, just save product + gallery
                            $product = $this->upsertProduct($first, $topId, $midId, $bottomId, $fourthId, 'simple');
                            $this->insertGalleryImages($product->id, $productItems);
                        } else {
                            $product   = $this->upsertProduct($first, $topId, $midId, $bottomId, $fourthId, 'variable');
                            $productId = $product->id;

                            $this->insertGalleryImages($productId, $productItems);

                            if ($mode === 'size') {
                                // Size only — one Size variant per row, priced directly.
                                $this->insertSingleAttributeVariants($productId, 'Size', $productItems, $attributeCache);
                            } elseif ($mode === 'colour') {
                                // Colour only — this colour group is one Color variant.
                                $colorVariantId = $this->upsertColorVariant($productId, $colorKey, $attributeCache);
                                $this->insertSingleAttributeItem($productId, $colorVariantId, $productItems[0]);
                                $this->insertVariantImages($productId, $colorVariantId, $productItems);
                            } else {
                                // Colour + size — full variant/sub-variant flow
                                $colorVariantId = $this->upsertColorVariant(
                                    $productId, $colorKey, $attributeCache
                                );

                                $this->insertSubVariantsAndItems(
                                    $productId, $colorVariantId, $productItems
                                );

                                $this->insertVariantImages($productId, $colorVariantId, $productItems);
                            }

                            $this->removeStaleDefaultVariants($productId, $mode, $attributeCache);
                        }

                    });

                } catch (\Throwable $e) {
                    \Log::error("ImportJob: Skipped product [{$colorKey}]: " . $e->getMessage());
                }
            }
        }

        $this->assignFallbackCategoryImages();
        // ← removed return $processedCount
    }


    protected function insertVariantImages(
    int $productId, int $colorVariantId, array $productItems
    ): void {
        $uniqueImages = [];

        foreach ($productItems as $item) {

            foreach ($item['product_gallery_image'] ?? [] as $img) {
                if (!empty($img)) $uniqueImages[$img] = true;
            }
             
        }

        if (empty($uniqueImages)) return;

        // Check existing to avoid duplicates
        $existing = array_flip(
            DB::table('product_variant_images')
                ->where('product_id', $productId)
                ->where('product_variant_id', $colorVariantId)
                ->pluck('imageUrl')
                ->toArray()
        );

        $batch = [];
        foreach (array_keys($uniqueImages) as $img) {
            if (!isset($existing[$img])) {

                $batch[] = [
                    'product_id'         => $productId,
                    'product_variant_id' => $colorVariantId,
                    'imageUrl'           => $img,
                    'created_at'         => now(),
                    'updated_at'         => now(),
                ];
            }
        }

        if (!empty($batch)) {
            DB::table('product_variant_images')->insertOrIgnore($batch);
        }

    }

    // =========================================================
    // PRODUCT UPSERT
    // =========================================================
    protected function upsertProduct(
        array $item, ?int $topId, ?int $midId, ?int $bottomId = null, ?int $fourthId = null,
        string $productType = 'variable'
    ): Products {
        $product = Products::firstOrNew([
            'title'       => $item['title'],
            'supplier_id' => $item['supplier_id'],
        ]);

        if (!$product->exists) {
            $product->slug = createSlug('products', $item['title']);
        }

        $product->supplier_id               = $item['supplier_id'];
        $product->product_type              = $productType;
        // Variable products keep their per-size SKU in product_sub_variant_items instead
        // (see insertSubVariantsAndItems) — only simple products need it stored here.
        if ($productType === 'simple' && !empty($item['sku'])) {
            $product->sku = $item['sku'];
        }
        $product->description               = $item['description'];
        $product->quantity                  = $item['quantity'];
        $product->regular_price             = $item['regular_price'];
        $product->discount_price            = $item['discount_price'];
        $this->assignProductImage($product, (string) ($item['product_image'] ?? ''));
        $product->top_level_category_id     = $topId;
        $product->mid_level_category_id     = $midId;
        $product->bottom_level_category_id  = $bottomId;
        // 'category' stores 4th-level ID when present, otherwise 3rd-level (matches store controller)
        $product->category                  = $fourthId ?? $bottomId;
        $product->save();

        $this->assignCategoryImages($product, [$midId, $bottomId, $fourthId]);

        return $product;
    }

    // =========================================================
    // PRODUCT IMAGE
    // =========================================================
    // Sets products.image / products.image_url from the row's image value. Only an image
    // URL that is actually reachable is ever stored:
    //  - file imports download it into public/uploads/Product (the download is the check)
    //    and products.image gets the local filename;
    //  - API imports check it with isImageUrlAccessible() and store the URL itself.
    // A URL that 404s, errors, times out or isn't an image leaves both columns NULL —
    // nothing is downloaded and the import carries on with the next product.
    protected function assignProductImage(Products $product, string $image): void
    {
        $image = trim($image);

        if (!preg_match('#^https?://#i', $image)) {
            // Empty, or already a local filename — store as-is (previous behaviour).
            $product->image = $image;
            return;
        }

        $value = $this->downloadProductImages
            ? $this->downloadProductImage($image)
            : (!$this->verifyImageUrls || $this->isImageUrlAccessible($image) ? $image : null);

        // A download that failed for a temporary reason (supplier site down / timing out)
        // keeps the URL so products:fetch-images can fill the image in later, and never
        // wipes an image the product already has. A permanent failure (404, not an image)
        // leaves both columns empty, as before.
        if ($value === null && $this->downloadProductImages && $this->imageDownloader()->failedTransiently($image)) {
            if (empty($product->image)) {
                $product->image     = null;
                $product->image_url = $image;
            }
            return;
        }

        $product->image     = $value;
        $product->image_url = $value !== null ? $image : null;
    }

    // URL -> bool for this job run (see isImageUrlAccessible()).
    protected array $checkedImageUrls = [];

    // Cross-run cache of URLs that passed the check, so a recurring API sync doesn't
    // re-check thousands of unchanged image URLs every run. Failures aren't cached
    // there — a supplier fixing a broken link is picked up on the next run.
    const IMAGE_CHECK_CACHE_TTL_DAYS = 7;

    protected function isImageUrlAccessible(string $url): bool
    {
        if (!array_key_exists($url, $this->checkedImageUrls)) {
            $this->prefetchImageUrlChecks([$url]);
        }

        return $this->checkedImageUrls[$url];
    }

    // Checks many URLs concurrently (batches of 20) and records the results in
    // $checkedImageUrls. A HEAD request is tried first; servers that reject HEAD
    // (403/405/501, or no image content-type) get a tiny ranged GET instead.
    protected function prefetchImageUrlChecks(array $urls): void
    {
        $pending = [];
        foreach (array_unique($urls) as $url) {
            if (array_key_exists($url, $this->checkedImageUrls)) continue;

            if (Cache::has($this->imageCheckCacheKey($url))) {
                $this->checkedImageUrls[$url] = true;
                continue;
            }

            $pending[] = $url;
        }

        foreach (array_chunk($pending, 20) as $batch) {
            $retry = [];

            foreach ($this->poolImageRequests($batch, 'head') as $url => $response) {
                if ($response instanceof \Illuminate\Http\Client\Response && $response->status() === 404) {
                    $this->recordImageCheck($url, false, 'HTTP 404');
                } elseif ($this->isImageResponse($response)) {
                    $this->recordImageCheck($url, true);
                } else {
                    $retry[] = $url;
                }
            }

            foreach ($this->poolImageRequests($retry, 'get') as $url => $response) {
                $ok = $this->isImageResponse($response);
                $this->recordImageCheck($url, $ok, $ok ? '' : $this->describeImageFailure($response));
            }
        }
    }

    // [url => Response|Throwable]
    protected function poolImageRequests(array $urls, string $method): array
    {
        if (empty($urls)) return [];

        $responses = Http::pool(function ($pool) use ($urls, $method) {
            foreach ($urls as $url) {
                $request = $pool->as($url)
                    ->timeout(15)
                    ->connectTimeout(8)
                    ->withUserAgent('Mozilla/5.0 (compatible; ProductImporter/1.0)');

                $method === 'head'
                    ? $request->head($url)
                    : $request->withHeaders(['Range' => 'bytes=0-1023'])->get($url);
            }
        });

        return array_intersect_key($responses, array_flip($urls));
    }

    protected function isImageResponse($response): bool
    {
        if (!$response instanceof \Illuminate\Http\Client\Response || !$response->successful()) {
            return false;
        }

        return str_starts_with(strtolower((string) $response->header('Content-Type')), 'image/');
    }

    protected function describeImageFailure($response): string
    {
        if ($response instanceof \Throwable) return $response->getMessage();
        if (!$response->successful())        return "HTTP {$response->status()}";

        return 'not an image (' . ($response->header('Content-Type') ?: 'no content-type') . ')';
    }

    protected function recordImageCheck(string $url, bool $ok, string $reason = ''): void
    {
        $this->checkedImageUrls[$url] = $ok;

        if ($ok) {
            Cache::put($this->imageCheckCacheKey($url), true, now()->addDays(self::IMAGE_CHECK_CACHE_TTL_DAYS));
        } else {
            \Log::warning("ImportJob: product image not accessible ({$reason}), image left blank — {$url}");
        }
    }

    protected function imageCheckCacheKey(string $url): string
    {
        return 'import_image_ok_' . md5($url);
    }

    // Returns the local filename inside IMAGE_STORAGE_PATH, or null on any failure.
    // See ProductImageDownloader: concurrent, de-duplicated by URL, skips dead hosts.
    protected function downloadProductImage(string $url): ?string
    {
        return $this->imageDownloader()->download($url);
    }

    protected ?ProductImageDownloader $imageDownloaderInstance = null;

    protected function imageDownloader(): ProductImageDownloader
    {
        return $this->imageDownloaderInstance ??= new ProductImageDownloader();
    }

    // =========================================================
    // GALLERY
    // =========================================================
    protected function insertGalleryImages(int $productId, array $productItems): void
    {
        $uniqueImages = [];
        foreach ($productItems as $item) {
            foreach ($item['product_gallery_image'] ?? [] as $img) {
                if (!empty($img)) $uniqueImages[$img] = true;
            }
        }

        \Log::info("insertGalleryImages: productId={$productId}, found " . count($uniqueImages) . " unique images");
        if (empty($uniqueImages)) return;

        $existing = array_flip(
            DB::table('product_galleries')
                ->where('product_id', $productId)
                ->pluck('image')
                ->toArray()
        );

        $batch = [];
        foreach (array_keys($uniqueImages) as $img) {
            if (!isset($existing[$img])) {
                $batch[] = [
                    'product_id' => $productId,
                    'image'      => $img,
                    'created_at' => now(),
                    'updated_at' => now(),
                ];
            }
        }

        if (!empty($batch)) {
            DB::table('product_galleries')->insertOrIgnore($batch);
        }
    }

    // =========================================================
    // COLOR VARIANT
    // =========================================================
    protected function upsertColorVariant(
    int $productId, $colorValue, array &$attributeCache
    ): int {
        // Debug what's coming in
        //\Log::info("upsertColorVariant: productId={$productId}, colorValue=" . $colorValue);

        // Get or create Color attribute (cached per job to avoid repeated DB hits)
        if (!isset($attributeCache['Color'])) {
            $attributeCache['Color'] = Attributes::firstOrCreate(['title' => 'Color'])->id;
        }

        $attributeId = $attributeCache['Color'];

        // Skip if both are empty
        if (empty($attributeId) || $colorValue === null || $colorValue === '') {
            //\Log::warning("upsertColorVariant: skipping — attributeId={$attributeId}, colorValue=" . var_export($colorValue, true));
            // Still need to return something — use a default
            $colorValue = 'Default';
        }

        // Update product's attribute_id
        Products::where('id', $productId)->update([
            'attribute_id' => $attributeId,
        ]);


        $ProductVariant = ProductVariants::firstOrCreate([
            'product_id'    => $productId,
            'attribute_id'  => $attributeId,
            'value'         => $colorValue,
        ]);

        return $ProductVariant->id;
 
    }

    // =========================================================
    // SUB VARIANTS + STOCK ITEMS
    // =========================================================
    protected function insertSubVariantsAndItems(
        int $productId, int $colorVariantId, array $productItems
    ): void {
        // Resolve Size attribute once for the whole batch
        $sizeAttr = Attributes::firstOrCreate(['title' => 'Size']);
        $sizeAttrId = $sizeAttr->id;

        // Keep sub_variant_attribute_id up-to-date on the product
        Products::where('id', $productId)->update([
            'sub_variant_attribute_id' => $sizeAttrId,
        ]);

        foreach ($productItems as $item) {
            // product_sub_variants.product_variant_id stores the SIZE ATTRIBUTE ID
            // (not a product_variants row) — matches what the store controller saves via
            // $Product->sub_variant_attribute_id
            $subVariant = ProductSubVariants::firstOrCreate(
                [
                    'value'      => $item['attribute2'],
                    'product_id' => $productId,
                ],
                [
                    'product_variant_id' => $sizeAttrId,
                ]
            );

            ProducSubtVariantItems::updateOrCreate(
                [
                    'product_id'             => $productId,
                    'product_variant_id'     => $colorVariantId,
                    'product_sub_variant_id' => $subVariant->id,
                ],
                [
                    'sku_number' => $item['sku']          ?? '',
                    'stock'      => $item['quantity']     ?? 0,
                    'price'      => $item['regular_price'] ?? 0,
                ]
            );
        }
    }

    // =========================================================
    // SINGLE-ATTRIBUTE VARIANTS (size only / colour only)
    // =========================================================
    // Same layout the admin product form saves when only one attribute is chosen:
    // products.attribute_id = that attribute, products.sub_variant_attribute_id = NULL,
    // one product_variants row per value, and each price/stock row points straight at it
    // with product_sub_variant_id = NULL. The product page then shows just that one
    // attribute, and cart / price lookups work without a second selection.
    protected function insertSingleAttributeVariants(
        int $productId, string $attributeTitle, array $productItems, array &$attributeCache
    ): void {
        $attributeCache[$attributeTitle] ??= Attributes::firstOrCreate(['title' => $attributeTitle])->id;
        $attributeId = $attributeCache[$attributeTitle];

        Products::where('id', $productId)->update(['attribute_id' => $attributeId]);

        foreach ($productItems as $item) {
            $variant = ProductVariants::firstOrCreate([
                'product_id'   => $productId,
                'attribute_id' => $attributeId,
                'value'        => $item['attribute2'],
            ]);

            $this->insertSingleAttributeItem($productId, $variant->id, $item);
        }
    }

    protected function insertSingleAttributeItem(int $productId, int $variantId, array $item): void
    {
        Products::where('id', $productId)->update(['sub_variant_attribute_id' => null]);

        ProducSubtVariantItems::updateOrCreate(
            [
                'product_id'             => $productId,
                'product_variant_id'     => $variantId,
                'product_sub_variant_id' => null,
            ],
            [
                'sku_number' => $item['sku']           ?? '',
                'stock'      => $item['quantity']      ?? 0,
                'price'      => $item['regular_price'] ?? 0,
            ]
        );
    }

    // Removes variant data left by an earlier import that stored a missing colour/size as
    // a "Default" value (or used a different structure), so a re-import doesn't keep
    // showing "Color: Default" next to the corrected options.
    protected function removeStaleDefaultVariants(int $productId, string $mode, array &$attributeCache): void
    {
        $attributeCache['Color'] ??= Attributes::firstOrCreate(['title' => 'Color'])->id;
        $attributeCache['Size']  ??= Attributes::firstOrCreate(['title' => 'Size'])->id;
        $colorId = $attributeCache['Color'];
        $sizeId  = $attributeCache['Size'];

        $stale = ProductVariants::where('product_id', $productId)
            ->where(function ($q) use ($mode, $colorId, $sizeId) {
                if ($mode === 'size') {
                    $q->where('attribute_id', '!=', $sizeId);
                } elseif ($mode === 'colour') {
                    $q->where('attribute_id', '!=', $colorId)->orWhere('value', self::NO_ATTRIBUTE);
                } else {
                    $q->where('attribute_id', $colorId)->where('value', self::NO_ATTRIBUTE);
                }
            })
            ->pluck('id');

        if ($stale->isNotEmpty()) {
            ProducSubtVariantItems::where('product_id', $productId)->whereIn('product_variant_id', $stale)->delete();
            ProductVariantImages::where('product_id', $productId)->whereIn('product_variant_id', $stale)->delete();
            DB::table('cart')->where('product_id', $productId)->whereIn('product_variant_id', $stale)->delete();
            ProductVariants::whereIn('id', $stale)->delete();
        }

        if ($mode !== 'colour_size') {
            // Single-attribute product: no sizes-as-sub-variants any more.
            $subIds = ProductSubVariants::where('product_id', $productId)->pluck('id');
            if ($subIds->isNotEmpty()) {
                ProducSubtVariantItems::where('product_id', $productId)->whereNotNull('product_sub_variant_id')->delete();
                DB::table('cart')->where('product_id', $productId)->whereIn('product_sub_variant_id', $subIds)->delete();
                ProductSubVariants::whereIn('id', $subIds)->delete();
            }
        }
    }

    // =========================================================
    // CATEGORIES
    // =========================================================
    // Subcategory image from the imported product's image (CSV + API imports).
    // A category whose image file exists is never touched (an image column pointing at a
    // file that is no longer on disk counts as no image). Otherwise the product image is
    // used when the product sits directly in that category (deepest level) or its
    // title/description mentions the category name, so the image is relevant. A parent
    // category no product mentions by name falls back to the first product image seen
    // under it — see assignFallbackCategoryImages().
    protected array $categoryImageSettled  = [];
    protected array $categoryImageFallback = [];

    protected function assignCategoryImages(Products $product, array $categoryIds): void
    {
        try {
            $image = $product->image;
            $ids   = array_values(array_filter($categoryIds));
            if (empty($image) || empty($ids)) return;

            $deepestId = end($ids);
            $text      = strtolower(strip_tags($product->title . ' ' . $product->description));

            foreach ($ids as $id) {
                if (isset($this->categoryImageSettled[$id])) continue;

                $category = Categories::find($id);
                if (!$category || $this->categoryImageExists($category->image)) {
                    $this->categoryImageSettled[$id] = true;   // already has an image — skip
                    continue;
                }

                if ($id !== $deepestId && !$this->textMatchesTitle($text, $category->title)) {
                    $this->categoryImageFallback[$id] ??= $image;
                    continue;
                }

                $file = $this->saveCategoryImage($image);
                if (!$file) continue;

                $category->image = $file;
                $category->save();
                $this->categoryImageSettled[$id] = true;
            }
        } catch (\Throwable $e) {
            \Log::warning("ImportJob: category image skipped for product [{$product->id}]: " . $e->getMessage());
        }
    }

    // Runs once the chunk's products are saved: parent categories that no product
    // mentioned by name (e.g. "Accessories" above "Insole" / "Socks") still get an image,
    // from the first product imported under them.
    protected function assignFallbackCategoryImages(): void
    {
        foreach ($this->categoryImageFallback as $id => $image) {
            if (isset($this->categoryImageSettled[$id])) continue;

            try {
                $category = Categories::find($id);
                if (!$category || $this->categoryImageExists($category->image)) continue;

                $file = $this->saveCategoryImage($image);
                if (!$file) continue;

                $category->image = $file;
                $category->save();
                $this->categoryImageSettled[$id] = true;
            } catch (\Throwable $e) {
                \Log::warning("ImportJob: fallback category image skipped for category [{$id}]: " . $e->getMessage());
            }
        }

        $this->categoryImageFallback = [];
    }

    protected function categoryImageExists(?string $image): bool
    {
        return !empty($image) && is_file(public_path('uploads/Category/' . $image));
    }

    protected function textMatchesTitle(string $text, string $title): bool
    {
        $words = array_filter(
            preg_split('/\W+/', strtolower($title)),
            fn($w) => strlen($w) > 2 && !in_array($w, ['and', 'the', 'for', 'with', 'only'], true)
        );
        foreach ($words as $w) {
            if (str_contains($text, rtrim($w, 's'))) return true;
        }
        return false;
    }

    // Download (URL) or copy (already-uploaded product file) into public/uploads/Category
    protected function saveCategoryImage(string $image): ?string
    {
        $dir = public_path('uploads/Category');
        if (!is_dir($dir)) mkdir($dir, 0777, true);

        if (preg_match('#^https?://#i', $image)) {
            // A supplier host that keeps failing is skipped (for a few minutes, across
            // chunks) instead of costing a timeout for every product of the import.
            if ($this->imageDownloader()->hostIsSkipped($image)) return null;

            $data = @file_get_contents($image, false, stream_context_create(
                ['http' => ['timeout' => 20, 'user_agent' => 'Mozilla/5.0']]
            ));
            $this->imageDownloader()->reportHostResult($image, $data !== false);
            if (!$data || strlen($data) < 500) return null;
            $ext = strtolower(pathinfo(parse_url($image, PHP_URL_PATH), PATHINFO_EXTENSION)) ?: 'jpg';
        } else {
            $source = public_path(self::IMAGE_STORAGE_PATH . '/' . $image);
            if (!is_file($source)) return null;
            $data = file_get_contents($source);
            $ext  = strtolower(pathinfo($source, PATHINFO_EXTENSION)) ?: 'jpg';
        }

        $name = time() . '_' . uniqid() . '.' . $ext;
        file_put_contents("{$dir}/{$name}", $data);
        return $name;
    }

    // Stamp the importing supplier on the category (saved only when it differs)
    protected function assignSupplier(Categories $category): Categories
    {
        if ((int) $category->supplier_id !== (int) $this->supplierId) {
            $category->supplier_id = $this->supplierId;
            $category->save();
        }
        return $category;
    }

    protected function getOrCreateTopCategory(string $name, array &$cache): ?int
    {
        if (empty($name)) return null;
        $key = strtolower(trim($name));

        if (!isset($cache[$key])) {
            $cache[$key] = Categories::firstOrCreate(
                ['title' => $name, 'top_level_category_id' => null, 'mid_level_category_id' => null],
                ['slug'  => createSlug('categories', $name)]
            );
        }
        return $this->assignSupplier($cache[$key])->id;
    }

    protected function getOrCreateMidCategory(
        string $midName, string $topName, ?int $topId, array &$cache
    ): ?int {
        if (empty($midName) || !$topId) return null;
        $key = strtolower(trim($topName)) . '_' . strtolower(trim($midName));

        if (!isset($cache[$key])) {
            $cache[$key] = Categories::firstOrCreate(
                ['title' => $midName, 'top_level_category_id' => $topId, 'mid_level_category_id' => null],
                ['slug'  => createSlug('categories', $this->buildMidCategorySlugText($midName, $topId))]
            );
        }
        return $this->assignSupplier($cache[$key])->id;
    }

    protected function getOrCreateBottomCategory(
        string $name, ?int $topId, ?int $midId, array &$cache
    ): ?int {
        if (empty($name) || !$midId) return null;
        $key = strtolower(trim($name)) . '_mid_' . $midId;

        if (!isset($cache[$key])) {
            $cache[$key] = Categories::firstOrCreate(
                [
                    'title'                    => $name,
                    'top_level_category_id'    => $topId,
                    'mid_level_category_id'    => $midId,
                    'bottom_level_category_id' => null,
                ],
                ['slug' => createSlug('categories', $this->buildBottomCategorySlugText($name, $topId, $midId))]
            );
        }
        return $this->assignSupplier($cache[$key])->id;
    }

    protected function getOrCreateFourthCategory(
        string $name, ?int $topId, ?int $midId, ?int $bottomId, array &$cache
    ): ?int {
        if (empty($name) || !$bottomId) return null;
        $key = strtolower(trim($name)) . '_bottom_' . $bottomId;

        if (!isset($cache[$key])) {
            $cache[$key] = Categories::firstOrCreate(
                [
                    'title'                    => $name,
                    'top_level_category_id'    => $topId,
                    'mid_level_category_id'    => $midId,
                    'bottom_level_category_id' => $bottomId,
                ],
                ['slug' => createSlug('categories', $this->buildFourthCategorySlugText($name, $topId, $midId, $bottomId))]
            );
        }
        return $this->assignSupplier($cache[$key])->id;
    }

    protected function buildMidCategorySlugText(string $midName, int $topId): string
    {
        $slugParts = [$midName];
        $topCategory = Categories::where('id', $topId)->first();
        if ($topCategory) {
            $slugParts[] = $topCategory->slug ?: $topCategory->title;
        }
        return implode('-', array_filter($slugParts));
    }

    protected function buildBottomCategorySlugText(string $name, ?int $topId, ?int $midId): string
    {
        $slugParts = [$name];
        if ($midId) {
            $midCategory = Categories::where('id', $midId)->first();
            if ($midCategory) {
                $slugParts[] = $this->buildMidCategorySlugText(
                    $midCategory->title,
                    $topId ?: $midCategory->top_level_category_id
                );
            }
        }
        return implode('-', array_filter($slugParts));
    }

    protected function buildFourthCategorySlugText(string $name, ?int $topId, ?int $midId, ?int $bottomId): string
    {
        $slugParts = [$name];
        if ($bottomId) {
            $bottomCategory = Categories::where('id', $bottomId)->first();
            if ($bottomCategory) {
                $slugParts[] = $this->buildBottomCategorySlugText(
                    $bottomCategory->title,
                    $topId ?: $bottomCategory->top_level_category_id,
                    $midId ?: $bottomCategory->mid_level_category_id
                );
            }
        }
        return implode('-', array_filter($slugParts));
    }
 
    // ── Import ledger ──────────────────────────────────────
    // import_meta_{id}['chunks'] maps each chunk (by its first line) to 'done' | 'failed'.
    // Progress is derived from it, so a retried or redelivered chunk is never counted
    // twice, and a failed chunk that later succeeds (queue:retry) flips to done while the
    // chunks that already succeeded are left alone.
    const META_TTL_HOURS = 24;

    protected function chunkState(): ?string
    {
        $meta = cache()->get("import_meta_{$this->importId}");

        return $meta['chunks'][$this->startRow]['state'] ?? null;
    }

    protected function recordChunk(string $state, int $rowCount = 0, string $error = ''): void
    {
        $metaKey = "import_meta_{$this->importId}";
        // Lock TTL 15 s — long enough to outlast a slow cache write but short
        // enough to auto-expire if the holder crashes before releasing it.
        $lock = cache()->lock("import_lock_{$this->importId}", 15);

        try {
            // 30 s wait — covers the full 15 s TTL of a stale lock left by a
            // crashed worker, plus a margin for cache latency on Windows.
            $lock->block(30);

            $meta = cache()->get($metaKey);

            if (empty($meta)) {
                \Log::warning("ImportJob: Cache missing [{$this->importId}]");
                return;
            }

            $meta['chunks'][$this->startRow] = [
                'state' => $state,
                'rows'  => $rowCount,
                'error' => mb_substr($error, 0, 300),
            ];

            $chunks    = $meta['chunks'];
            $done      = count(array_filter($chunks, fn($c) => $c['state'] === 'done'));
            $failed    = count(array_filter($chunks, fn($c) => $c['state'] === 'failed'));
            $total     = max(1, (int) ($meta['total_chunks'] ?? 1));

            $meta['completed_chunks'] = $done;
            $meta['failed_chunks']    = $failed;
            $meta['processed_rows']   = array_sum(array_map(fn($c) => $c['state'] === 'done' ? (int) $c['rows'] : 0, $chunks));
            $meta['percentage']       = min(100, (int) round(($done / $total) * 100));

            if ($done >= $total) {
                $meta['status'] = 'completed';
            } elseif ($done + $failed >= $total) {
                // Every chunk has finished but some failed — retry them with
                // `php artisan queue:retry all`; only those chunks are re-run.
                $meta['status'] = 'failed';
            } else {
                $meta['status'] = 'processing';
            }

            cache()->put($metaKey, $meta, now()->addHours(self::META_TTL_HOURS));

            \Log::info("Progress [{$this->importId}]: {$done} done, {$failed} failed of {$total} chunks = {$meta['percentage']}%");

        } catch (\Illuminate\Contracts\Cache\LockTimeoutException $e) {
            \Log::warning("ImportJob: Could not acquire progress lock [{$this->importId}]: " . $e->getMessage());
        } finally {
            optional($lock)->release();
        }
    }

    protected function isFinalAttempt(): bool
    {
        return $this->job === null
            || $this->job instanceof \Illuminate\Queue\Jobs\SyncJob
            || $this->attempts() >= $this->tries;
    }

    // Called by the queue once every attempt of this chunk has failed.
    public function failed(\Throwable $e): void
    {
        \Log::error("ImportJob FAILED [{$this->importId}] lines {$this->startRow}-{$this->endRow}: " . $e->getMessage());
        $this->recordChunk('failed', 0, $e->getMessage());
    }
}
