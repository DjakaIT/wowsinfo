<?php

namespace App\Services;

use App\Models\ClanMember;
use App\Models\Player;
use App\Models\PlayerShip;
use App\Models\Ship;
use App\Models\WikiVehicles;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;


class PlayerShipService
{
    protected $apiKey;
    protected $apiUrl = "https://api.worldofwarships.eu/wows/ships/stats/";
    protected $apiUrlNames = "https://api.worldofwarships.eu/wows/account/info/";

    protected $baseUrls;


    protected $expectedValues;
    public function __construct()
    {
        $this->apiKey = config('services.wargaming.api_key');

        $this->baseUrls = [
            'eu' => 'https://api.worldofwarships.eu',
            'na' => 'https://api.worldofwarships.com',
            'asia' => 'https://api.worldofwarships.asia',
        ];

        ini_set('memory_limit', '512M');
    }

    public function loadExpectedValues()
    {
        $path = resource_path('expected_values.json');
        if (!File::exists($path)) {
            Log::error("Expected values file not found at: $path");
            throw new \Exception("Expected values file not found");
        }

        $jsonContent = File::get($path);
        $decodedData = json_decode($jsonContent, true);

        if (json_last_error() !== JSON_ERROR_NONE) {
            Log::error("Invalid JSON in expected values file", [
                'error' => json_last_error_msg(),
                'path' => $path
            ]);
            throw new \Exception("Invalid JSON in expected values file");
        }

        $this->expectedValues = $decodedData;
    }

    private function calculateWN8($ship, $totalBattles, $totalFrags, $totalWins, $totalDamageDealt)
    {
        $shipId = $ship->ship_id; // Extract the ship_id from the model

        //check if it's missing or empty
        if (
            !isset($this->expectedValues['data'][$shipId]) ||
            empty($this->expectedValues['data'][$shipId])
        ) {
            Log::warning("Expected values not found or empty for ship_id: $shipId");
            return null;
        }

        //store expected values for each ship in a variable
        $expected = $this->expectedValues['data'][$shipId];

        //get final expected values by multiplying expected values with number of battles
        $expectedDamage = $expected['average_damage_dealt'] * $totalBattles;
        $expectedFrags = $expected['average_frags'] * $totalBattles;
        $expectedWins = ($expected['win_rate'] / 100) * $totalBattles;

        // Ratios
        $rDmg = $expectedDamage > 0 ? $totalDamageDealt / $expectedDamage : 0;
        $rFrags = $expectedFrags > 0 ? $totalFrags / $expectedFrags : 0;
        $rWins = $expectedWins > 0 ? $totalWins / $expectedWins : 0;

        // Normalize
        $nDmg = max(0, ($rDmg - 0.4) / (1 - 0.4));
        $nFrags = max(0, ($rFrags - 0.1) / (1 - 0.1));
        $nWins = max(0, ($rWins - 0.7) / (1 - 0.7));


        // WN8 formula
        $wn8 = (1000 * $nDmg) + (100 * $nFrags) + (200 * $nWins);


        return $wn8;
    }

    public function totalPlayerWN8($playerId)
    {
        $playerShips = PlayerShip::where('account_id', $playerId)->get();


        $total_weighted_wn8 = 0;
        $total_battles = 0;

        foreach ($playerShips as $playerShip) {
            //condition: if there's battles played at all for that player 
            //and corresponding wn8 for the ship played
            if ($playerShip->battles_played > 0 && $playerShip->wn8 !== null) {
                //weighted by total battles to get the total
                $total_weighted_wn8 += $playerShip->wn8 * $playerShip->battles_played;
                $total_battles += $playerShip->battles_played;
            }
        }

        $player_total_wn8 = $total_battles > 0 ? $total_weighted_wn8 / $total_battles : 0;



        return $player_total_wn8;
    }


    private function calculatePR($ship, $totalBattles, $totalFrags, $totalWins, $totalDamageDealt)
    {
        //PR FORMULA - DIFFERENT RATIOS BUT SAME PARAMETERS AS WN8
        if ($totalBattles <= 0) {
            return 0;
        }

        $shipId = $ship->ship_id;

        if (
            !isset($this->expectedValues['data'][$shipId]) ||
            empty($this->expectedValues['data'][$shipId])
        ) {
            Log::warning("Expected values not found or empty for ship_id: $shipId");
            return null;
        }

        //store expected values for each ship in a varibale
        $expected = $this->expectedValues['data'][$shipId];

        //get final expected values by multiplying expected values with number of battles
        $expectedDamage = $expected['average_damage_dealt'] * $totalBattles;
        $expectedFrags = $expected['average_frags'] * $totalBattles;
        $expectedWins = ($expected['win_rate'] / 100) * $totalBattles;

        // Ratios
        $rDmg = $expectedDamage > 0 ? $totalDamageDealt / $expectedDamage : 0;
        $rFrags = $expectedFrags > 0 ? $totalFrags / $expectedFrags : 0;
        $rWins = $expectedWins > 0 ? $totalWins / $expectedWins : 0;

        // Normalize
        $nDmg = max(0, ($rDmg - 0.4) / (1 - 0.4));
        $nFrags = max(0, ($rFrags - 0.1) / (1 - 0.1));
        $nWins = max(0, ($rWins - 0.7) / (1 - 0.7));


        // PR formula
        $pr = ceil((700 * $nDmg) + (300 * $nFrags) + (150 * $nWins));



        return $pr;
    }


    public function totalPlayerPR($playerId)
    {
        // Load expected values if not already loaded
        if (!isset($this->expectedValues)) {
            try {
                $this->loadExpectedValues();
            } catch (\Exception $e) {
                Log::error("Failed to load expected values for PR calculation", [
                    'error' => $e->getMessage()
                ]);
                return 0;
            }
        }

        $playerShips = PlayerShip::where('account_id', $playerId)
            ->where('battles_played', '>', 0)
            ->get();

        if ($playerShips->isEmpty()) {
            return 0;
        }

        // Initialize variables to accumulate values
        $totalActualDamage = 0;
        $totalActualWins = 0;
        $totalActualFrags = 0;

        $totalExpectedDamage = 0;
        $totalExpectedWins = 0;
        $totalExpectedFrags = 0;

        foreach ($playerShips as $playerShip) {
            $shipId = $playerShip->ship_id;
            $battles = $playerShip->battles_played;

            if ($battles <= 0) {
                continue;
            }

            // Skip if expected values not available
            if (
                !isset($this->expectedValues['data']) ||
                !isset($this->expectedValues['data'][$shipId]) ||
                empty($this->expectedValues['data'][$shipId])
            ) {
                Log::warning("Expected values not found for ship_id: $shipId in PR calculation");
                continue;
            }

            $expected = $this->expectedValues['data'][$shipId];

            // Accumulate actual values
            $totalActualDamage += $playerShip->damage_dealt;
            $totalActualWins += $playerShip->wins_count;
            $totalActualFrags += $playerShip->frags;

            // Accumulate expected values (expected × battles)
            $totalExpectedDamage += $expected['average_damage_dealt'] * $battles;
            $totalExpectedWins += ($expected['win_rate'] / 100) * $battles;
            $totalExpectedFrags += $expected['average_frags'] * $battles;
        }

        // Avoid division by zero
        if ($totalExpectedDamage <= 0 || $totalExpectedWins <= 0 || $totalExpectedFrags <= 0) {
            return 0;
        }

        // Calculate ratios
        $rDmg = $totalActualDamage / $totalExpectedDamage;
        $rFrags = $totalActualFrags / $totalExpectedFrags;
        $rWins = $totalActualWins / $totalExpectedWins;

        // Normalize
        $nDmg = max(0, ($rDmg - 0.4) / (1 - 0.4));
        $nFrags = max(0, ($rFrags - 0.1) / (1 - 0.1));
        $nWins = max(0, ($rWins - 0.7) / (1 - 0.7));

        // PR formula
        $pr = round(700 * $nDmg + 300 * $nFrags + 150 * $nWins);

        return $pr;
    }

