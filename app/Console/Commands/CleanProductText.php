<?php

namespace App\Console\Commands;

use App\Models\Products;
use App\Support\ProductTextCleaner;
use Illuminate\Console\Command;

// Applies the import-time ProductTextCleaner to products already in the database. Run it
// once after deploying the cleaner: imports match existing products on title +
// supplier, so a product still holding its old garbled title would otherwise be
// imported again as a duplicate.
class CleanProductText extends Command
{
    protected $signature = 'products:clean-text {--supplier= : Only products of this supplier ID} {--dry-run : Show changes without saving}';

    protected $description = 'Repair broken encoding / escape junk in existing product titles and descriptions';

    public function handle(): int
    {
        $titleChanges = $descChanges = 0;
        $skipped      = [];

        Products::query()
            ->when($this->option('supplier'), fn($q, $id) => $q->where('supplier_id', $id))
            ->orderBy('id')
            ->chunkById(200, function ($products) use (&$titleChanges, &$descChanges, &$skipped) {
                foreach ($products as $product) {
                    $title = ProductTextCleaner::title($product->title);
                    $desc  = ProductTextCleaner::description($product->description);
                    $dirty = false;

                    if ($title !== '' && $title !== $product->title) {
                        $clash = Products::where('supplier_id', $product->supplier_id)
                            ->where('id', '!=', $product->id)
                            ->where('title', $title)
                            ->exists();

                        if ($clash) {
                            $skipped[] = "#{$product->id}: cleaned title already used by another product of this supplier — \"{$title}\"";
                        } else {
                            $this->line("#{$product->id} title: " . mb_substr($title, 0, 100));
                            $product->title = $title;
                            $titleChanges++;
                            $dirty = true;
                        }
                    }

                    if ($desc !== (string) $product->description) {
                        $product->description = $desc;
                        $descChanges++;
                        $dirty = true;
                    }

                    if ($dirty && !$this->option('dry-run')) {
                        $product->save();
                    }
                }
            });

        $verb = $this->option('dry-run') ? 'Would update' : 'Updated';
        $this->info("{$verb} {$titleChanges} titles and {$descChanges} descriptions.");
        foreach ($skipped as $s) {
            $this->warn("Skipped {$s}");
        }

        return self::SUCCESS;
    }
}
