<?php

namespace App\Console\Commands;

use App\Http\Controllers\Admin\ProductController;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class SyncFashionBizProducts extends Command
{
    protected $signature = 'products:sync-fashionbiz';
    protected $description = 'Trigger the Fashion Biz (Biz Collection) paged product import';

    public function handle(ProductController $controller): int
    {
        $startedAt = now();
        Log::info("[products:sync-fashionbiz] Cron fired at {$startedAt}.");
        $this->info("Starting Fashion Biz (Biz Collection) sync at {$startedAt}...");

        try {
            $importId = $controller->startFashionBizImport();
        } catch (\Throwable $e) {
            // Catches things like Suppliers::findOrFail(10) throwing a
            // ModelNotFoundException if supplier ID 10 isn't Biz Collection (or
            // doesn't exist), or the API token/config being missing/invalid —
            // otherwise this would crash the cron process with nothing but a
            // bare stack trace to go on.
            Log::error("[products:sync-fashionbiz] Failed to dispatch: {$e->getMessage()}", [
                'exception' => $e,
            ]);
            $this->error("Fashion Biz sync failed to dispatch: {$e->getMessage()}");
            return self::FAILURE;
        }

        $this->info("Fashion Biz sync dispatched. Import ID: {$importId}");
        Log::info("[products:sync-fashionbiz] Dispatched import {$importId}, took " . $startedAt->diffInSeconds(now()) . 's to dispatch.');

        return self::SUCCESS;
    }
}
