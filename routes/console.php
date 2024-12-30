<?php

use App\Http\Controllers\PlayerShipController;
use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;
use Illuminate\Support\Facades\Log;
use App\Http\Controllers\ShipController;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote')->hourly();

// Add a test task that runs every minute
//Schedule::call(function () {
//  Log::info('Test scheduler task running at: ' . now());
//})->everyMinute();

Schedule::call(function () {
    try {
        Log::info("Fetch ships cron started from console.php");
        app(ShipController::class)->fetchAndStoreShips();
        Log::info("Fetch ships cron completed from console.php");
    } catch (\Exception $e) {
        Log::error('Fetch ships cron failed from console.php: ' . $e->getMessage());
    }
})->monthlyOn(15, '01:00');


Schedule::call(function () {
    try {
        Log::info("Fetch player-ships cron started from console.php");
        app(PlayerShipController::class)->updatePlayerShips();
        Log::info("Fetch player-ships cron completed from console.php");
    } catch (\Exception $e) {
        Log::error('Fetch player-ships cron failed from console.php: ' . $e->getMessage());
    }
})->dailyAt('02:00');
