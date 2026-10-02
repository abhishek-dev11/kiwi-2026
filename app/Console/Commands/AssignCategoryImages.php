<?php

namespace App\Console\Commands;

use App\Models\Categories;
use App\Models\Products;
use Illuminate\Console\Command;

class AssignCategoryImages extends Command
{
    protected $signature = 'categories:assign-images {--force : Replace existing images} {--dry-run : Show picks without saving} {--supplier= : Only categories of this supplier ID}';

    protected $description = 'Give every subcategory without a usable image a relevant product image, saved to public/uploads/Category';

    private const STOP_WORDS = ['and', 'the', 'for', 'with', 'only'];

    public function handle(): int
    {
        $dir = public_path('uploads/Category');
        if (!is_dir($dir)) {
            mkdir($dir, 0777, true);
        }

        // Every subcategory level (mid, bottom, fourth). Without --force, only those with
        // no image — or whose image column points at a file no longer on disk.
        $categories = Categories::whereNull('is_delete')
            ->whereNotNull('top_level_category_id')
            ->when($this->option('supplier'), fn($q, $id) => $q->where('supplier_id', $id))
            ->get()
            ->when(!$this->option('force'), fn($c) => $c->filter(
                fn($cat) => empty($cat->image) || !is_file("{$dir}/{$cat->image}")
            ));

        $done = 0;
        $failed = [];

        foreach ($categories as $category) {
            $product = $this->pickProduct($category);
            if (!$product) {
                $failed[] = "{$category->id} {$category->title} (no product with an image)";
                continue;
            }

            $this->line("{$category->id} {$category->title} <= " . mb_substr(strip_tags($product->title), 0, 70));
            if ($this->option('dry-run')) {
                $done++;
                continue;
            }

            $name = $this->download($product->image, $dir);
            if (!$name) {
                $failed[] = "{$category->id} {$category->title} (download failed)";
                continue;
            }

            $category->image = $name;
            $category->save();
            $done++;
        }

        $this->info("Updated {$done} of {$categories->count()} categories.");
        foreach ($failed as $f) {
            $this->warn($f);
        }

        return self::SUCCESS;
    }

    // Products in the category itself, else of its parent (third-level) category, else
    // of its mid-level ancestor; the best title/description match for the category name
    // wins.
    private function pickProduct(Categories $category): ?Products
    {
        $parent = Categories::find($category->bottom_level_category_id);
        $scopes = array_filter([
            ['category', $category->id],
            ['bottom_level_category_id', $category->id],
            ['mid_level_category_id', $category->id],
            ['bottom_level_category_id', $parent?->id],
            ['mid_level_category_id', $parent?->mid_level_category_id],
            ['mid_level_category_id', $category->mid_level_category_id],
        ], fn($s) => $s[1]);

        $words = array_filter(
            preg_split('/\W+/', strtolower($category->title)),
            fn($w) => strlen($w) > 2 && !in_array($w, self::STOP_WORDS, true)
        );

        foreach ($scopes as [$column, $id]) {
            $products = Products::whereNull('is_delete')
                ->where($column, $id)
                ->whereNotNull('image')
                ->where('image', '!=', '')
                ->get();

            if ($products->isEmpty()) {
                continue;
            }

            return $products->sortByDesc(function ($p) use ($words) {
                $text = strtolower($p->title . ' ' . $p->description);
                $score = 0;
                foreach ($words as $w) {
                    $score += str_contains($text, rtrim($w, 's')) ? 1 : 0;
                }
                return $score;
            })->first();
        }

        return null;
    }

    // A product image is either a remote URL or (since imports download product images)
    // a filename in public/uploads/Product — copy the local file in that case.
    private function download(string $url, string $dir): ?string
    {
        if (!preg_match('#^https?://#i', $url)) {
            $source = public_path('uploads/Product/' . $url);
            $data   = is_file($source) ? file_get_contents($source) : false;
        } else {
            $context = stream_context_create(['http' => ['timeout' => 20, 'user_agent' => 'Mozilla/5.0']]);
            $data = @file_get_contents($url, false, $context);
        }
        if (!$data || strlen($data) < 500) {
            return null;
        }

        $ext = strtolower(pathinfo((string) parse_url($url, PHP_URL_PATH), PATHINFO_EXTENSION)) ?: 'jpg';
        $name = time() . '_' . uniqid() . '.' . $ext;
        file_put_contents("{$dir}/{$name}", $data);

        return $name;
    }
}
