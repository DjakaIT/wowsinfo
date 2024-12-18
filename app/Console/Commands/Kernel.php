<?php

namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel
{
    /**
     * Define the application's command schedule.
     */
    protected function schedule(Schedule $schedule): void
    {
        // $schedule->command('inspire')->hourly();
        // Add your scheduled tasks here.
        $schedule->command('fetch-store:player-ships')
            ->dailyAt('02:00')
            ->appendOutputTo(storage_path('logs/Fetch_player_ships.log'));
    }

    /**
     * Register the commands for the application.
     */
    protected function commands(): void
    {
        $this->load(__DIR__ . '/Commands');

        require base_path('routes/console.php');
    }


    protected $commands = [
        Commands\fetchAndStorePlayerShipsCommand::class,
    ];
}
