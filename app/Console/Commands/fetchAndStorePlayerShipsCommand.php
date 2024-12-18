<?php

namespace App\Console\Commands;

use App\Http\Controllers\PlayerShipController;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class fetchAndStorePlayerShipsCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'fetch-store:player-ships';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Fetch and store player ships stats daily';

    /**
     * Execute the console command.
     */

    public function __construct()
    {
        parent::__construct();
    }
    public function handle()
    {
        try {
            app(PlayerShipController::class)->updatePlayerShips();

            Log::info('FetchPlayerShipsCommand executed succesfully');
            $this->info("Players' ships data fetched and stored succesfully");
        } catch (\Exception $e) {
            Log::Error("FetchPlayerShipsCommand failed: " . $e->getMessage());
            $this->error("Failed fetching players' ships data, check logs");
        }
    }
}