    private function extractBattleStats($stats, $battleType)
    {
        return [
            'battles' => $stats[$battleType]['battles'] ?? 0,
            'wins' => $stats[$battleType]['wins'] ?? 0,
            'damage_dealt' => $stats[$battleType]['damage_dealt'] ?? 0,
            'frags' => $stats[$battleType]['frags'] ?? 0,
            'xp' => $stats[$battleType]['xp'] ?? 0,
            'survived_battles' => $stats[$battleType]['survived_battles'] ?? 0,
            'distance' => $stats[$battleType]['distance'] ?? 0,
            'ships_spotted' => $stats[$battleType]['ships_spotted'] ?? 0,
            'capture_points' => $stats[$battleType]['capture_points'] ?? 0,
            'dropped_capture_points' => $stats[$battleType]['dropped_capture_points'] ?? 0,
        ];
    }

    public function cacheTopPlayersList()
    {
        $stats24h = $this->getTopPlayersLast24Hours();
        $stats7d = $this->getTopPlayersLast7Days();
        $stats30d = $this->getTopPlayersLastMonth();

        Cache::put('stats_24h', $stats24h, now()->addHours());
        Cache::put('stats_7d', $stats7d, now()->addDay());
        Cache::put('stats_30d', $stats30d, now()->addDays(2));
    }



    public function getTopPlayersLast24Hours()
    {

        return Cache::remember('stats_24h', now()->addHours(4), function () {
            $last24Hours = now()->subHours(24);

            return PlayerShip::select('account_id', DB::raw('MAX(player_name) as player_name'), DB::raw('MAX(total_player_wn8) as total_player_wn8'))
                ->where('ship_tier', '>', 5)
                ->where('battles_played', '>', 5)
                ->where('last_battle_time', '>=', $last24Hours)
                ->groupBy('account_id')
                ->orderByDesc('total_player_wn8')
                ->limit(10)
                ->get()
                ->map(function ($player) {
                    return [
                        'name' => $player->player_name,
                        'wid' => $player->account_id,
                        'wn8' => $player->total_player_wn8,
                    ];
                })
                ->toArray();
        });
    }


    public function getTopPlayersLast7Days()
    {
        return Cache::remember('stats_7d', now()->addDay(), function () {
            $last7days = now()->subDays(6);

            $weeklyStats = PlayerShip::select('account_id', DB::raw('MAX(player_name) as player_name'), DB::raw('MAX(total_player_wn8) as total_player_wn8'))
                ->where('ship_tier', '>', 5)
                ->where('battles_played', '>', 30)
                ->where('last_battle_time', '>=', $last7days)
                ->groupBy('account_id')
                ->orderByDesc('total_player_wn8')
                ->limit(10)
                ->get()
                ->map(function ($player) {
                    return [
                        'name' => $player->player_name,
                        'wid' => $player->account_id,
                        'wn8' => $player->total_player_wn8,
                    ];
                })
                ->toArray();

            // If no weekly stats, fall back to daily stats
            if (empty($weeklyStats)) {
                Log::info("Weekly stats empty, using daily stats instead");

                $last24Hours = now()->subHours(24);
                return PlayerShip::select('account_id', DB::raw('MAX(player_name) as player_name'), DB::raw('MAX(total_player_wn8) as total_player_wn8'))
                    ->where('ship_tier', '>', 5)
                    ->where('battles_played', '>', 5)
                    ->where('last_battle_time', '>=', $last24Hours)
                    ->groupBy('account_id')
                    ->orderByDesc('total_player_wn8')
                    ->limit(10)
                    ->get()
                    ->map(function ($player) {
                        return [
                            'name' => $player->player_name,
                            'wid' => $player->account_id,
                            'wn8' => $player->total_player_wn8,
                        ];
                    })
                    ->toArray();
            }

            return $weeklyStats;
        });
    }

    public function getTopPlayersLastMonth()
    {
        return Cache::remember('stats_30d', now()->addDays(2), function () {
            $lastMonth = now()->subDays(25);

            $monthlyStats = PlayerShip::select('account_id', DB::raw('MAX(player_name) as player_name'), DB::raw('MAX(total_player_wn8) as total_player_wn8'))
                ->where('ship_tier', '>', 5)
                ->where('battles_played', '>', 120)
                ->where('last_battle_time', '>=', $lastMonth)
                ->groupBy('account_id')
                ->orderByDesc('total_player_wn8')
                ->limit(10)
                ->get()
                ->map(function ($player) {
                    return [
                        'name' => $player->player_name,
                        'wid' => $player->account_id,
                        'wn8' => $player->total_player_wn8,
                    ];
                })
                ->toArray();

            // If no monthly stats, fall back to weekly stats
            if (empty($monthlyStats)) {
                Log::info("Monthly stats empty, using weekly stats instead");

                $last7days = now()->subDays(6);
                $weeklyStats = PlayerShip::select('account_id', DB::raw('MAX(player_name) as player_name'), DB::raw('MAX(total_player_wn8) as total_player_wn8'))
                    ->where('ship_tier', '>', 5)
                    ->where('battles_played', '>', 30)
                    ->where('last_battle_time', '>=', $last7days)
                    ->groupBy('account_id')
                    ->orderByDesc('total_player_wn8')
                    ->limit(10)
                    ->get()
                    ->map(function ($player) {
                        return [
                            'name' => $player->player_name,
                            'wid' => $player->account_id,
                            'wn8' => $player->total_player_wn8,
                        ];
                    })
                    ->toArray();

                // If no weekly stats either, fall back to daily stats
                if (empty($weeklyStats)) {
                    Log::info("Weekly stats empty too, using daily stats instead");

                    $last24Hours = now()->subHours(24);
                    return PlayerShip::select('account_id', DB::raw('MAX(player_name) as player_name'), DB::raw('MAX(total_player_wn8) as total_player_wn8'))
                        ->where('ship_tier', '>', 5)
                        ->where('battles_played', '>', 5)
                        ->where('last_battle_time', '>=', $last24Hours)
                        ->groupBy('account_id')
                        ->orderByDesc('total_player_wn8')
                        ->limit(10)
                        ->get()
                        ->map(function ($player) {
                            return [
                                'name' => $player->player_name,
                                'wid' => $player->account_id,
                                'wn8' => $player->total_player_wn8,
                            ];
                        })
                        ->toArray();
                }

                return $weeklyStats;
            }

            return $monthlyStats;
        });
    }

    public function getTopPlayersOverall()
    {


        return PlayerShip::select('account_id', DB::raw('MAX(player_name) as player_name'), DB::raw('MAX(total_player_wn8) as total_player_wn8'))
            ->where('ship_tier', '>', 5)
            ->where('battles_overall', '>', 400)
            ->groupBy('account_id')
            ->orderByDesc('total_player_wn8')
            ->limit(10)
            ->get()
            ->map(function ($player) {
                return [
                    'name' => $player->player_name,
                    'wid' => $player->account_id,
                    'wn8' => $player->total_player_wn8,
                ];
            })
            ->toArray();
    }





    // TO DO: 
    //Ovdje treba proslijediti sa fronta ono što igrač klikne / upiše, 
    //npr. Ako klikne na "Bismarck" brod 
    //onda aplikacija taj string ovdje unese kao parametar
    // $ship_name = 'Bismarck';
    public function getPlayerStatsByVehicle($ship_name)
    {


        return DB::table('player_ships')
            ->select(
                'account_id',
                'player_name',
                'battles_played',
                'wins_count',
                'ship_tier',
                'ship_type',
                'survival_rate',
                'damage_dealt',
                'frags',
                'xp',
                'capture',
                'defend',
                'spotted',
                'wn8'
            )
            ->where('ship_name', $ship_name)
            ->orderBy('total_player_wn8', 'desc')
            ->get();
    }


