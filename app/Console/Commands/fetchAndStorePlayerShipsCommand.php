<?php

namespace App\Console\Commands;

use App\Http\Controllers\PlayerShipController;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class fetchAndStorePlayerShipsCommand extends Command
{
    protected $signature = 'fetch-store:player-ships';
    protected $description = 'Fetch and store player ships stats daily';

    public function handle()
    {
        // Create a custom logger for this command
        $logFilePath = storage_path('logs/Fetch_player_ships.log');
        $logger = Log::build([
            'driver' => 'single',
            'path' => $logFilePath,
        ]);

        try {
            $logger->info('FetchPlayerShipsCommand started.');

            // Call the PlayerShipController's method
            app(PlayerShipController::class)->updatePlayerShips();

            $logger->info('FetchPlayerShipsCommand executed successfully.');
            $this->info("Players' ships data fetched and stored successfully.");
        } catch (\Exception $e) {
            $logger->error("FetchPlayerShipsCommand failed: " . $e->getMessage());
            $this->error("Failed fetching players' ships data. Check the logs for details.");
        }

        $logger->info('FetchPlayerShipsCommand finished.');
    }
}
