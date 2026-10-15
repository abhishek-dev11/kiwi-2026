<?php

namespace App\Console\Commands;

use App\Models\Products;
use App\Support\ProductImageDownloader;
use Illuminate\Console\Command;

class FetchProductImages extends Command
{
    protected $signature = 'products:fetch-images {--supplier= : Only products of this supplier ID} {--limit=0 : Stop after this many products}';

    protected $description = 'Download product images that an import could not fetch (image empty, image_url set) into public/uploads/Product';

    public function handle(): int
    {
        $downloader = new ProductImageDownloader();
        $limit      = (int) $this->option('limit');
        $seen = $done = $failed = 0;

        Products::whereNull('is_delete')
            ->where(fn($q) => $q->whereNull('image')->orWhere('image', ''))
            ->where('image_url', 'like', 'http%')
            ->when($this->option('supplier'), fn($q, $id) => $q->where('supplier_id', $id))
            ->orderBy('id')
            ->chunkById(100, function ($products) use ($downloader, $limit, &$seen, &$done, &$failed) {
                // One concurrent batch per 100 products.
                $downloader->prefetch($products->pluck('image_url')->all());

                foreach ($products as $product) {
                    if ($limit && $seen >= $limit) return false;
                    $seen++;

                    $file = $downloader->download($product->image_url);
                    if ($file) {
                        $product->image = $file;
                        $product->save();
                        $done++;
                    } else {
                        $failed++;
                        // A permanent failure (404 / not an image) won't improve on retry.
                        if (!$downloader->failedTransiently($product->image_url)) {
                            $product->image_url = null;
                            $product->save();
                        }
                    }
                }
            });

        $this->info("Images saved: {$done}; still failing: {$failed}.");
        if ($failed) {
            $this->line('Failures caused by an unreachable host are kept and can be retried by running this command again.');
        }

        return self::SUCCESS;
    }
}
