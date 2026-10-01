<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;
use App\Models\Products;
use App\Models\Attributes;
use App\Models\ProductVariants;
use App\Models\ProductVariantImages;
use App\Models\ProductSubVariants;
use App\Models\ProducSubtVariantItems;
use App\Models\Categories;
use PhpOffice\PhpSpreadsheet\IOFactory;

class ProductImportJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $timeout = 600;
    public $tries   = 1;      // no retries during debug
    public $backoff = 10;

    protected string $path;
    protected string $importId;
    protected int    $supplierId;
    protected int    $startRow;
    protected int    $endRow;
    protected int    $totalChunks;

    protected int $defaultQuantity = 999;

    // ── Image storage path (relative to public/) ──
    const IMAGE_STORAGE_PATH = 'uploads/Product';

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

        \Log::info("=== ImportJob START [{$this->importId}] lines {$this->startRow}-{$this->endRow} ===");

        try {
            // ── 1. Read assigned rows ─────────────────────────
            $rows = $this->readRows();

            \Log::info("ImportJob [{$this->importId}]: Read " . count($rows) . " rows from file");

            if (empty($rows)) {
                \Log::warning("ImportJob [{$this->importId}]: No rows — skipping chunk");
                $this->updateProgress(0);
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

            $this->updateProgress($actualRowCount);

            \Log::info("=== ImportJob DONE [{$this->importId}] lines {$this->startRow}-{$this->endRow} | rows: {$actualRowCount} ===");

        } catch (\Throwable $e) {
            \Log::error("=== ImportJob CRASHED [{$this->importId}] ===");
            \Log::error("Message : " . $e->getMessage());
            \Log::error("File    : " . $e->getFile() . ":" . $e->getLine());
            \Log::error("Trace   : " . $e->getTraceAsString());

            $this->markFailed();
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

    // =========================================================
    // GROUP ROWS → [groupName][color][]
    // =========================================================
    protected function groupRows(array $rows): array
    {
        $grouped = [];

        foreach ($rows as $row) {
            $sku        = $this->value($row, ['sku']);
            $itemName   = $this->value($row, ['item_name', 'title', 'name']);
            $groupName  = $this->value($row, ['group_name']) ?: $itemName ?: 'unknown';
            $attribute1 = $this->value($row, ['colour', 'color']) ?: 'Default';
            $attribute2 = $this->value($row, ['size']) ?: 'Default';
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
                : 999;

            $grouped[$groupName][$attribute1][] = [
                'sku'                   => $sku,
                'title'                 => $groupName,
                'supplier_id'           => $this->supplierId,
                'description'           => $this->value($row, ['description_html', 'description']),
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

        foreach ($grouped as $baseTitle => $colorGroups) {
            // Simple product: only one 'Default' color group and all sizes are also 'Default'
            $colorKeys = array_keys($colorGroups);
            $isSimple  = count($colorKeys) === 1
                && $colorKeys[0] === 'Default'
                && count(array_filter($colorGroups['Default'], fn($i) => $i['attribute2'] !== 'Default')) === 0;

            foreach ($colorGroups as $colorKey => $productItems) {
                try {
                    DB::transaction(function () use (
                        $isSimple, $colorKey, $productItems,
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
                            // Variable product — full variant/sub-variant flow
                            $product        = $this->upsertProduct($first, $topId, $midId, $bottomId, $fourthId, 'variable');
                            $productId      = $product->id;

                            $this->insertGalleryImages($productId, $productItems);

                            $colorVariantId = $this->upsertColorVariant(
                                $productId, $colorKey, $attributeCache
                            );

                            $this->insertSubVariantsAndItems(
                                $productId, $colorVariantId, $productItems
                            );

                            $this->insertVariantImages($productId, $colorVariantId, $productItems);
                        }

                    });

                } catch (\Throwable $e) {
                    \Log::error("ImportJob: Skipped product [{$colorKey}]: " . $e->getMessage());
                }
            }
        }
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
        $product->image                     = $item['product_image'];
        $product->top_level_category_id     = $topId;
        $product->mid_level_category_id     = $midId;
        $product->bottom_level_category_id  = $bottomId;
        // 'category' stores 4th-level ID when present, otherwise 3rd-level (matches store controller)
        $product->category                  = $fourthId ?? $bottomId;
        $product->save();

        return $product;
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
    // CATEGORIES
    // =========================================================
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
 
    protected function updateProgress(int $rowCount): void
    {
        $metaKey = "import_meta_{$this->importId}";
        // Lock TTL 15 s — long enough to outlast a slow cache write but short
        // enough to auto-expire if the holder crashes before releasing it.
        $lock = cache()->lock("import_lock_{$this->importId}", 15);

        try {
            // 30 s wait — covers the full 15 s TTL of a stale lock left by a
            // crashed worker, plus a margin for file-cache latency on Windows.
            $lock->block(30);

            $meta = cache()->get($metaKey);

            if (empty($meta)) {
                \Log::warning("ImportJob: Cache missing [{$this->importId}]");
                return;
            }

            $meta['processed_rows']   = (int)($meta['processed_rows']   ?? 0) + $rowCount;
            $meta['completed_chunks'] = (int)($meta['completed_chunks'] ?? 0) + 1;

            $totalChunks     = (int)($meta['total_chunks'] ?? 1);
            $completedChunks = (int) $meta['completed_chunks'];

            if ($completedChunks >= $totalChunks) {
                $meta['status']     = 'completed';
                $meta['percentage'] = 100;
            } else {
                $meta['percentage'] = (int) round(($completedChunks / $totalChunks) * 100);
            }

            cache()->put($metaKey, $meta, now()->addHours(3));

            \Log::info("Progress [{$this->importId}]: chunk {$completedChunks}/{$totalChunks} = {$meta['percentage']}%");

        } catch (\Illuminate\Contracts\Cache\LockTimeoutException $e) {
            \Log::warning("ImportJob: Could not acquire progress lock [{$this->importId}]: " . $e->getMessage());
        } finally {
            optional($lock)->release();
        }
    }

    // =========================================================
    protected function markFailed(): void
    {
        $key  = "import_meta_{$this->importId}";
        $meta = cache()->get($key, []);
        if (!empty($meta)) {
            $meta['status'] = 'failed';
            cache()->put($key, $meta, now()->addHours(3));
        }
    }

    public function failed(\Throwable $e): void
    {
        \Log::error("ImportJob FAILED [{$this->importId}]: " . $e->getMessage());
        $this->markFailed();
    }

}
