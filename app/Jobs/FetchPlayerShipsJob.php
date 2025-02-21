<?php


namespace App\Jobs;

use App\Services\PlayerShipService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class FetchPlayerShipsJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function handle(PlayerShipService $playerShipService)
    {
        try {
            $result = $playerShipService->fetchAndStorePlayerShips();
            Log::info('Finished fetching and storing player ships', ['result' => $result]);
        } catch (\Exception $e) {
            Log::error('Error in FetchPlayerShipsJob: ' . $e->getMessage());
        }
    }
}
