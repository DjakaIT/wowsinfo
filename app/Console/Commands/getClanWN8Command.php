<?php

namespace App\Console\Commands;

use App\Http\Controllers\ClanController;
use Exception;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class getClanWN8Command extends Command
{
    protected $signature = 'fetch-store:getclanwn8';

    protected $description = 'fetch and store clans wn8';

    public function handle()
    {
        $logFilePath = storage_path('logs/clanwn8.log');
        $logger = Log::build([
            'driver' => 'single',
            'path' => $logFilePath,
        ]);


        try {
            $logger->info('fetch clans wn8 cron started');
            app(ClanController::class)->getClanWN8();
        } catch (Exception $e) {
            $logger->error('fetch clans wn8 cron failed: ' . $e->getMessage());
        }

        $logger->info('Fetching clans wn8 cron finished');
    }
}
