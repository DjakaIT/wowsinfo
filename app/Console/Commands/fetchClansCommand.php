<?php

namespace App\Console\Commands;

use App\Http\Controllers\ClanController;
use Exception;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class fetchClansCommand extends Command
{
    protected $signature = 'fetch-store:clans';

    protected $description = 'fetch and store clans';

    public function handle()
    {
        $logFilePath = storage_path('logs/clans.log');
        $logger = Log::build([
            'driver' => 'single',
            'path' => $logFilePath,
        ]);


        try {
            $logger->info('fetch clans cron started');
            app(ClanController::class)->fetchAndStoreClans();
        } catch (Exception $e) {
            $logger->error('fetch clans cron failed: ' . $e->getMessage());
        }

        $logger->info('Fetching clans cron finished');
    }
}