    public function getNullNamePlayersNames(): void
    {
        $playerIds = PlayerShip::whereNull('player_name')->pluck('account_id')->unique()->all();

        if (empty($playerIds)) {
            Log::info("No players with null names found.");
            return;
        }

        Log::info("Found " . count($playerIds) . " players with null names.");

        // Group player IDs into batches of 100
        $batches = array_chunk($playerIds, 100);

        foreach ($this->baseUrls as $serverKey => $baseUrl) {
            Log::info("Processing server: " . strtoupper($serverKey) . " with " . count($batches) . " batches");

            foreach ($batches as $batchIndex => $batch) {
                Log::info("Processing batch " . ($batchIndex + 1) . "/" . count($batches) . " with " . count($batch) . " players");

                // Create comma-separated list of account IDs
                $accountIds = implode(',', $batch);

                $url = $baseUrl . "/wows/account/info/";

                try {
                    $response = Http::get($url, [
                        'application_id' => $this->apiKey,
                        'account_id' => $accountIds,
                    ]);

                    if ($response->successful()) {
                        $data = $response->json();

                        if (isset($data['data'])) {
                            $updatedCount = 0;

                            foreach ($data['data'] as $accountId => $accountData) {
                                if (isset($accountData['nickname'])) {
                                    $playerName = $accountData['nickname'];
                                    PlayerShip::where('account_id', $accountId)->update(['player_name' => $playerName]);
                                    $updatedCount++;
                                } else {
                                    Log::warning("Nickname not found for account_id: $accountId on server " . strtoupper($serverKey));
                                }
                            }

                            Log::info("Updated $updatedCount player names from server " . strtoupper($serverKey));
                        } else {
                            Log::warning("No data found in API response for batch on server " . strtoupper($serverKey));
                        }
                    } else {
                        Log::error("API request failed for batch on server " . strtoupper($serverKey), [
                            'status' => $response->status(),
                            'body' => $response->body()
                        ]);
                    }

                    // Add a small delay to avoid rate limiting
                    usleep(100000); // 100ms delay between batches

                } catch (\Exception $e) {
                    Log::error("Exception while fetching player names on server " . strtoupper($serverKey), [
                        'error' => $e->getMessage(),
                        'trace' => $e->getTraceAsString()
                    ]);
                }
            }
        }

        $remainingNull = PlayerShip::whereNull('player_name')->count();
        Log::info("Completed player name update. Remaining null names: $remainingNull");
    }


