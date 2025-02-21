<?php

namespace App\Console\Commands;

use App\Http\Controllers\ClanMemberController;
use App\Services\ClanMemberService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class accountCreationCommand extends Command
{

    protected $signature = 'fetch-store:account-creation {player} {serverKey}';

    protected $description = 'Fetch and store account creation date of players';

    protected $clanMemberService;

    public function __construct(ClanMemberService $clanMemberService)
    {
        parent::__construct();
        $this->clanMemberService = $clanMemberService;
    }
    public function handle()
    {
        // Create a custom logger for this command
        $logFilePath = storage_path('logs/account_creation_date.log');
        $logger = Log::build([
            'driver' => 'single',
            'path' => $logFilePath,
        ]);

        try {
            $logger->info('AccountCreationCommand started.');

            $player = $this->argument('player');
            $serverKey = $this->argument('serverKey');
            // Call the Clan Members' method
            app(ClanMemberController::class)->fetchCreationDate($player, $serverKey);

            $logger->info('fetch account creation date command executed successfully.');
            $this->info("Players' account creation date fetched and stored successfully.");
        } catch (\Exception $e) {
            $logger->error("fetch account creation date command failed: " . $e->getMessage());
            $this->error("Failed fetching players' account creation date. Check the logs for details.");
        }

        $logger->info('account creation date command finished.');
    }
}
