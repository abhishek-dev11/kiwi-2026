<?php

namespace App\Console\Commands;

use App\Http\Controllers\Admin\ProductController;
use Illuminate\Console\Command;

class SyncLogoLineProducts extends Command
{
    /**
     * This is the RECURRING sync command — safe to run on a schedule.
     * It calls LogoLineAuthService::getAccessToken(), which uses the stored
     * refresh token to keep the access token valid. It does NOT consume the
     * one-time auth_token — that only happens via `logoline:initiate`, which
     * should be run manually, once, whenever support issues a fresh token.
     */
    protected $signature = 'products:sync-logoline';
    protected $description = 'Trigger the Logo Line full-catalogue product import';

    public function handle(ProductController $controller): int
    {
        $importId = $controller->startLogoLineImport();
        $this->info("Logo Line sync dispatched. Import ID: {$importId}");
        return self::SUCCESS;
    }
}
