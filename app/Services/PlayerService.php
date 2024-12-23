<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use App\Models\Player;
use Illuminate\Support\Facades\Log;

class PlayerService
{
    protected $apiKey;
    protected $baseUrls;

    public function __construct()
    {
        $this->apiKey = config('services.wargaming.api_key');

        // Base URLs for each server
        $this->baseUrls = [
            'eu' => 'https://api.worldofwarships.eu',
            'na' => 'https://api.worldofwarships.com',
            'asia' => 'https://api.worldofwarships.asia',
        ];
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


    public function fetchAndStorePlayers($server, $search, $page = 1, $limit = 100)
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
                    $players = $this->getAllPlayers($server, $search, $page, $limit);

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

    public function getAllPlayers($server, $search, $page = 1, $limit = 100)
    {
        if (!isset($this->baseUrls[$server])) {
            Log::error("Invalid server specified: {$server}");
            return null;
        }

        $url = $this->baseUrls[$server] . "/wows/account/list/";
        try {
            $response = Http::get($url, [
                'application_id' => $this->apiKey,
                'search' => $search,
                'page_no' => $page,
                'limit' => $limit,
            ]);

            if ($response->failed()) {
                Log::error("Player API Request failed for server: {$server} with status: " . $response->status());
                return null;
            }

            $responseData = $response->json();

            if ($responseData['status'] === 'ok' && isset($responseData['data'])) {
                return $responseData['data'];
            } else {
                Log::error("Unexpected API response for server: {$server}", ['response' => $responseData]);
            }
        } catch (\Exception $e) {
            Log::error("Exception during Player API call: " . $e->getMessage());
        }

        return null;
    }
}
