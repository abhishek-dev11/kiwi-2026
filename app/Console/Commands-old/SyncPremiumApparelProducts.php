<?php

namespace App\Console\Commands;

use App\Http\Controllers\Admin\ProductController;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class SyncPremiumApparelProducts extends Command
{
    protected $signature = 'products:sync-premiumapparel';
    protected $description = 'Trigger the Premium Apparel chunked product import';

    public function handle(ProductController $controller): int
    {
        $startedAt = now();
        Log::info("[products:sync-premiumapparel] Cron fired at {$startedAt}.");
        $this->info("Starting Premium Apparel sync at {$startedAt}...");

        try {
            $importId = $controller->startPremiumApparelImport();
        } catch (\Throwable $e) {
            // Catches things like Suppliers::findOrFail(11) throwing a
            // ModelNotFoundException if supplier ID 11 isn't Premium Apparel
            // (or doesn't exist), or missing/invalid config('services.premiumapparel.*')
            // — otherwise this would crash the cron process with nothing but a
            // bare stack trace to go on.
            Log::error("[products:sync-premiumapparel] Failed to dispatch: {$e->getMessage()}", [
                'exception' => $e,
            ]);
            $this->error("Premium Apparel sync failed to dispatch: {$e->getMessage()}");
            return self::FAILURE;
        }

        $this->info("Premium Apparel sync dispatched. Import ID: {$importId}");
        Log::info("[products:sync-premiumapparel] Dispatched import {$importId}, took " . $startedAt->diffInSeconds(now()) . 's to dispatch.');

        return self::SUCCESS;
    }
}