    public function fetchAndStoreOverallPlayerStats()
    {
        try {
            // Get players from each server's table - using uppercase table names
            $euPlayers = DB::table('Player_EU')->pluck('account_id')->unique()->all();
            $naPlayers = DB::table('Player_NA')->pluck('account_id')->unique()->all();
            $asiaPlayers = DB::table('Player_ASIA')->pluck('account_id')->unique()->all();
            $selfSearchPlayerIds = PlayerShip::distinct()->pluck('account_id')->all();

            // Remove players from region tables from self-search to avoid duplicates
            $selfSearchPlayerIds = array_diff($selfSearchPlayerIds, $euPlayers, $naPlayers, $asiaPlayers);

            Log::info("Overall stats - Player counts by source", [
                'eu' => count($euPlayers),
                'na' => count($naPlayers),
                'asia' => count($asiaPlayers),
                'self_search' => count($selfSearchPlayerIds)
            ]);

            // Define server-specific pools and their corresponding endpoint
            $serverPools = [
                'eu' => $euPlayers,
                'na' => $naPlayers,
                'asia' => $asiaPlayers
            ];

            $batchSize = 100; // API allows up to 100 accounts per request

            // CHANGED ORDER: Process self-search players first - try all endpoints
            if (!empty($selfSearchPlayerIds)) {
                Log::info("Processing self-search players for overall stats", ['count' => count($selfSearchPlayerIds)]);

                // Process players in batches of 100
                foreach (array_chunk($selfSearchPlayerIds, $batchSize) as $batchIndex => $batch) {
                    $batchProcessed = false;

                    // Try each server endpoint until we get a successful response
                    foreach ($this->baseUrls as $serverKey => $baseUrl) {
                        $overallUrl = $baseUrl . "/wows/account/info/";
                        $idsString = implode(',', $batch);

                        $response = Http::get($overallUrl, [
                            'application_id' => $this->apiKey,
                            'account_id' => $idsString,
                        ]);

                        if ($response->successful()) {
                            $data = $response->json();

                            if (isset($data['data']) && !empty($data['data'])) {
                                Log::info("Processing self-search batch {$batchIndex} on {$serverKey}", [
                                    'batch_size' => count($batch)
                                ]);

                                $this->processOverallStats($data, $serverKey);
                                $batchProcessed = true;
                                break; // Exit server loop once processed
                            }
                        }

                        // Short delay to prevent rate limiting
                        usleep(10000); // 10ms
                    }

                    if (!$batchProcessed) {
                        Log::warning("Could not find overall stats for self-search batch", [
                            'batch_index' => $batchIndex,
                            'player_count' => count($batch)
                        ]);
                    }
                }
            }

            // THEN process players from each server using the appropriate endpoint
            foreach ($serverPools as $serverKey => $players) {
                if (empty($players)) {
                    Log::info("No players from {$serverKey} server to process for overall stats");
                    continue;
                }

                $baseUrl = $this->baseUrls[$serverKey];
                $overallUrl = $baseUrl . "/wows/account/info/";

                Log::info("Processing {$serverKey} players for overall stats", ['count' => count($players)]);

                // Process in batches of 100 players
                foreach (array_chunk($players, $batchSize) as $batchIndex => $batch) {
                    $idsString = implode(',', $batch);

                    $response = Http::get($overallUrl, [
                        'application_id' => $this->apiKey,
                        'account_id' => $idsString,
                    ]);

                    if ($response->successful()) {
                        $data = $response->json();

                        if (isset($data['data']) && !empty($data['data'])) {
                            Log::info("Processing {$serverKey} batch {$batchIndex}", [
                                'batch_size' => count($batch)
                            ]);

                            $this->processOverallStats($data, $serverKey);
                        } else {
                            Log::warning("No data returned for {$serverKey} batch", [
                                'batch_index' => $batchIndex
                            ]);
                        }
                    } else {
                        Log::error("Failed to fetch overall stats for {$serverKey} batch", [
                            'batch_index' => $batchIndex,
                            'status' => $response->status(),
                            'response' => $response->body()
                        ]);
                    }

                    // Add a small delay to prevent rate limiting
                    usleep(10000); // 10ms
                }
            }

            return true;
        } catch (\Exception $e) {
            Log::error("Error in fetchAndStoreOverallPlayerStats", [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            return false;
        }
    }

    // Helper method to process overall stats API response data
    private function processOverallStats($data, $serverKey)
    {
        if (!isset($data['data']) || !is_array($data['data'])) {
            Log::warning("Overall stats API returned invalid data structure", [
                'server' => $serverKey
            ]);
            return;
        }

        // Build batch updates array
        $updates = [];
        $updateCount = 0;

        foreach ($data['data'] as $accountId => $accountData) {
            // Skip invalid data structure
            if (!isset($accountData['statistics']['pvp'])) {
                Log::warning("Overall stats for account $accountId not in expected format", [
                    'server' => strtoupper($serverKey)
                ]);
                continue;
            }

            // Skip inactive players
            if (isset($accountData['last_battle_time'])) {
                $cutoffTime = now()->subDays(40)->timestamp;
                if ($accountData['last_battle_time'] < $cutoffTime) {
                    continue;
                }
            }

            // Update player name if available
            if (isset($accountData['nickname'])) {
                $playerName = $accountData['nickname'];
                PlayerShip::where('account_id', $accountId)
                    ->whereNull('player_name')
                    ->update(['player_name' => $playerName]);
            }

            $pvp = $accountData['statistics']['pvp'];
            $updates[] = [
                'account_id'       => $accountId,
                'battles_overall'  => $pvp['battles'] ?? 0,
                'survived_overall' => $pvp['survived_battles'] ?? 0,
                'wins_count_overall' => $pvp['wins'] ?? 0,
                'damage_overall'   => $pvp['damage_dealt'] ?? 0,
                'defended_overall' => $pvp['dropped_capture_points'] ?? 0,
                'captured_overall' => $pvp['capture_points'] ?? 0,
                'xp_overall'       => $pvp['xp'] ?? 0,
                'spotted_overall'  => $pvp['ships_spotted'] ?? 0,
                'frags_overall'    => $pvp['frags'] ?? 0,
            ];

            $updateCount++;
        }

        // Perform batch update using transactions for better performance
        if (!empty($updates)) {
            DB::transaction(function () use ($updates) {
                foreach ($updates as $updateData) {
                    DB::table('player_ships')
                        ->where('account_id', $updateData['account_id'])
                        ->update([
                            'battles_overall'  => $updateData['battles_overall'],
                            'frags_overall' => $updateData['frags_overall'],
                            'survived_overall' => $updateData['survived_overall'],
                            'wins_count_overall' => $updateData['wins_count_overall'],
                            'damage_overall'   => $updateData['damage_overall'],
                            'defended_overall' => $updateData['defended_overall'],
                            'captured_overall' => $updateData['captured_overall'],
                            'xp_overall'       => $updateData['xp_overall'],
                            'spotted_overall'  => $updateData['spotted_overall'],
                        ]);
                }
            });

            Log::info("Batch updated overall stats", [
                'server' => strtoupper($serverKey),
                'updated_count' => $updateCount
            ]);
        }
    }


    private function getExistingPlayerName($accountId)
    {
        // Check PlayerShip table first
        $playerName = PlayerShip::where('account_id', $accountId)
            ->whereNotNull('player_name')
            ->value('player_name');

        if ($playerName) {
            return $playerName;
        }

        // Check ClanMember table if no name found
        $clanMemberName = DB::table('clan_members')
            ->where('account_id', $accountId)
            ->value('account_name');

        return $clanMemberName ?: null;
    }

    public function fetchAndStorePlayerShips()
    {
        try {
            $this->loadExpectedValues();
        } catch (\Exception $e) {
            Log::error("Failed to load expected values", [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            throw new \Exception("Failed to initialize: " . $e->getMessage());
        }

        Log::info('Starting fetchAndStorePlayerShips');

        try {
            // Get players from each server's table - using uppercase table names
            $euPlayers = DB::table('Player_EU')->pluck('account_id')->unique()->all();
            $naPlayers = DB::table('Player_NA')->pluck('account_id')->unique()->all();
            $asiaPlayers = DB::table('Player_ASIA')->pluck('account_id')->unique()->all();
            $selfSearchPlayerIds = PlayerShip::distinct()->pluck('account_id')->all();

            // Remove players from region tables from self-search to avoid duplicates
            $selfSearchPlayerIds = array_diff($selfSearchPlayerIds, $euPlayers, $naPlayers, $asiaPlayers);

            Log::info("Player counts by source", [
                'eu' => count($euPlayers),
                'na' => count($naPlayers),
                'asia' => count($asiaPlayers),
                'self_search' => count($selfSearchPlayerIds)
            ]);

            // Define server-specific pools and their corresponding endpoint
            $serverPools = [
                'eu' => $euPlayers,
                'na' => $naPlayers,
                'asia' => $asiaPlayers
            ];

            // CHANGED ORDER: Process self-search players first - try all endpoints
            if (!empty($selfSearchPlayerIds)) {
                Log::info("Processing self-search players", ['count' => count($selfSearchPlayerIds)]);

                foreach ($selfSearchPlayerIds as $playerId) {
                    $processed = false;

                    // Try each server endpoint
                    foreach ($this->baseUrls as $serverKey => $baseUrl) {
                        $url = $baseUrl . "/wows/ships/stats/";

                        $response = Http::get($url, [
                            'application_id' => $this->apiKey,
                            'account_id' => $playerId,
                            'extra' => 'pvp_solo,pvp_div2,pvp_div3'
                        ]);

                        if ($response->successful() && isset($response->json()['data'][$playerId])) {
                            $data = $response->json();
                            $playerName = PlayerShip::where('account_id', $playerId)->value('player_name');

                            $this->StorePlayerShips($playerId, $playerName, $data['data'][$playerId]);
                            $processed = true;
                            Log::info("Self-search player processed successfully", [
                                'player_id' => $playerId,
                                'server' => $serverKey
                            ]);
                            break; // Exit the server loop once we've found data
                        }

                        // Add a small delay between requests
                        usleep(50000);
                    }

                    if (!$processed) {
                        Log::warning("Could not find data for self-search player on any server", [
                            'player_id' => $playerId
                        ]);
                    }
                }
            }

            // THEN process players from each server using the appropriate endpoint
            foreach ($serverPools as $serverKey => $players) {
                if (empty($players)) {
                    Log::info("No players from {$serverKey} server to process");
                    continue;
                }

                $baseUrl = $this->baseUrls[$serverKey];
                $url = $baseUrl . "/wows/ships/stats/";

                Log::info("Processing {$serverKey} players", ['count' => count($players)]);

                foreach ($players as $playerId) {
                    $response = Http::get($url, [
                        'application_id' => $this->apiKey,
                        'account_id' => $playerId,
                        'extra' => 'pvp_solo,pvp_div2,pvp_div3'
                    ]);

                    if ($response->successful()) {
                        $data = $response->json();

                        // For server tables, we initially set playerName to null
                        // The getNullNamePlayersNames method will fill these in later
                        $playerName = $this->getExistingPlayerName($playerId);

                        Log::info("Processing player", ['player_id' => $playerId, 'server' => $serverKey]);

                        if (isset($data['data'][$playerId])) {
                            $this->StorePlayerShips($playerId, $playerName, $data['data'][$playerId]);
                        } else {
                            Log::warning("No ship data found for player", [
                                'player_id' => $playerId,
                                'server' => $serverKey
                            ]);
                        }
                    } else {
                        Log::error("Failed to fetch player ships", [
                            'account_id' => $playerId,
                            'server' => $serverKey,
                            'status' => $response->status(),
                            'response' => $response->body()
                        ]);
                    }

                    // Add a small delay to prevent rate limiting
                    usleep(10); // 10ms
                }
            }

            return true;
        } catch (\Exception $e) {
            Log::error("Error in fetchAndStorePlayerShips", [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            throw $e;
        }
    }

    public function StorePlayerShips($playerId, $playerName, $playerShipsData)
    {
        try {
            if (empty($playerShipsData)) {
                Log::warning("No ship data to process for player", [
                    'player_id' => $playerId,
                    'player_name' => $playerName
                ]);
                return false;
            }

            // Check if player has played recently
            $mostRecentBattleTime = 0;
            foreach ($playerShipsData as $shipStats) {
                $mostRecentBattleTime = max($mostRecentBattleTime, $shipStats['last_battle_time'] ?? 0);
            }

            // Skip if player hasn't played in the last 40 days
            $cutoffTime = now()->subDays(40)->timestamp;
            if ($mostRecentBattleTime < $cutoffTime) {
                Log::info("Skipping inactive player (no battles in last 40 days)", [
                    'player_id' => $playerId,
                    'player_name' => $playerName,
                    'last_battle_time' => date('Y-m-d', $mostRecentBattleTime)
                ]);
                return true; // Return true so the process continues, just skips this player
            }

            // Prepare batch insert/update data
            $batchData = [];
            $shipIds = [];
            $processedShipsCount = 0;
            $lastBattleTime = 0;

            // First pass: collect data and calculate metrics
            foreach ($playerShipsData as $shipStats) {
                // Skip ships with no data
                if (empty($shipStats) || !isset($shipStats['ship_id'])) {
                    continue;
                }

                // Find the ship using ship_id from the API
                $ship = WikiVehicles::where('ship_id', $shipStats['ship_id'])->first();

                if (!$ship) {
                    Log::warning("Ship not found in database", [
                        'api_ship_id' => $shipStats['ship_id'],
                        'player_id' => $playerId
                    ]);
                    continue;
                }

                // Track ship IDs for later batch update
                $shipIds[] = $shipStats['ship_id'];

                // Extract stats from ships table 
                $shipName = $ship->name ?? 'Unknown ship name';
                $shipType = $ship->type ?? 'Unknown ship type';
                $shipTier = $ship->tier ?? 'Unknown ship tier';
                $shipNation = $ship->nation ?? 'Unknown nation';

                // Initialize all battle type stats
                $pvpStats = [];
                $pveStats = [];
                $clubStats = [];
                $rankStats = [];

                if (isset($shipStats['pvp'])) {
                    $pvpStats = $this->extractBattleStats($shipStats, 'pvp');
                }

                if (isset($shipStats['pve'])) {
                    $pveStats = $this->extractBattleStats($shipStats, 'pve');
                }

                if (isset($shipStats['club'])) {
                    $clubStats = $this->extractBattleStats($shipStats, 'club');
                }

                if (isset($shipStats['rank_solo'])) {
                    $rankStats = $this->extractBattleStats($shipStats, 'rank_solo');
                }

                // Calculate total battles
                $totalBattles = ($pvpStats['battles'] ?? 0);

                // Skip if no battles
                if ($totalBattles <= 0) {
                    continue;
                }

                // Calculate total damage
                $totalDamageDealt = ($pvpStats['damage_dealt'] ?? 0);
                $averageDamage = $totalBattles > 0 ? $totalDamageDealt / $totalBattles : 0;
                $totalWins = ($pvpStats['wins'] ?? 0);
                $totalFrags = ($pvpStats['frags'] ?? 0);
                $totalXp = ($pvpStats['xp'] ?? 0);
                $totalCapture = ($pvpStats['capture_points'] ?? 0);
                $totalDefend = ($pvpStats['dropped_capture_points'] ?? 0);
                $totalSpotted = ($pvpStats['ships_spotted'] ?? 0);

                // Calculate survival rate
                $totalSurvivedBattles = ($pvpStats['survived_battles'] ?? 0) +
                    ($pveStats['survived_battles'] ?? 0) +
                    ($clubStats['survived_battles'] ?? 0) +
                    ($rankStats['survived_battles'] ?? 0);
                $survivalRate = $totalBattles > 0 ? ($totalSurvivedBattles / $totalBattles) * 100 : 0;

                // Calculate WN8 and PR
                $wn8 = $this->calculateWN8($ship, $totalBattles, $totalFrags, $totalWins, $totalDamageDealt);
                $pr = $this->calculatePR($ship, $totalBattles, $totalFrags, $totalWins, $totalDamageDealt);
                $pr = $pr !== null ? $pr : 0;

                // Keep track of most recent battle
                $lastBattleTime = max($lastBattleTime, $shipStats['last_battle_time'] ?? 0);

                // Add to batch data array
                $batchData[] = [
                    'account_id' => $playerId,
                    'player_name' => $playerName,
                    'ship_id' => $shipStats['ship_id'],
                    'battles_played' => $totalBattles,
                    'last_battle_time' => $shipStats['last_battle_time'],
                    'wins_count' => $totalWins,
                    'damage_dealt' => $totalDamageDealt,
                    'average_damage' => $averageDamage,
                    'frags' => $totalFrags,
                    'survival_rate' => $survivalRate,
                    'xp' => $totalXp,
                    'ship_name' => $shipName,
                    'ship_type' => $shipType,
                    'ship_tier' => $shipTier,
                    'ship_nation' => $shipNation,
                    'distance' => $shipStats['distance'] ?? 0,
                    'wn8' => $wn8,
                    'pr' => $pr,
                    'capture' => $totalCapture,
                    'defend' => $totalDefend,
                    'spotted' => $totalSpotted,
                    'pve_battles' => $pveStats['battles'] ?? 0,
                    'pve_wins' => $pveStats['wins'] ?? 0,
                    'pve_frags' => $pveStats['frags'] ?? 0,
                    'pve_xp' => $pveStats['xp'] ?? 0,
                    'pve_survived_battles' => $pveStats['survived_battles'] ?? 0,
                    'pvp_battles' => $pvpStats['battles'] ?? 0,
                    'pvp_wins' => $pvpStats['wins'] ?? 0,
                    'pvp_frags' => $pvpStats['frags'] ?? 0,
                    'pvp_xp' => $pvpStats['xp'] ?? 0,
                    'pvp_survived_battles' => $pvpStats['survived_battles'] ?? 0,
                    'club_battles' => $clubStats['battles'] ?? 0,
                    'club_wins' => $clubStats['wins'] ?? 0,
                    'club_frags' => $clubStats['frags'] ?? 0,
                    'club_xp' => $clubStats['xp'] ?? 0,
                    'club_survived_battles' => $clubStats['survived_battles'] ?? 0,
                    'rank_battles' => $rankStats['battles'] ?? 0,
                    'rank_wins' => $rankStats['wins'] ?? 0,
                    'rank_frags' => $rankStats['frags'] ?? 0,
                    'rank_xp' => $rankStats['xp'] ?? 0,
                    'rank_survived_battles' => $rankStats['survived_battles'] ?? 0,
                    'updated_at' => now()->toDateTimeString(),
                ];

                $processedShipsCount++;
            }

            if ($processedShipsCount > 0) {
                // Batch update using upsert operation
                DB::transaction(function () use ($batchData, $playerId) {
                    // Using upsert to insert or update records in batch
                    PlayerShip::upsert(
                        $batchData,
                        ['account_id', 'ship_id'], // Unique key combination
                        [ // Fields to update if record exists
                            'player_name',
                            'battles_played',
                            'last_battle_time',
                            'wins_count',
                            'damage_dealt',
                            'average_damage',
                            'frags',
                            'survival_rate',
                            'xp',
                            'ship_name',
                            'ship_type',
                            'ship_tier',
                            'ship_nation',
                            'distance',
                            'wn8',
                            'pr',
                            'capture',
                            'defend',
                            'spotted',
                            'pve_battles',
                            'pve_wins',
                            'pve_frags',
                            'pve_xp',
                            'pve_survived_battles',
                            'pvp_battles',
                            'pvp_wins',
                            'pvp_frags',
                            'pvp_xp',
                            'pvp_survived_battles',
                            'club_battles',
                            'club_wins',
                            'club_frags',
                            'club_xp',
                            'club_survived_battles',
                            'rank_battles',
                            'rank_wins',
                            'rank_frags',
                            'rank_xp',
                            'rank_survived_battles',
                            'updated_at'
                        ]
                    );
                });

                // Calculate and update player totals ONCE after all ships are processed
                $finalTotalWN8 = $this->totalPlayerWN8($playerId);
                $finalTotalPR = $this->totalPlayerPR($playerId);

                // Batch update all ships with player totals
                DB::table('player_ships')
                    ->where('account_id', $playerId)
                    ->update([
                        'total_player_wn8' => $finalTotalWN8,
                        'total_player_pr' => $finalTotalPR
                    ]);

                Log::info("Successfully processed {$processedShipsCount} ships for player in batch", [
                    'player_id' => $playerId,
                    'player_name' => $playerName,
                    'total_wn8' => $finalTotalWN8,
                    'total_pr' => $finalTotalPR
                ]);
            }

            return true;
        } catch (\Exception $e) {
            Log::error("Error storing player ship data", [
                'player_id' => $playerId,
                'player_name' => $playerName,
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            return false;
        }
    }
    //get stats for each player, based on a period: 24, 7, 30, overall

    public function cachePlayerStats()
    {
        $playerIds = PlayerShip::pluck('account_id')->unique()->all();
        foreach ($playerIds as $account_id) {
            $this->getPlayerStatsLastDay($account_id);
            $this->getPlayerStatsLastWeek($account_id);
            $this->getPlayerStatsLastMonth($account_id);
        }
    }
    public function getPlayerStatsLastDay($account_id)
    {

        $cacheKey = "stats_24h_{$account_id}";
        if (Cache::has($cacheKey)) {
            Log::info("Cache hit for key: {$cacheKey}");
        } else {
            Log::info("Cache miss for key: {$cacheKey}. Computing and caching value.");
        }

        return Cache::remember("stats_24h_{$account_id}", now()->addDay(), function () use ($account_id) {
            Log::info("Cache value for key:", ["key" => "stats_24h_{$account_id}"]);
            $threshold = now()->subDay()->timestamp; // Convert to Unix timestamp
            $playerStatistics = PlayerShip::select(
                DB::raw('SUM(battles_played) as battles'),
                DB::raw('CASE WHEN SUM(battles_played) > 0 THEN ROUND((SUM(wins_count)/SUM(battles_played))*100,0) ELSE 0 END as wins'),
                DB::raw('CASE WHEN SUM(battles_played) > 0 THEN ROUND(SUM(ship_tier * battles_played)/SUM(battles_played),1) ELSE 0 END as tier'),
                DB::raw('AVG(survival_rate) as survived'),
                DB::raw('CASE WHEN SUM(battles_played) > 0 THEN CEIL(SUM(damage_dealt) / SUM(battles_played)) ELSE 0 END as damage'),
                DB::raw('CASE WHEN SUM(battles_played) > 0 THEN CEIL(SUM(frags) / SUM(battles_played)) ELSE 0 END as frags'),
                DB::raw('CASE WHEN SUM(battles_played) > 0 THEN CEIL(SUM(xp) / SUM(battles_played)) ELSE 0 END as xp'),
                DB::raw('CASE WHEN SUM(battles_played) > 0 THEN CEIL(SUM(spotted) / SUM(battles_played)) ELSE 0 END as spotted'),
                DB::raw('CASE WHEN SUM(battles_played) > 0 THEN CEIL(SUM(capture) / SUM(battles_played)) ELSE 0 END as capture'),
                DB::raw('CASE WHEN SUM(battles_played) > 0 THEN CEIL(SUM(defend) / SUM(battles_played)) ELSE 0 END as defend'),
                DB::raw('MAX(total_player_wn8) as wn8'),
                DB::raw('MAX(total_player_pr) as pr')
            )
                ->where('account_id', $account_id)
                ->where('last_battle_time', '>=', $threshold)
                ->first();

            return $playerStatistics ? $playerStatistics->toArray() : [
                'battles'  => '-',
                'wins'     => '-',
                'tier'     => '-',
                'survived' => '-',
                'damage'   => '-',
                'frags'    => '-',
                'xp'       => '-',
                'spotted'  => '-',
                'capture'  => '-',
                'defend'   => '-',
                'wn8'      => '-',
                'pr'       => '-'
            ];
        });
    }

    public function getPlayerStatsLastWeek($account_id)
    {
        return Cache::remember("stats_7d_{$account_id}", now()->addWeek(), function () use ($account_id) {
            $threshold = now()->subWeek()->timestamp;
            $playerStatistics = PlayerShip::select(
                DB::raw('SUM(battles_played) as battles'),
                DB::raw('CASE WHEN SUM(battles_played) > 0 THEN ROUND((SUM(wins_count)/SUM(battles_played))*100,0) ELSE 0 END as wins'),
                DB::raw('CASE WHEN SUM(battles_played) > 0 THEN ROUND(SUM(ship_tier * battles_played)/SUM(battles_played),1) ELSE 0 END as tier'),
                DB::raw('AVG(survival_rate) as survived'),
                DB::raw('CASE WHEN SUM(battles_played) > 0 THEN CEIL(SUM(damage_dealt) / SUM(battles_played)) ELSE 0 END as damage'),
                DB::raw('CASE WHEN SUM(battles_played) > 0 THEN CEIL(SUM(frags) / SUM(battles_played)) ELSE 0 END as frags'),
                DB::raw('CASE WHEN SUM(battles_played) > 0 THEN CEIL(SUM(xp) / SUM(battles_played)) ELSE 0 END as xp'),
                DB::raw('CASE WHEN SUM(battles_played) > 0 THEN CEIL(SUM(spotted) / SUM(battles_played)) ELSE 0 END as spotted'),
                DB::raw('CASE WHEN SUM(battles_played) > 0 THEN CEIL(SUM(capture) / SUM(battles_played)) ELSE 0 END as capture'),
                DB::raw('CASE WHEN SUM(battles_played) > 0 THEN CEIL(SUM(defend) / SUM(battles_played)) ELSE 0 END as defend'),
                DB::raw('MAX(total_player_wn8) as wn8'),
                DB::raw('MAX(total_player_pr) as pr')
            )
                ->where('account_id', $account_id)
                ->where('last_battle_time', '>=', $threshold)
                ->first();

            return $playerStatistics ? $playerStatistics->toArray() : [
                'battles'  => '-',
                'wins'     => '-',
                'tier'     => '-',
                'survived' => '-',
                'damage'   => '-',
                'frags'    => '-',
                'xp'       => '-',
                'spotted'  => '-',
                'capture'  => '-',
                'defend'   => '-',
                'wn8'      => '-',
                'pr'       => '-'
            ];
        });
    }

    public function getPlayerStatsLastMonth($account_id)
    {
        return Cache::remember("stats_30d_{$account_id}", now()->addMonth(), function () use ($account_id) {
            $threshold = now()->subMonth()->timestamp;
            $playerStatistics = PlayerShip::select(
                DB::raw('SUM(battles_played) as battles'),
                DB::raw('CASE WHEN SUM(battles_played) > 0 THEN ROUND((SUM(wins_count)/SUM(battles_played))*100,0) ELSE 0 END as wins'),
                DB::raw('CASE WHEN SUM(battles_played) > 0 THEN ROUND(SUM(ship_tier * battles_played)/SUM(battles_played),1) ELSE 0 END as tier'),
                DB::raw('AVG(survival_rate) as survived'),
                DB::raw('CASE WHEN SUM(battles_played) > 0 THEN CEIL(SUM(damage_dealt) / SUM(battles_played)) ELSE 0 END as damage'),
                DB::raw('CASE WHEN SUM(battles_played) > 0 THEN CEIL(SUM(frags) / SUM(battles_played)) ELSE 0 END as frags'),
                DB::raw('CASE WHEN SUM(battles_played) > 0 THEN CEIL(SUM(xp) / SUM(battles_played)) ELSE 0 END as xp'),
                DB::raw('CASE WHEN SUM(battles_played) > 0 THEN CEIL(SUM(spotted) / SUM(battles_played)) ELSE 0 END as spotted'),
                DB::raw('CASE WHEN SUM(battles_played) > 0 THEN CEIL(SUM(capture) / SUM(battles_played)) ELSE 0 END as capture'),
                DB::raw('CASE WHEN SUM(battles_played) > 0 THEN CEIL(SUM(defend) / SUM(battles_played)) ELSE 0 END as defend'),
                DB::raw('MAX(total_player_wn8) as wn8'),
                DB::raw('MAX(total_player_pr) as pr')
            )
                ->where('account_id', $account_id)
                ->where('last_battle_time', '>=', $threshold)
                ->first();

            return $playerStatistics ? $playerStatistics->toArray() : [
                'battles'  => '-',
                'wins'     => '-',
                'tier'     => '-',
                'survived' => '-',
                'damage'   => '-',
                'frags'    => '-',
                'xp'       => '-',
                'spotted'  => '-',
                'capture'  => '-',
                'defend'   => '-',
                'wn8'      => '-',
                'pr'       => '-'
            ];
        });
    }


    public function getPlayerStatsOverall($name, $account_id)
    {
        $stats = DB::table('player_ships')
            ->where('account_id', $account_id)
            ->select(
                DB::raw('COUNT(*) as ship_count'),
                DB::raw('SUM(CASE WHEN ship_name IS NULL THEN 1 ELSE 0 END) as null_name_count'),
                DB::raw('SUM(CASE WHEN battles_overall IS NULL THEN 1 ELSE 0 END) as null_overall_count')
            )
            ->first();

        // Decide what actions to take based on data state
        if (!$stats || $stats->ship_count === 0) {
            // No records at all - fetch everything
            Log::info("Player not found in database, fetching all stats", ['name' => $name, 'account_id' => $account_id]);
            $this->fetchSinglePlayerStats($name, $account_id);
        } else if ($stats->null_name_count > 0) {
            // Has ships but some have null names - fetch ship details
            Log::info("Player has ships with null names, fetching ship details", ['name' => $name, 'account_id' => $account_id]);
            $this->fetchSinglePlayerStats($name, $account_id);
        } else if ($stats->null_overall_count > 0) {
            // Has complete ship data but missing overall stats
            Log::info("Player missing overall stats, fetching those only", ['name' => $name, 'account_id' => $account_id]);
            $this->fetchOverallStatsForSinglePlayer($account_id);
        } else {
            // Complete data - no action needed
            Log::info("Player has complete data - loading from database", ['name' => $name, 'account_id' => $account_id]);
        }



        $playerStatistics = PlayerShip::select(
            DB::raw('MAX(battles_overall) as battles'),
            DB::raw('CASE WHEN SUM(battles_overall) > 0 THEN ROUND((SUM(wins_count_overall)/SUM(battles_overall))*100, 2) ELSE 0 END as wins'),
            DB::raw('CASE WHEN SUM(battles_played) > 0 THEN ROUND(SUM(ship_tier * battles_played)/SUM(battles_played),1) ELSE 0 END as tier'),
            DB::raw('CASE WHEN SUM(battles_overall) > 0 THEN ROUND((SUM(survived_overall)/SUM(battles_overall))*100, 2) ELSE 0 END as survived'),
            DB::raw('CASE WHEN SUM(battles_overall) > 0 THEN CEIL(SUM(damage_overall)/SUM(battles_overall)) ELSE 0 END as damage'),
            DB::raw('CASE WHEN SUM(battles_overall) > 0 THEN ROUND(SUM(frags_overall)/SUM(battles_overall), 2) ELSE 0 END as frags'),
            DB::raw('CASE WHEN SUM(battles_overall) > 0 THEN CEIL(SUM(xp_overall)/SUM(battles_overall)) ELSE 0 END as xp'),
            DB::raw('CASE WHEN SUM(battles_overall) > 0 THEN ROUND(SUM(spotted_overall)/SUM(battles_overall), 2) ELSE 0 END as spotted'),
            DB::raw('CASE WHEN SUM(battles_overall) > 0 THEN ROUND(SUM(captured_overall)/SUM(battles_overall), 2) ELSE 0 END as capture'),
            DB::raw('CASE WHEN SUM(battles_overall) > 0 THEN ROUND(SUM(defended_overall)/SUM(battles_overall), 2) ELSE 0 END as defend'),
            DB::raw('MAX(total_player_wn8) as wn8'),
            DB::raw('MAX(total_player_pr) as pr')
        )
            ->where('account_id', $account_id)
            ->groupBy('account_id')
            ->first();
        Log::info($playerStatistics);

        return $playerStatistics ? $playerStatistics->toArray() : [
            'battles'  => '-',
            'wins'     => '-',
            'tier'     => '-',
            'survived' => '-',
            'damage'   => '-',
            'frags'    => '-',
            'xp'       => '-',
            'spotted'  => '-',
            'capture'  => '-',
            'defend'   => '-',
            'wn8'      => '-',
            'pr'       => '-'
        ];
    }

    public function getPlayerVehicleData($account_id, $name)
    {
        $playerVehicles = PlayerShip::select(
            'ship_id',  // Add ship_id for grouping
            'ship_name as name',
            'ship_nation as nation',
            'ship_type as type',
            'ship_tier as tier',
            'battles_played as battles',
            DB::raw('CASE WHEN battles_played > 0 THEN ROUND((frags / battles_played), 2) ELSE 0 END as frags'),
            'average_damage as damage',  // plain value from column
            DB::raw('CASE WHEN battles_played > 0 THEN ROUND((wins_count / battles_played) * 100, 2) ELSE 0 END as wins'),
            DB::raw('CASE WHEN battles_played > 0 THEN CEIL(pvp_xp / battles_played) ELSE 0 END as xp'),
            'wn8 as wn8',
            'last_battle_time'  // Add this for sorting by most recent
        )
            ->where('account_id', $account_id)
            ->where('player_name', $name)
            ->where('battles_played', '>', 0)
            ->orderBy('last_battle_time', 'desc')  // Order by latest battle time first
            ->get()
            ->unique('ship_id')  // Filter out duplicates by ship_id
            ->sortByDesc('battles')  // Now sort by battles as before
            ->map(function ($vehicle) {
                return [
                    'name' => $vehicle->name,
                    'nation' => $vehicle->nation,
                    'type' => $vehicle->type,
                    'tier' => $vehicle->tier,
                    'battles' => $vehicle->battles,
                    'frags' => $vehicle->frags,
                    'damage' => $vehicle->damage,
                    'xp' => $vehicle->xp,
                    'wins' => $vehicle->wins,
                    'wn8' => $vehicle->wn8,
                ];
            })
            ->values()  // Re-index the array after filtering
            ->toArray();
        if (!$playerVehicles) {
            Log::warning("Player vehicle info not found", ['account_id' => $account_id, 'name' => $name]);
            return [];
        }

        Log::info("Fetched vehicle for player $account_id", ['player vehicle data: ' => $playerVehicles]);

        return $playerVehicles;
    }


    public function cleanUpPlayerData($account_id)
    {
        // Clear all database records
        PlayerShip::where('account_id', $account_id)->delete();

        // Clear all caches
        Cache::forget("stats_24h_{$account_id}");
        Cache::forget("stats_7d_{$account_id}");
        Cache::forget("stats_30d_{$account_id}");

        Log::info("Cleaned up all data for player", ['account_id' => $account_id]);
        return true;
    }

    public function fetchSinglePlayerStats($name, $accountId)
    {

        $this->cleanUpPlayerData($accountId);

        // Load expected values first - this is critical for WN8 calculations
        try {
            $this->loadExpectedValues();
        } catch (\Exception $e) {
            Log::error("Failed to load expected values", [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            throw new \Exception("Failed to initialize: " . $e->getMessage());
        }

        Log::info("Fetching stats for player", ['name' => $name, 'account_id' => $accountId]);

        foreach ($this->baseUrls as $serverKey => $baseUrl) {
            $url = $baseUrl . "/wows/ships/stats/";

            $response = Http::get($url, [
                'application_id' => $this->apiKey,
                'account_id' => $accountId,
                'extra' => 'pvp_solo,pvp_div2,pvp_div3'
            ]);

            if ($response->successful()) {
                $data = $response->json();
                $playerName = $name;

                if (isset($data['data'][$accountId])) {
                    foreach ($data['data'][$accountId] as $shipStats) {
                        // Find the ship using ship_id from the API
                        $ship = WikiVehicles::where('ship_id', $shipStats['ship_id'])->first();
                        if (!$ship) {
                            Log::warning("Ship not found in database", [
                                'api_ship_id' => $shipStats['ship_id'],
                                'player_id' => $accountId
                            ]);
                            continue;
                        }

                        // Extract stats from ships table 
                        $shipName = $ship->name ?? 'Unknown ship name';
                        $shipType = $ship->type ?? 'Unknown ship type';
                        $shipTier = $ship->tier ?? 'Unknown ship tier';
                        $shipNation = $ship->nation ?? 'Unknown nation';

                        // Initialize all battle type stats
                        $pvpStats = [];
                        $pveStats = []; //not actually used
                        $clubStats = []; //not acutally used
                        $rankStats = []; //not acutally used

                        if (isset($shipStats['pvp'])) {
                            $pvpStats = $this->extractBattleStats($shipStats, 'pvp');
                        }

                        if (isset($shipStats['pve'])) {
                            $pveStats = $this->extractBattleStats($shipStats, 'pve');
                        }

                        if (isset($shipStats['club'])) {
                            $clubStats = $this->extractBattleStats($shipStats, 'club');
                        }

                        if (isset($shipStats['rank_solo'])) {
                            $rankStats = $this->extractBattleStats($shipStats, 'rank_solo');
                        }

                        // Calculate total battles
                        $totalBattles = ($pvpStats['battles'] ?? 0);
                        $totalDamageDealt = ($pvpStats['damage_dealt'] ?? 0);
                        $averageDamage = $totalBattles > 0 ? $totalDamageDealt / $totalBattles : 0;
                        $totalWins = ($pvpStats['wins'] ?? 0);
                        $totalFrags = ($pvpStats['frags'] ?? 0);
                        $totalXp = ($pvpStats['xp'] ?? 0);
                        $totalCapture = ($pvpStats['capture_points'] ?? 0);
                        $totalDefend = ($pvpStats['dropped_capture_points'] ?? 0);
                        $totalSpotted = ($pvpStats['ships_spotted'] ?? 0);

                        // Calculate survival rate
                        $totalSurvivedBattles = ($pvpStats['survived_battles'] ?? 0) +
                            ($pveStats['survived_battles'] ?? 0) +
                            ($clubStats['survived_battles'] ?? 0) +
                            ($rankStats['survived_battles'] ?? 0);

                        $survivalRate = $totalBattles > 0 ? ($totalSurvivedBattles / $totalBattles) * 100 : 0;

                        // Calculate WN8 and PR
                        $wn8 = $this->calculateWN8($ship, $totalBattles, $totalFrags, $totalWins, $totalDamageDealt);
                        $pr = $this->calculatePR($ship, $totalBattles, $totalFrags, $totalWins, $totalDamageDealt);
                        $pr = $pr !== null ? $pr : 0;

                        PlayerShip::updateOrCreate(
                            [
                                'account_id' => $accountId,
                                'ship_id' => $shipStats['ship_id']
                            ],
                            [
                                'player_name' => $playerName,
                                'battles_played' => $totalBattles,
                                'last_battle_time' => $shipStats['last_battle_time'],
                                'wins_count' => $totalWins,
                                'damage_dealt' => $totalDamageDealt,
                                'average_damage' => $averageDamage,
                                'frags' => $totalFrags,
                                'survival_rate' => $survivalRate,
                                'xp' => $totalXp,
                                'ship_name' => $shipName,
                                'ship_type' => $shipType,
                                'ship_tier' => $shipTier,
                                'ship_nation' => $shipNation,
                                'distance' => $shipStats['distance'] ?? 0,
                                'wn8' => $wn8,
                                'pr' => $pr,
                                'capture' => $totalCapture,
                                'defend' => $totalDefend,
                                'spotted' => $totalSpotted,
                                // PVE stats
                                'pve_battles' => $pveStats['battles'] ?? 0,
                                'pve_wins' => $pveStats['wins'] ?? 0,
                                'pve_frags' => $pveStats['frags'] ?? 0,
                                'pve_xp' => $pveStats['xp'] ?? 0,
                                'pve_survived_battles' => $pveStats['survived_battles'] ?? 0,
                                // PVP stats
                                'pvp_battles' => $pvpStats['battles'] ?? 0,
                                'pvp_wins' => $pvpStats['wins'] ?? 0,
                                'pvp_frags' => $pvpStats['frags'] ?? 0,
                                'pvp_xp' => $pvpStats['xp'] ?? 0,
                                'pvp_survived_battles' => $pvpStats['survived_battles'] ?? 0,
                                // Club stats
                                'club_battles' => $clubStats['battles'] ?? 0,
                                'club_wins' => $clubStats['wins'] ?? 0,
                                'club_frags' => $clubStats['frags'] ?? 0,
                                'club_xp' => $clubStats['xp'] ?? 0,
                                'club_survived_battles' => $clubStats['survived_battles'] ?? 0,
                                //Rank stats
                                'rank_battles' => $rankStats['battles'] ?? 0,
                                'rank_wins' => $rankStats['wins'] ?? 0,
                                'rank_frags' => $rankStats['frags'] ?? 0,
                                'rank_xp' => $rankStats['xp'] ?? 0,
                                'rank_survived_battles' => $rankStats['survived_battles'] ?? 0,
                            ]
                        );

                        Log::info("Successfully created ship record for player", [
                            'player_id' => $accountId,
                            'ship_id' => $shipStats['ship_id']
                        ]);
                    } // End foreach ship

                    // AFTER all processing is done, calculate the final values ONCE
                    $finalTotalWN8 = $this->totalPlayerWN8($accountId);
                    $finalTotalPR = $this->totalPlayerPR($accountId);

                    // Update ALL ships for this player with consistent values
                    DB::table('player_ships')
                        ->where('account_id', $accountId)
                        ->update([
                            'total_player_wn8' => $finalTotalWN8,
                            'total_player_pr' => $finalTotalPR
                        ]);

                    // Also fetch the overall stats for this player
                    $this->fetchOverallStatsForSinglePlayer($accountId);

                    return true; // Successfully processed player
                }
            } else {
                Log::error("Failed fetching specific player's data: ", [
                    'account_id' => $accountId,
                    'status' => $response->status(),
                    'response' => $response->body()
                ]);
            }
        } // End foreach server

        return false; // Player not found on any server
    }

    // New helper method to fetch overall stats for a single player
    public function fetchOverallStatsForSinglePlayer($accountId)
    {
        foreach ($this->baseUrls as $serverKey => $baseUrl) {
            $overallUrl = $baseUrl . "/wows/account/info/";

            $response = Http::get($overallUrl, [
                'application_id' => $this->apiKey,
                'account_id' => $accountId,
            ]);

            if ($response->successful()) {
                $data = $response->json();

                if (isset($data['data'][$accountId]['statistics']['pvp'])) {
                    $pvp = $data['data'][$accountId]['statistics']['pvp'];

                    DB::table('player_ships')
                        ->where('account_id', $accountId)
                        ->update([
                            'battles_overall'  => $pvp['battles'] ?? 0,
                            'frags_overall' => $pvp['frags'] ?? 0,
                            'survived_overall' => $pvp['survived_battles'] ?? 0,
                            'wins_count_overall' => $pvp['wins'] ?? 0,
                            'damage_overall'   => $pvp['damage_dealt'] ?? 0,
                            'defended_overall' => $pvp['dropped_capture_points'] ?? 0,
                            'captured_overall' => $pvp['capture_points'] ?? 0,
                            'xp_overall'       => $pvp['xp'] ?? 0,
                            'spotted_overall'  => $pvp['ships_spotted'] ?? 0,
                        ]);

                    Log::info("Updated overall stats for player", ['account_id' => $accountId]);
                    return true;
                }
            }
        }

        return false;
    }


    public function getAccountIdByUsername($username)
    {
        Log::info("Looking up account ID for username", ['username' => $username]);

        // First try to find the account_id in our database
        $playerShip = PlayerShip::where('player_name', $username)->first();

        if ($playerShip) {
            Log::info("Found player in database", ['username' => $username, 'account_id' => $playerShip->account_id]);
            return [
                'success' => true,
                'account_id' => $playerShip->account_id,
                'username' => $username
            ];
        }

        // If not found in database, search via the API on all servers
        foreach ($this->baseUrls as $serverKey => $baseUrl) {
            $url = $baseUrl . "/wows/account/list/";

            $response = Http::get($url, [
                'application_id' => $this->apiKey,
                'search' => $username,
                'limit' => 1
            ]);

            if ($response->successful()) {
                $data = $response->json();

                if (isset($data['data']) && count($data['data']) > 0) {
                    $accountData = $data['data'][0];
                    $accountId = $accountData['account_id'];
                    $accountName = $accountData['nickname'];

                    Log::info("Found player via API", [
                        'username' => $username,
                        'account_id' => $accountId,
                        'server' => $serverKey
                    ]);

                    return [
                        'success' => true,
                        'account_id' => $accountId,
                        'username' => $accountName,
                        'server' => $serverKey
                    ];
                }
            }
        }

        // Player not found on any server
        Log::warning("Player not found on any server", ['username' => $username]);
        return [
            'success' => false,
            'message' => "Player not found"
        ];
    }

    public function updatePlayerStats($username)
    {
        // First get the account ID
        $result = $this->getAccountIdByUsername($username);

        if (!$result['success']) {
            return $result; // Return the error if player not found
        }

        $accountId = $result['account_id'];
        $name = $result['username'];

        Log::info("Force updating stats for player", ['username' => $name, 'account_id' => $accountId]);

        // Update both ship stats and overall stats
        $shipsUpdated = $this->fetchSinglePlayerStats($name, $accountId);



        return [
            'success' => $shipsUpdated,
            'account_id' => $accountId,
            'username' => $name,
            'message' => $shipsUpdated ? 'Your stats have been updated succesfully, please navigate to your stat page by search to check them out!' : 'Failed to update player stats'
        ];
    }


    public function cleanupDuplicatePlayerShips()
    {
        $duplicatesFound = 0;
        $duplicatesDeleted = 0;

        try {
            // First, identify duplicate records by account_id and ship_id
            $duplicates = DB::table('player_ships')
                ->select('account_id', 'ship_id', DB::raw('COUNT(*) as count'))
                ->groupBy('account_id', 'ship_id')
                ->having('count', '>', 1)
                ->get();

            $duplicatesFound = $duplicates->count();

            Log::info("Found $duplicatesFound sets of duplicate player ships");

            // For each set of duplicates, delete all but the newest record
            foreach ($duplicates as $duplicate) {
                // Get all the duplicate records
                $records = PlayerShip::where('account_id', $duplicate->account_id)
                    ->where('ship_id', $duplicate->ship_id)
                    ->orderBy('updated_at', 'desc') // Sort by most recent first
                    ->get();

                // Keep the first record (newest) and delete the rest
                $keepRecord = $records->shift(); // Remove and get the first item

                // Delete all remaining records
                foreach ($records as $record) {
                    $record->delete();
                    $duplicatesDeleted++;
                }

                Log::info("Kept newest record for player {$duplicate->account_id}, ship {$duplicate->ship_id}, deleted " . count($records) . " duplicates");
            }

            return [
                'success' => true,
                'duplicates_found' => $duplicatesFound,
                'duplicates_deleted' => $duplicatesDeleted,
                'message' => "Successfully cleaned up player ships: found $duplicatesFound sets of duplicates, deleted $duplicatesDeleted duplicate records"
            ];
        } catch (\Exception $e) {
            Log::error("Error cleaning up duplicate player ships", [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return [
                'success' => false,
                'message' => "Error cleaning up duplicate ships: " . $e->getMessage()
            ];
        }
    }
}
