<?php

namespace App\Console\Commands;

use App\Http\Controllers\Admin\ProductController;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class SyncTrendsProducts extends Command
{
    protected $signature = 'products:sync-trends';
    protected $description = 'Trigger the Trends paged product import';

    public function handle(ProductController $controller): int
    {
        $startedAt = now();
        Log::info("[products:sync-trends] Cron fired at {$startedAt}.");
        $this->info("Starting Trends sync at {$startedAt}...");

        try {
            $importId = $controller->startTrendsImport();
        } catch (\Throwable $e) {
            // Catches things like Suppliers::findOrFail(7) throwing a
            // ModelNotFoundException if supplier ID 7 isn't Trends (or doesn't
            // exist), or missing/invalid config('services.trends.*') — otherwise
            // this would crash the cron process with nothing but a bare stack
            // trace to go on.
            Log::error("[products:sync-trends] Failed to dispatch: {$e->getMessage()}", [
                'exception' => $e,
            ]);
            $this->error("Trends sync failed to dispatch: {$e->getMessage()}");
            return self::FAILURE;
        }

        $this->info("Trends sync dispatched. Import ID: {$importId}");
        Log::info("[products:sync-trends] Dispatched import {$importId}, took " . $startedAt->diffInSeconds(now()) . 's to dispatch.');

        return self::SUCCESS;
    }
}
