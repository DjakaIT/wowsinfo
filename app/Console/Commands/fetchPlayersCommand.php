<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\PlayerService;
use App\Models\Player;
use Illuminate\Support\Facades\Log;

class fetchPlayersCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'fetch-store:players-data {server} {--search=} {--page=1} {--limit=100}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Fetch and store data about players';

    /**
     * Execute the console command.
     */


    protected $playerService;

    public function __construct(PlayerService $playerService)
    {
        parent::__construct();
        $this->playerService = $playerService;
    }
    public function handle()
    {
        try {
            $server = $this->argument('server');
            $search = $this->option('search') ?? '';
            $page = $this->option('page') ?? 1;
            $limit = $this->option('limit') ?? 100;

            $this->playerService->fetchAndStorePlayers($server, $search, $page);

            Log::info('fetchPlayersCommand executed successfully');
            $this->info("Player data fetched and stored successfully.");
        } catch (\Exception $e) {
            Log::error("fetchPlayersCommand FAILED: " . $e->getMessage());
            $this->error("Failed fetching players' data.");
        }
    }
}
