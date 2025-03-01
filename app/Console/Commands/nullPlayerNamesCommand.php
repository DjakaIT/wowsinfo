<?php

namespace App\Console\Commands;

use App\Http\Controllers\PlayerShipController;
use Exception;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class nullPlayerNamesCommand extends Command
{
    protected $signature = 'fetch-store:getNullNames';

    protected $description = 'fetch and store names for players without names';

    public function handle()
    {
        $logFilePath = storage_path('logs/laravel.log');
        $logger = Log::build([
            'driver' => 'single',
            'path' => $logFilePath,
        ]);


        try {
            $logger->info('fetch null names cron started');
            app(PlayerShipController::class)->getNullNames();
        } catch (Exception $e) {
            $logger->error('fetch null names cron failed: ' . $e->getMessage());
        }

        $logger->info('Fetching null names cron finished');
    }
}
