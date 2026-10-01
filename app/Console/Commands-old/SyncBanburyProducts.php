<?php

namespace App\Console\Commands;

use App\Http\Controllers\Admin\ProductController;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class SyncBanburyProducts extends Command
{
    protected $signature = 'products:sync-banbury';
    protected $description = 'Trigger the Banbury (Cloke Clothing) paged product import';

    public function handle(ProductController $controller): int
    {
        $startedAt = now();
        Log::info("[products:sync-banbury] Cron fired at {$startedAt}.");
        $this->info("Starting Banbury sync at {$startedAt}...");

        try {
            $importId = $controller->startBanburyImport();
        } catch (\Throwable $e) {
            // Catches things like Suppliers::findOrFail(5) throwing a
            // ModelNotFoundException if supplier ID 5 isn't Cloke Clothing (or
            // doesn't exist) — otherwise this would crash the cron process
            // with nothing but a bare stack trace to go on.
            Log::error("[products:sync-banbury] Failed to dispatch: {$e->getMessage()}", [
                'exception' => $e,
            ]);
            $this->error("Banbury sync failed to dispatch: {$e->getMessage()}");
            return self::FAILURE;
        }

        $this->info("Banbury sync dispatched. Import ID: {$importId}");
        Log::info("[products:sync-banbury] Dispatched import {$importId}, took " . $startedAt->diffInSeconds(now()) . 's to dispatch.');

        return self::SUCCESS;
    }
}
