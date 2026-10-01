<?php

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use App\Http\Controllers\Admin\ProductController;
use App\Http\Middleware\AdminCheckLogin;
use App\Http\Middleware\AdminAuth;
use App\Http\Middleware\UserCheckLogin;
use App\Http\Middleware\FrontUserAuth;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: [
            __DIR__.'/../routes/web.php',   // admin
            __DIR__.'/../routes/front.php', // frontend
        ],
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->alias([
            'admin_checkLogin' => AdminCheckLogin::class,
            'adminAuth' => AdminAuth::class,
            'user_checkLogin' => UserCheckLogin::class,
            'frontuserAuth' => FrontUserAuth::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        //
    })
    ->withSchedule(function (Schedule $schedule): void {
        // $schedule->call(fn () => app(ProductController::class)->startAussiePacificImport())
        //     ->dailyAt('02:00')
        //     ->name('aussiepacific-sync')
        //     ->withoutOverlapping();

        // $schedule->call(fn () => app(ProductController::class)->startBanburyImport())
        //     ->dailyAt('02:30')
        //     ->name('banbury-sync')
        //     ->withoutOverlapping();

        // $schedule->call(fn () => app(ProductController::class)->startLogoLineImport())
        //     ->dailyAt('03:00')
        //     ->name('logoline-sync')
        //     ->withoutOverlapping();

        // $schedule->call(fn () => app(ProductController::class)->startFashionBizImport())
        //     ->dailyAt('03:30')
        //     ->name('fashionbiz-sync')
        //     ->withoutOverlapping();

        // $schedule->call(fn () => app(ProductController::class)->startTrendsImport())
        //     ->dailyAt('04:00')
        //     ->name('trends-sync')
        //     ->withoutOverlapping();

        // $schedule->call(fn () => app(ProductController::class)->startPremiumApparelImport())
        //     ->dailyAt('04:30')
        //     ->name('trends-sync')
        //     ->withoutOverlapping();


    })
    ->create();
