<?php

namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;
use Illuminate\Support\Facades\Log;

class Kernel extends ConsoleKernel
{
    protected $commands = [
        Commands\fetchAndStorePlayerShipsCommand::class,
        Commands\fetchAndStoreShipsCommand::class,
    ];

    protected function schedule(Schedule $schedule): void
    {
        Log::info('Scheduler started, attempting to register commands');

        try {
            // Ships fetch - every 2 minutes
            $schedule->command('fetch-store:ships')
                ->everyTwoMinutes()
                ->appendOutputTo(storage_path('logs/ships.log'))
                ->before(function () {
                    Log::info('Starting fetch-store:ships command');
                })
                ->after(function () {
                    Log::info('Completed fetch-store:ships command');
                });

            Log::info('Successfully registered fetch-store:ships command');

            // Player ships fetch - daily at 2 AM
            $schedule->command('fetch-store:player-ships')
                ->dailyAt('02:00')
                ->appendOutputTo(storage_path('logs/Fetch_player_ships.log'))
                ->before(function () {
                    Log::info('Starting fetch-store:player-ships command');
                })
                ->after(function () {
                    Log::info('Completed fetch-store:player-ships command');
                });

            Log::info('Successfully registered fetch-store:player-ships command');
        } catch (\Exception $e) {
            Log::error('Error registering commands: ' . $e->getMessage());
        }
    }

    protected function commands(): void
    {
        try {
            Log::info('Loading commands from directory');
            $this->load(__DIR__ . '/Commands');
            Log::info('Successfully loaded commands from directory');

            require base_path('routes/console.php');
        } catch (\Exception $e) {
            Log::error('Error loading commands: ' . $e->getMessage());
        }
    }
}
