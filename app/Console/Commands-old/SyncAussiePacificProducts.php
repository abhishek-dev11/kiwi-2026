<?php

namespace App\Console\Commands;

use App\Http\Controllers\Admin\ProductController;
use Illuminate\Console\Command;

class SyncAussiePacificProducts extends Command
{
    protected $signature = 'products:sync-aussiepacific';
    protected $description = 'Trigger the Aussie Pacific paged product import';

    public function handle(ProductController $controller): int
    {
        $importId = $controller->startAussiePacificImport();
        $this->info("Aussie Pacific sync dispatched. Import ID: {$importId}");
        return self::SUCCESS;
    }
}