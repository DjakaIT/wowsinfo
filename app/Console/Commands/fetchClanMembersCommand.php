<?php

namespace App\Console\Commands;

use App\Http\Controllers\ClanMemberController;
use App\Http\Controllers\ShipController;
use Exception;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class fetchClanMembersCommand extends Command
{

    protected $signature = 'fetch-store:clan-members';

    protected $description = 'fetch and store clan member info';

    public function handle()
    {

        $logFilePath = storage_path('logs/clan_members.log');
        $logger = Log::build([
            'driver' => 'single',
            'path' => $logFilePath,
        ]);

        try {
            $logger->info('Fetch clan members cron started ');
            app(ClanMemberController::class)->updateClanMembers();
        } catch (Exception $e) {
            $logger->error('Fetch clan members cron failed: ' . $e->getMessage());
        }
        $logger->info('Fetching clan members cron finished');
    }
}
