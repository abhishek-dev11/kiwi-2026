<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\DB;
use App\Models\Products;

class ImageDownloadJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $timeout = 120;
    public $tries   = 3;
    public $backoff = 15;

    protected int    $productId;
    protected int    $colorVariantId;
    protected string $mainImageUrl;
    protected array  $galleryImageUrls;

    // ── Disk path  = where file is physically saved (relative to public/)
    // ── DB path    = what gets stored in the database column
    const MAIN_DISK_PATH    = 'uploads/Product';         // public/uploads/Product/file.jpg
    const MAIN_DB_PATH      = 'Product';                 // stored in DB as: Product/file.jpg

    const GALLERY_DISK_PATH = 'uploads/ProductVariant';  // public/uploads/ProductVariant/file.jpg
    const GALLERY_DB_PATH   = 'ProductVariant';           // stored in DB as: ProductVariant/file.jpg

    public function __construct(
        int    $productId,
        string $mainImageUrl,
        array  $galleryImageUrls = [],
        string $storagePath      = 'uploads/Product',    // kept for backward compat, unused
        int    $colorVariantId   = 0
    ) {
        $this->productId        = $productId;
        $this->colorVariantId   = $colorVariantId;
        $this->mainImageUrl     = $mainImageUrl;
        $this->galleryImageUrls = $galleryImageUrls;
    }

    // =========================================================
    public function handle(): void
    {
        \Log::info("ImageDownloadJob START — productId={$this->productId}");

        $this->ensureDirectoriesExist();

        // ── 1. Download & update main product image ──────────
        if (!empty($this->mainImageUrl)) {
            $localPath = $this->downloadImage(
                $this->mainImageUrl,
                self::MAIN_DISK_PATH,
                self::MAIN_DB_PATH
            );

            if ($localPath) {
                Products::where('id', $this->productId)
                    ->update(['image' => $localPath]);

                \Log::info("ImageDownloadJob: main image saved — {$localPath}");
            }
        }

        // ── 2. Download & insert gallery images into product_variant_images ──
        if (!empty($this->galleryImageUrls) && $this->colorVariantId > 0) {

            // Delete old records for this variant before re-inserting
            DB::table('product_variant_images')
                ->where('product_id', $this->productId)
                ->where('product_variant_id', $this->colorVariantId)
                ->delete();

            $galleryBatch = [];

            foreach ($this->galleryImageUrls as $url) {
                if (empty($url)) continue;

                $localPath = $this->downloadImage(
                    $url,
                    self::GALLERY_DISK_PATH,
                    self::GALLERY_DB_PATH
                );

                if ($localPath) {
                    $galleryBatch[] = [
                        'product_id'         => $this->productId,
                        'product_variant_id' => $this->colorVariantId,
                        'imageUrl'           => $localPath,
                        'created_at'         => now(),
                        'updated_at'         => now(),
                    ];

                    \Log::info("ImageDownloadJob: gallery image saved — {$localPath}");
                }
            }

            // ── Single bulk insert ──
            if (!empty($galleryBatch)) {
                DB::table('product_variant_images')->insertOrIgnore($galleryBatch);
            }
        }

        \Log::info("ImageDownloadJob DONE — productId={$this->productId}");
    }

    // =========================================================
    // DOWNLOAD A SINGLE IMAGE
    // =========================================================
    /**
     * @param string $url       Remote image URL
     * @param string $diskPath  Folder relative to public/ where file is saved  e.g. "uploads/Product"
     * @param string $dbPath    Prefix stored in DB column                       e.g. "Product"
     */
    protected function downloadImage(
        string $url,
        string $diskPath,
        string $dbPath
    ): ?string {
        try {
            $filename = basename(parse_url($url, PHP_URL_PATH));

            if (empty($filename)) {
                \Log::warning("ImageDownloadJob: Could not parse filename from URL: {$url}");
                return null;
            }

            $fullPath  = public_path("{$diskPath}/{$filename}"); // public/uploads/Product/file.jpg
            $localPath = "{$dbPath}/{$filename}";               // Product/file.jpg  ← saved in DB

            // ── Skip if already downloaded ──
            if (file_exists($fullPath)) {
                \Log::info("ImageDownloadJob: already exists, skipping — {$filename}");
                return $localPath;
            }

            // ── Fetch from remote ──
            $context = stream_context_create([
                'http' => [
                    'timeout'         => 30,
                    'follow_location' => true,
                    'user_agent'      => 'Mozilla/5.0 (compatible; ProductImporter/1.0)',
                ],
                'ssl' => [
                    'verify_peer'      => false,
                    'verify_peer_name' => false,
                ],
            ]);

            $imageContents = file_get_contents($url, false, $context);

            if ($imageContents === false) {
                \Log::warning("ImageDownloadJob: file_get_contents failed for URL: {$url}");
                return null;
            }

            // ── Validate it's actually an image ──
            $tmpFile = tempnam(sys_get_temp_dir(), 'img_');
            file_put_contents($tmpFile, $imageContents);
            $mime = mime_content_type($tmpFile);
            unlink($tmpFile);

            $allowedMimes = ['image/jpeg', 'image/png', 'image/gif', 'image/webp', 'image/svg+xml'];
            if (!in_array($mime, $allowedMimes)) {
                \Log::warning("ImageDownloadJob: invalid MIME [{$mime}] for URL: {$url}");
                return null;
            }

            // ── Save to disk ──
            file_put_contents($fullPath, $imageContents);

            return $localPath;

        } catch (\Throwable $e) {
            \Log::error("ImageDownloadJob: download error [{$url}]: " . $e->getMessage());
            return null;
        }
    }

    // =========================================================
    // ENSURE BOTH DIRECTORIES EXIST
    // =========================================================
    protected function ensureDirectoriesExist(): void
    {
        foreach ([self::MAIN_DISK_PATH, self::GALLERY_DISK_PATH] as $path) {
            $dir = public_path($path);
            if (!is_dir($dir)) {
                mkdir($dir, 0755, true);
                \Log::info("ImageDownloadJob: created directory — {$dir}");
            }
        }
    }

    // =========================================================
    public function failed(\Throwable $e): void
    {
        \Log::error("ImageDownloadJob FAILED — productId={$this->productId}: " . $e->getMessage());
    }
}