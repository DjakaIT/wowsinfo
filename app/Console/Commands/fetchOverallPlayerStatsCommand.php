<?php


namespace App\Console\Commands;

use App\Services\PlayerShipService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use App\Http\Controllers\PlayerShipController;


class fetchOverallPlayerStatsCommand extends Command
{
    protected $signature = 'fetch:overall-stats';
    protected $description = 'Fetch and store overall player stats';

    public function handle(PlayerShipService $playerShipService)
    {
        Log::info('FetchOverallPlayerStatsCommand started');
        $result = $playerShipService->fetchAndStoreOverallPlayerStats();

        app(PlayerShipController::class)->cachePlayerStatistics();
        app(PlayerShipController::class)->cacheTopPlayers();

        Log::info('FetchOverallPlayerStatsCommand finished', ['result' => $result]);
        return 0;
    }
}
