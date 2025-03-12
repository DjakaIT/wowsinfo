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

        Cache::put('stats_24h', $stats24h, now()->addDay());
        Cache::put('stats_7d', $stats7d, now()->addWeek());
        Cache::put('stats_30d', $stats30d, now()->addMonth());
    }



    public function getTopPlayersLast24Hours()
    {

        return Cache::remember('stats_24h', now()->addDay(), function () {
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

        return Cache::remember('stats_7d', now()->addWeek(), function () {
            $last7days = now()->subDays(6);

            return PlayerShip::select('account_id', DB::raw('MAX(player_name) as player_name'), DB::raw('MAX(total_player_wn8) as total_player_wn8'))
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
        });
    }

    public function getTopPlayersLastMonth()
    {

        return Cache::remember('stats_30d', now()->addMonth(), function () {
            $lastMonth = now()->subDays(25);

            return PlayerShip::select('account_id', DB::raw('MAX(player_name) as player_name'), DB::raw('MAX(total_player_wn8) as total_player_wn8'))
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
        });
    }

    public function getTopPlayersOverall()
    {

        $overall = now()->subDays(29);

        return PlayerShip::select('account_id', DB::raw('MAX(player_name) as player_name'), DB::raw('MAX(total_player_wn8) as total_player_wn8'))
            ->where('ship_tier', '>', 5)
            ->where('battles_overall', '>', 400)
            ->where('last_battle_time', '>=', $overall)
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
        // Get global list of player IDs (from clan_members in this example)
        $playerIds = ClanMember::pluck('account_id')->unique()->all();
        if (empty($playerIds)) {
            Log::info("No player ids found for overall stats update.");
            return false;
        }

        $batchSize = 100; // API allows up to 100 accounts per request

        // Loop through each server so that no server is skipped
        foreach ($this->baseUrls as $serverKey => $baseUrl) {
            $overallUrl = $baseUrl . "/wows/account/info/";
            foreach (array_chunk($playerIds, $batchSize) as $batch) {
                $idsString = implode(',', $batch);
                $response = Http::get($overallUrl, [
                    'application_id' => $this->apiKey,
                    'account_id' => $idsString,
                ]);

                if (!$response->successful()) {
                    Log::error("Overall stats API request failed", [
                        'server' => strtoupper($serverKey),
                        'account_ids' => $idsString,
                        'status' => $response->status(),
                        'response' => $response->body()
                    ]);
                    continue;
                }

                $data = $response->json();
                if (!isset($data['data']) || !is_array($data['data'])) {
                    Log::warning("Overall stats API returned no data", ['response' => $data]);
                    continue;
                }

                // Build updates from the response
                $updates = [];
                foreach ($data['data'] as $accountId => $accountData) {
                    if (!isset($accountData['statistics']['pvp'])) {
                        Log::warning("Overall stats for account $accountId not in expected format", [
                            'server' => strtoupper($serverKey),
                            'data' => $accountData
                        ]);
                        continue;
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
                        'frags_overall' => $pvp['frags'] ?? 0,
                    ];
                }

                // Update the database for each account in this batch.
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
                    Log::info("Updated overall stats for account {$updateData['account_id']} on server " . strtoupper($serverKey));
                }
            }
        }
        return true;
    }


    public function fetchAndStorePlayerShipsBoth()
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
            $playerIds = ClanMember::pluck('account_id')->all();
            if (empty($playerIds)) {
                Log::info("No player ids found in database");
                return false;
            }

            Log::info("Data loaded", ['players_count' => count($playerIds)]);
            foreach ($this->baseUrls as $serverKey => $baseUrl) {

                $url = $baseUrl . "/wows/ships/stats/";

                foreach ($playerIds as $playerId) {


                    $response = Http::get($url, [
                        'application_id' => $this->apiKey,
                        'account_id' => $playerId,
                        'extra' => 'pvp_solo,pvp_div2,pvp_div3'
                    ]);

                    if ($response->successful()) {
                        $data = $response->json();

                        $playerName = ClanMember::where('account_id', $playerId)->value('account_name');



                        if (isset($data['data'][$playerId])) {
                            foreach ($data['data'][$playerId] as $shipStats) {
                                // Find the ship using ship_id from the API
                                $ship = WikiVehicles::where('ship_id', $shipStats['ship_id'])->first();

                                if (!$ship) {
                                    Log::warning("Ship not found in database", [
                                        'api_ship_id' => $shipStats['ship_id'],
                                        'player_id' => $playerId
                                    ]);
                                    continue;
                                }


                                //extract stats from ships table 
                                $shipName = $ship->name ?? 'Unknown ship name';
                                $shipType = $ship->type ?? 'Unknown ship type';
                                $shipTier = $ship->tier ?? 'Unknown ship tier';
                                $shipNation = $ship->nation ?? 'Unkown nation';


                                // Extract statistics for different battle types
                                $pvpStats = [];


                                if (isset($shipStats['pvp'])) {
                                    $pvpStats = $this->extractBattleStats($shipStats, 'pvp');
                                }




                                // Calculate total battles
                                $totalBattles = ($pvpStats['battles'] ?? 0);


                                // Calculate total damage
                                $totalDamageDealt = ($pvpStats['damage_dealt'] ?? 0);


                                $averageDamage = $totalBattles > 0 ? $totalDamageDealt / $totalBattles : 0;

                                //calculate total wins
                                $totalWins = ($pvpStats['wins'] ?? 0);

                                //calculate total frags
                                $totalFrags = ($pvpStats['frags'] ?? 0);


                                $totalXp = ($pvpStats['xp'] ?? 0);


                                $totalCapture = ($pvpStats['capture_points'] ?? 0);

                                $totalDefend = ($pvpStats['dropped_capture_points'] ?? 0);

                                $totalSpotted = ($pvpStats['ships_spotted'] ?? 0);

                                // Calculate survival rate
                                $totalSurvivedBattles = ($pvpStats['survived_battles'] ?? 0) + ($pveStats['survived_battles'] ?? 0) + ($clubStats['survived_battles'] ?? 0) + ($rankStats['survived_battles'] ?? 0);
                                $survivalRate = $totalBattles > 0 ? ($totalSurvivedBattles / $totalBattles) * 100 : 0;

                                //wn8
                                $wn8 =  $this->calculateWN8($ship, $totalBattles, $totalFrags, $totalWins, $totalDamageDealt);
                                //pr
                                $pr = $this->calculatePR($ship, $totalBattles, $totalFrags, $totalWins, $totalDamageDealt);
                                $pr = $pr != null ? $pr : 0;
                                Log::info("Processing ship for player", [
                                    'player_id' => $playerId,
                                    'ship_id' => $ship->ship_id,
                                    'ship_name' => $ship->name,
                                    'ship_nation' => $ship->nation,
                                    'spotted' => $totalSpotted,
                                    'capture' => $totalCapture,
                                    'defend' => $totalDefend,
                                    'xp' => $totalXp,
                                ]);

                                PlayerShip::updateOrCreate(
                                    [
                                        'account_id' => $playerId,
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
                                        'distance' => $shipStats['distance'],
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
                            }
                            Log::info("Successfully updated/created player ship record", [
                                'player_id' => $playerId,
                                'ship_id' => $shipStats['ship_id'],
                            ]);

                            $finalTotalWN8 = $this->totalPlayerWN8($playerId);
                            $finalTotalPR = $this->totalPlayerPR($playerId);

                            // Update ALL ships for this player with the consistent values
                            DB::table('player_ships')
                                ->where('account_id', $playerId)
                                ->update([
                                    'total_player_wn8' => $finalTotalWN8,
                                    'total_player_pr' => $finalTotalPR
                                ]);
                        }
                    } else {
                        Log::error("Failed to fetch player ships", [
                            'account_id' => $playerId,
                            'status' => $response->status(),
                            'response' => $response->body()
                        ]);
                    }
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
            $playerIds = ClanMember::pluck('account_id')->all();
            if (empty($playerIds)) {
                Log::info("No player ids found in database");
                return false;
            }

            Log::info("Data loaded", ['players_count' => count($playerIds)]);
            foreach ($this->baseUrls as $serverKey => $baseUrl) {

                $url = $baseUrl . "/wows/ships/stats/";

                foreach ($playerIds as $playerId) {


                    $response = Http::get($url, [
                        'application_id' => $this->apiKey,
                        'account_id' => $playerId,
                        'extra' => 'pvp_solo,pvp_div2,pvp_div3'
                    ]);

                    if ($response->successful()) {
                        $data = $response->json();

                        $playerName = ClanMember::where('account_id', $playerId)->value('account_name');
                        Log::info("Processing player", ['player_id' => $playerId, 'player_name' => $playerName]);
                        if (isset($data['data'][$playerId])) {
                            $this->StorePlayerShips($playerId, $playerName, $data['data'][$playerId]);
                        } else {
                            Log::warning("No ship data found for player", [
                                'player_id' => $playerId,
                                'player_name' => $playerName
                            ]);
                        }
                    } else {
                        Log::error("Failed to fetch player ships", [
                            'account_id' => $playerId,
                            'status' => $response->status(),
                            'response' => $response->body()
                        ]);
                    }
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

            $processedShipsCount = 0;

            foreach ($playerShipsData as $shipStats) {
                // Find the ship using ship_id from the API
                $ship = WikiVehicles::where('ship_id', $shipStats['ship_id'])->first();

                if (!$ship) {
                    Log::warning("Ship not found in database", [
                        'api_ship_id' => $shipStats['ship_id'],
                        'player_id' => $playerId
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

                PlayerShip::updateOrCreate(
                    [
                        'account_id' => $playerId,
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

                $processedShipsCount++;
            }

            if ($processedShipsCount > 0) {
                // Update the total WN8 and PR for this player
                $finalTotalWN8 = $this->totalPlayerWN8($playerId);
                $finalTotalPR = $this->totalPlayerPR($playerId);

                // Update ALL ships for this player with the consistent values
                DB::table('player_ships')
                    ->where('account_id', $playerId)
                    ->update([
                        'total_player_wn8' => $finalTotalWN8,
                        'total_player_pr' => $finalTotalPR
                    ]);

                Log::info("Successfully processed {$processedShipsCount} ships for player", [
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
            'ship_name as name',
            'ship_nation as nation',
            'ship_type as type',
            'ship_tier as tier',
            'battles_played as battles',
            DB::raw('CASE WHEN battles_played > 0 THEN ROUND((frags / battles_played), 2) ELSE 0 END as frags'),
            'average_damage as damage',  // plain value from column
            DB::raw('CASE WHEN battles_played > 0 THEN ROUND((wins_count / battles_played) * 100, 2) ELSE 0 END as wins'),
            DB::raw('CASE WHEN battles_played > 0 THEN CEIL(pvp_xp / battles_played) ELSE 0 END as xp'),
            'wn8 as wn8'
        )
            ->where('account_id', $account_id)
            ->where('player_name', $name)
            ->where('battles_played', '>', 0)
            ->orderBy('battles_played', 'desc')
            ->get()
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
            ->toArray();
        if (!$playerVehicles) {
            Log::warning("Player vehicle info not found", ['account_id' => $account_id, 'name' => $name]);
            return [];
        }

        Log::info("Fetched vehicle for player $account_id", ['player vehicle data: ' => $playerVehicles]);

        return $playerVehicles;
    }

    public function fetchSinglePlayerStats($name, $accountId)
    {
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
}
