<?php


namespace App\Console\Commands;

use App\Services\PlayerShipService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class fetchOverallPlayerStatsCommand extends Command
{
    protected $signature = 'fetch:overall-stats';
    protected $description = 'Fetch and store overall player stats';

    public function handle(PlayerShipService $playerShipService)
    {
        Log::info('FetchOverallPlayerStatsCommand started');
        $result = $playerShipService->fetchAndStoreOverallPlayerStats();
        Log::info('FetchOverallPlayerStatsCommand finished', ['result' => $result]);
        return 0;
    }
}
