<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use App\Console\Commands\ApplyBillingPenalties;
use App\Console\Commands\SendContractExpiryWarnings;
use App\Console\Commands\AutoArchiveExpiredContracts;
use Illuminate\Console\Scheduling\Schedule;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware) {
        $middleware->alias([
            'role'     => \App\Http\Middleware\EnsureUserHasRole::class,
            'activated'=> \App\Http\Middleware\EnsureAccountActivated::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions) {
        //
    })
    ->withSchedule(function (Schedule $schedule) {
        // Daily background tasks per the documentation
        $schedule->command('billing:apply-penalties')->dailyAt('00:05');
        $schedule->command('contracts:send-warnings')->dailyAt('00:10');
        $schedule->command('contracts:auto-archive')->dailyAt('00:15');
    })
    ->create();
