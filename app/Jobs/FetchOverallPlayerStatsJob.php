<?php

namespace App\Jobs;

use App\Services\PlayerShipService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class FetchOverallPlayerStatsJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function handle(PlayerShipService $playerShipService)
    {
        try {
            $result = $playerShipService->fetchAndStoreOverallPlayerStats();
            Log::info('Finished fetching and storing overall player stats', ['result' => $result]);
        } catch (\Exception $e) {
            Log::error('Error in FetchOverallPlayerStatsJob: ' . $e->getMessage());
        }
    }
}
