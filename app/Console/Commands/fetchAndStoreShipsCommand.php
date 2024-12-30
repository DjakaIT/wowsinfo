<?php

namespace App\Console\Commands;

use App\Http\Controllers\ShipController;
use Exception;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class fetchAndStoreShipsCommand extends Command
{

    protected $signature = 'fetch-store:ships';

    protected $description = 'fetch and store ships from wiki';

    public function handle()
    {
        $logFilePath = storage_path('logs/ships.log');
        $logger = Log::build([
            'driver' => 'single',
            'path' => $logFilePath,
        ]);

        try {
            $logger->info("Fetch ships cron started ");
            app(ShipController::class)->fetchAndStoreShips();
        } catch (Exception $e) {
            $logger->error('Fetch ships cron failed: ' . $e->getMessage());
        }

        $logger->info('Fetching ships cron finished');
    }
}
