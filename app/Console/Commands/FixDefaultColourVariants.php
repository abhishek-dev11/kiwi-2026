<?php

namespace App\Console\Commands;

use App\Models\Attributes;
use App\Models\ProducSubtVariantItems;
use App\Models\Products;
use App\Models\ProductSubVariants;
use App\Models\ProductVariants;
use App\Models\ProductVariantImages;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

// Earlier imports stored a missing colour as a "Default" Color value, so size-only
// products showed "Color: Default" on the product page. This converts those products
// to the single-attribute layout the import now builds (see
// ProductImportJob::insertSingleAttributeVariants()): Size variants priced directly,
// no Color attribute. Products that also have real colours are only reported.
class FixDefaultColourVariants extends Command
{
    protected $signature = 'products:fix-default-colour {--supplier= : Only products of this supplier ID} {--dry-run : Show what would change without saving}';

    protected $description = 'Convert products whose only colour is "Default" into size-only variable products';

    public function handle(): int
    {
        $colorId = Attributes::where('title', 'Color')->value('id');
        $sizeId  = Attributes::firstOrCreate(['title' => 'Size'])->id;

        $productIds = ProductVariants::where('attribute_id', $colorId)
            ->where('value', 'Default')
            ->distinct()
            ->pluck('product_id');

        $products = Products::whereIn('id', $productIds)
            ->when($this->option('supplier'), fn($q, $id) => $q->where('supplier_id', $id))
            ->get();

        $converted = 0;
        $skipped   = [];

        foreach ($products as $product) {
            $colours = ProductVariants::where('product_id', $product->id)->where('attribute_id', $colorId)->get();

            if ($colours->where('value', '!=', 'Default')->isNotEmpty()) {
                $skipped[] = "#{$product->id} {$product->title} — also has real colours; fix by re-importing";
                continue;
            }

            $defaultIds = $colours->pluck('id');
            $subs       = ProductSubVariants::where('product_id', $product->id)->get()->keyBy('id');
            $items      = ProducSubtVariantItems::where('product_id', $product->id)->whereIn('product_variant_id', $defaultIds)->get();

            if ($subs->isEmpty() || $subs->where('value', 'Default')->isNotEmpty()) {
                $skipped[] = "#{$product->id} {$product->title} — no real sizes; fix by re-importing";
                continue;
            }

            $this->line("#{$product->id} " . mb_substr($product->title, 0, 70) . " → Size: " . $subs->pluck('value')->implode(', '));
            $converted++;

            if ($this->option('dry-run')) {
                continue;
            }

            DB::transaction(function () use ($product, $sizeId, $defaultIds, $subs, $items) {
                foreach ($items as $item) {
                    $sub = $subs->get($item->product_sub_variant_id);
                    if (!$sub) {
                        $item->delete();   // orphan price row with no size
                        continue;
                    }

                    $sizeVariant = ProductVariants::firstOrCreate([
                        'product_id'   => $product->id,
                        'attribute_id' => $sizeId,
                        'value'        => $sub->value,
                    ]);

                    $item->product_variant_id     = $sizeVariant->id;
                    $item->product_sub_variant_id = null;
                    $item->save();
                }

                ProductVariantImages::where('product_id', $product->id)->whereIn('product_variant_id', $defaultIds)->delete();
                ProductVariants::whereIn('id', $defaultIds)->delete();
                ProductSubVariants::whereIn('id', $subs->keys())->delete();

                $product->attribute_id             = $sizeId;
                $product->sub_variant_attribute_id = null;
                $product->save();
            });
        }

        $verb = $this->option('dry-run') ? 'Would convert' : 'Converted';
        $this->info("{$verb} {$converted} of {$products->count()} products.");
        foreach ($skipped as $s) {
            $this->warn("Skipped {$s}");
        }

        return self::SUCCESS;
    }
}
