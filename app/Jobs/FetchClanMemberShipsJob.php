<?php

namespace App\Jobs;

use App\Services\PlayerShipService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class FetchClanMemberShipsJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $clanId;

    public function __construct($clanId)
    {
        $this->clanId = $clanId;
    }

    public function handle(PlayerShipService $playerShipService)
    {
        try {
            Log::info("Starting ship stats update for clan", ['clan_id' => $this->clanId]);
            $playerShipService->fetchAndStorePlayerShipsForClan($this->clanId);
            Log::info("Completed ship stats update for clan", ['clan_id' => $this->clanId]);
        } catch (\Exception $e) {
            Log::error("Error updating ship stats for clan", [
                'clan_id' => $this->clanId,
                'error' => $e->getMessage()
            ]);
        }
    }
}
