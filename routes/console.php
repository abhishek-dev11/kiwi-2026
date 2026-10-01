<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// Schedule::command('products:sync-aussiepacific')
//     ->everyThirtyMinutes()
//     ->withoutOverlapping();

// // Logo Line's API is rate-limited to 24 requests/day, 1 per 10-minute window
// // (see LogoLineProductImportJob). Hourly gives exactly 24 runs/day with an
// // hour of spacing between each — well clear of the 10-minute minimum.
// Schedule::command('products:sync-logoline')
//     ->hourly()
//     ->withoutOverlapping();

// // Banbury dispatches page-by-page like Aussie Pacific (same constructor shape:
// // importId, supplierId, page) rather than a single full-catalogue fetch like
// // Logo Line — no documented rate limit yet, so matching Aussie Pacific's
// // cadence until/unless Banbury's docs say otherwise.
// Schedule::command('products:sync-banbury')
//     ->everyTwoHours()
//     ->withoutOverlapping()
//     ->appendOutputTo(storage_path('logs/banbury-cron.log'))
//     ->onSuccess(function () {
//         Log::info('[schedule] products:sync-banbury finished without error.');
//     })
//     ->onFailure(function () {
//         Log::error('[schedule] products:sync-banbury exited with an error — check banbury-cron.log.');
//     });

// // Fashion Biz (Biz Collection) also dispatches page-by-page (importId,
// // supplierId, page — same constructor shape as Aussie Pacific/Banbury) with
// // no documented rate limit, so matching that same cadence for now.
// Schedule::command('products:sync-fashionbiz')
//     ->hourly()
//     ->withoutOverlapping()
//     ->appendOutputTo(storage_path('logs/fashionbiz-cron.log'))
//     ->onSuccess(function () {
//         Log::info('[schedule] products:sync-fashionbiz finished without error.');
//     })
//     ->onFailure(function () {
//         Log::error('[schedule] products:sync-fashionbiz exited with an error — check fashionbiz-cron.log.');
//     });

// // Trends has by far the most headroom of any supplier here — 300 req/min,
// // confirmed via response headers — and the full ~2,700-product catalogue only
// // needs ~27 paged requests plus one categories/stock fetch each. Matching the
// // same 30-minute cadence as the other paged suppliers for consistency, well
// // under any real constraint.
// Schedule::command('products:sync-trends')
//     ->everyThirtyMinutes()
//     ->withoutOverlapping()
//     ->appendOutputTo(storage_path('logs/trends-cron.log'))
//     ->onSuccess(function () {
//         Log::info('[schedule] products:sync-trends finished without error.');
//     })
//     ->onFailure(function () {
//         Log::error('[schedule] products:sync-trends exited with an error — check trends-cron.log.');
//     });

// // Premium Apparel's own docblock confirms a full run takes ~795s (~13 min)
// // end-to-end across ~15 chunks of 30 products each — by far the longest-
// // running sync here. Scheduling hourly rather than every 30 minutes gives it
// // comfortable headroom to finish well before the next run is due, even if a
// // run is slower than usual on a given day.
// Schedule::command('products:sync-premiumapparel')
//     ->hourly()
//     ->withoutOverlapping()
//     ->appendOutputTo(storage_path('logs/premiumapparel-cron.log'))
//     ->onSuccess(function () {
//         Log::info('[schedule] products:sync-premiumapparel finished without error.');
//     })
//     ->onFailure(function () {
//         Log::error('[schedule] products:sync-premiumapparel exited with an error — check premiumapparel-cron.log.');
//     });