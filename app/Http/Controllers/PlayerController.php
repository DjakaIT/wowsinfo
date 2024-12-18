<?php

namespace App\Http\Controllers;

use App\Models\Player;
use Illuminate\Http\Request;
use App\Services\PlayerService;
use Illuminate\Support\Facades\Log;

class PlayerController extends Controller
{
    protected $PlayerService;

    public function __construct(PlayerService $playerService)
    {
        $this->PlayerService = $playerService;
    }

    public function generateSearchTerms()
    {
        $terms = [];
        foreach (range('a', 'z') as $letter) {
            $terms[] = $letter;

            foreach (range('a', 'z') as $secondLetter) {
                $terms[] = $letter . $secondLetter;

                foreach (range('a', 'z') as $thirdLetter) {
                    $terms[] = $letter . $secondLetter . $thirdLetter;
                }
            }
        }
        return $terms;
    }

    public function fetchAndStorePlayers(Request $request)
    {

        /* set_time_limit(0); */

        Log::info("Started fetching players");

        $servers = ['eu', 'na', 'asia'];
        $searchTerms = $this->generateSearchTerms();
        $limit = 100;

        foreach ($servers as $server) {
            foreach ($searchTerms as $search) {
                $page = 1;
                $hasMore = true;

                while ($hasMore) {
                    $players = $this->PlayerService->getAllPlayers($server, $search, $page, $limit);

                    if ($players) {
                        foreach ($players as $playerData) {

                            $clan_id = Player::where('account_id')->value('clan_id');

                            Player::updateOrCreate(
                                ['account_id' => $playerData['account_id']],
                                [
                                    'nickname' => $playerData['nickname'],
                                    'server' => strtoupper($server),
                                    'clan_id' => $clan_id,
                                ]
                            );
                            Log::info("Stored player with ID: " . $playerData['account_id'] . " on server: " . strtoupper($server));
                        }

                        Log::info("Fetched page {$page} for search term '{$search}' on server: " . strtoupper($server));
                        $page++;
                        $hasMore = count($players) === $limit;
                    } else {
                        Log::info("No more players found for search term '{$search}' on server: " . strtoupper($server));
                        $hasMore = false;
                    }
                }

                // Short delay to respect API rate limits
                usleep(500000); // 0.5 seconds
            }
        }

        return response()->json(['message' => 'All players fetched and stored in database successfully'], 201);
    }
}
