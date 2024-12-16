<?php

namespace App\Services;

use App\Models\Player;
use App\Models\PlayerShip;
use App\Models\Ship;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;


class PlayerShipService
{
    protected $apiKey;
    protected $apiUrl = "https://api.worldofwarships.eu/wows/ships/stats/";

    protected $expectedValues;
    public function __construct()
    {
        $this->apiKey = config('services.wargaming.api_key');
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
        $wn8 = (700 * $nDmg) + (300 * $nFrags) + (150 * $nWins);



        Log::info("Intermediate WN8 values", [
            'ship_id' => $shipId,
            'rDmg' => $rDmg,
            'rFrags' => $rFrags,
            'rWins' => $rWins,
            'nDmg' => $nDmg,
            'nFrags' => $nFrags,
            'nWins' => $nWins,
            'WN8' => $wn8,
        ]);

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

        Log::info("Total player wn8", [
            'account_id' => $playerId,
            'total_player_wn8' => $player_total_wn8,
            'total_battles' => $total_battles
        ]);

        return $player_total_wn8;
    }


    private function determineCategoryWN8($wn8)
    {
        //simple if statement, if "value" eq "num" then return "x->value"
        if ($wn8 == null) {
            return 'Null';
        }

        if ($wn8 < 750) {
            return 'Bad';
        } elseif ($wn8 >= 750 && $wn8 < 1100) {
            return 'Below Average';
        } elseif ($wn8 >= 1100 && $wn8 < 1350) {
            return 'Average';
        } elseif ($wn8 >= 1350 && $wn8 < 1550) {
            return 'Good';
        } elseif ($wn8 >= 1550 && $wn8 < 1750) {
            return 'Very Good';
        } elseif ($wn8 >= 1750 && $wn8 < 2100) {
            return 'Great';
        } elseif ($wn8 >= 2100 && $wn8 < 2450) {
            return 'Unicum';
        } elseif ($wn8 >= 2450 && $wn8 < 9999) {
            return 'Super Unicum';
        }
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
        ];
    }



    /*  public function getPeriodCriteria()
    {
        return [
            'last24hours' => [
                'date' => Carbon::now()->subHours(24),
                'min_battles' => 5,
                'min_tier' => 2,
                'limit' => 10,
            ],
            'last7days' => [
                'date' => Carbon::now()->subDays(7),
                'min_battles' => 35,
                'min_tier' => 2,
                'limit' => 10,
            ],
            'lastMonth' => [
                'date' => Carbon::now()->subDays(25),
                'min_battles' => 120,
                'min_tier' => 2,
                'limit' => 10,
            ],
            'overall' => [
                'date' => null, // No date restriction for overall stats
                'min_battles' => 5,
                'min_tier' => 1,
                'limit' => 10,
            ],
        ];
    }



    public function getPlayerStatsByPeriod($playerId, $period)
    {

        $dateRanges = [
            'last24hours' => now()->subHours(24),
            'last7days' => now()->subDays(7),
            'lastMonth' => now()->subDays(25),
            'overall' => null,
        ];



        if (!isset($dateRanges[$period])) {
            throw new \InvalidArgumentException("Invalid period: $period");
        }

        $query = PlayerShip::where('account_id', $playerId);

        if ($period !== 'overall') {
            $query->where('updated_at', '>=', $dateRanges[$period]);
        }

        $stats = $query->get()->reduce(
            function ($carry, $playerShip) {
                $carry['battles'] += $playerShip->battles_played;
                $carry['damage'] += $playerShip->damage_dealt;
                $carry['wins'] += $playerShip->wins;
                $carry['frags'] += $playerShip->frags;
                $carry['wn8_player'] += $playerShip->total_player_wn8;
                $carry['ship_name'] = $playerShip->ship_name;
                $carry['sbip_wn8'] += $playerShip->wn8;
                $carry['average_tier'] += PlayerShip::avg($playerShip->ship_tier);
                $carry['win_rate'] += $playerShip->battles_played / $playerShip->wins_count;
            },
            [
                'battles' => 0,
                'damage' => 0,
                'wins' => 0,
                'frags' => 0,
                'wn8_player' => 0,
                'ship_name' => 0,
                'sbip_wn8' => 0,
                'win_rate' => 0,

            ]
        );

        return $stats;
    }

 */


    public function getTopPlayersLast24Hours()
    {
        $last24Hours = now()->subDays(1);

        return PlayerShip::select('account_id', DB::raw('MAX(player_name) as player_name'), DB::raw('MAX(total_player_wn8) as total_player_wn8'))
            ->where('ship_tier', '>', 5)
            ->where('battles_played', '>', 5)
            ->where('updated_at', '<=', $last24Hours)
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

    public function getTopPlayersLast7Days()
    {

        $last7days = now()->subDays(7);

        return PlayerShip::select('account_id', DB::raw('MAX(player_name) as player_name'), DB::raw('MAX(total_player_wn8) as total_player_wn8'))
            ->where('ship_tier', '>', 5)
            ->where('battles_played', '>', 30)
            ->where('updated_at', '<=', $last7days)
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

    public function getTopPlayersLastMonth()
    {

        $lastMonth = now()->subDays(25);

        return PlayerShip::select('account_id', DB::raw('MAX(player_name) as player_name'), DB::raw('MAX(total_player_wn8) as total_player_wn8'))
            ->where('ship_tier', '>', 5)
            ->where('battles_played', '>', 120)
            ->where('updated_at', '>=', $lastMonth)
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

    public function getTopPlayersOverall()
    {

        $overall = now()->subDays(29);

        return PlayerShip::select('account_id', DB::raw('MAX(player_name) as player_name'), DB::raw('MAX(total_player_wn8) as total_player_wn8'))
            ->where('ship_tier', '>', 5)
            ->where('battles_played', '>', 500)
            ->where('updated_at', '>=', $overall)
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


    /*   public function rankPlayersByPeriod($period)
    {

        $criteria = $this->getPeriodCriteria();

        if (!isset($dateRanges[$period])) {
            throw new \InvalidArgumentException("Invalid period: $period");
        }

        //store each period in a variable to use later
        $config = $criteria[$period];
        try {
            $query = PlayerShip::where('battles_played', '>=', $config['min_battles'])
                ->where('ship_tier', '>=', $config['min_tier'])
                ->orderByDesc('total_player_wn8');

            if ($config['date']) {
                $query->where('updated_at', '>=', $config['date']);
            }

            return $query->take($config['limit'])->get(['ship_name', 'account_id', 'total_player_wn8']);
        } catch (\Exception $e) {
            Log::error("Error ranking players for period: $period", [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            throw $e;
        }
    } */

    public function fetchAndStorePlayerShips()
    {
        $this->loadExpectedValues();

        Log::info('Starting fetchAndStorePlayerShips');

        try {
            $playerIds = Player::pluck('account_id')->all();
            Log::info("Data loaded", ['players_count' => count($playerIds)]);

            foreach ($playerIds as $playerId) {
                $response = Http::get($this->apiUrl, [
                    'application_id' => $this->apiKey,
                    'account_id' => $playerId,
                    'extra' => 'pve,club,pve_div2,pve_div3,pve_solo,pvp_solo,pvp_div2,pvp_div3,rank_solo,rank_div2,rank_div3'
                ]);

                if ($response->successful()) {
                    $data = $response->json();

                    $playerName = Player::where('account_id', $playerId)->value('nickname');



                    if (isset($data['data'][$playerId])) {
                        foreach ($data['data'][$playerId] as $shipStats) {
                            // Find the ship using ship_id from the API
                            $ship = Ship::where('ship_id', $shipStats['ship_id'])->first();


                            if (!$ship) {
                                Log::warning("Ship not found in database", [
                                    'api_ship_id' => $shipStats['ship_id'],
                                    'player_id' => $playerId
                                ]);
                                continue;
                            }


                            //extract stats from ships table 
                            $shipName = $ship->name;
                            $shipType = $ship->type;
                            $shipTier = $ship->tier;

                            // Extract statistics for different battle types
                            $pvpStats = [];
                            $pveStats = [];
                            $clubStats = [];
                            $rankStats = [];

                            if (isset($shipStats['pvp'])) {
                                $pvpStats = $this->extractBattleStats($shipStats, 'pvp');
                            }

                            if (isset($shipStats['pvp_div2'])) {
                                $pvp2Stats = $this->extractBattleStats($shipStats, 'pvp_div2');
                            }

                            if (isset($shipStats['pvp_div3'])) {
                                $pvp3Stats = $this->extractBattleStats($shipStats, 'pvp_div3');
                            }

                            if (isset($shipStats['pve'])) {
                                $pveStats = $this->extractBattleStats($shipStats, 'pve');
                            }

                            if (isset($shipStats['pve_solo'])) {
                                $pve_soloStats = $this->extractBattleStats($shipStats, 'pve_solo');
                            }

                            if (isset($shipStats['pve_div2'])) {
                                $pve2Stats = $this->extractBattleStats($shipStats, 'pve_div2');
                            }

                            if (isset($shipStats['pve_div3'])) {
                                $pve3Stats = $this->extractBattleStats($shipStats, 'pve_div3');
                            }

                            if (isset($shipStats['club'])) {
                                $clubStats = $this->extractBattleStats($shipStats, 'club');
                            }

                            if (isset($shipStats['rank_solo'])) {
                                $rankStats = $this->extractBattleStats($shipStats, 'rank_solo');
                            }

                            if (isset($shipStats['rank_div2'])) {
                                $rank_div2Stats = $this->extractBattleStats($shipStats, 'rank_solo');
                            }

                            if (isset($shipStats['rank_div3'])) {
                                $rank_div3Stats = $this->extractBattleStats($shipStats, 'rank_solo');
                            }



                            /*               $modes = ['pvp', 'pve', 'club', 'rank_solo', 'rank_div2', 'rank_div3', 'pve_solo', 'pve_div2', 'pve_div3', 'pvp_div2', 'pvp_div3'];
                            $totalStats = ['batles' => 0, 'wins' => 0, 'damage_dealt' => 0];

                            foreach ($modes as $mode){
                                if(isset($shipStats[mode])){
                                    $stats = $this->extractBattleStats($shipStats, $mode);
                                    $totalStats[]
                                }
                            }
 */
                            // Calculate total battles
                            $totalBattles = ($pvpStats['battles'] ?? 0) + ($pveStats['battles'] ?? 0)
                                + ($clubStats['battles'] ?? 0) + ($rankStats['battles'] ?? 0)
                                + ($rank_div2Stats['battles'] ?? 0) + ($rank_div3Stats['battles'] ?? 0)
                                + ($pve_soloStats['battles'] ?? 0) + ($pve2Stats['battles'] ?? 0)
                                + ($pve3Stats['battles'] ?? 0) + ($pvp2Stats['battles'] ?? 0)
                                + ($pvp3Stats['battles'] ?? 0);

                            // Calculate total damage
                            $totalDamageDealt = ($pvpStats['damage_dealt'] ?? 0) + ($pveStats['damage_dealt'] ?? 0)
                                + ($clubStats['damage_dealt'] ?? 0) + ($rankStats['damage_dealt'] ?? 0)
                                + ($rank_div2Stats['damage_dealt'] ?? 0) + ($rank_div3Stats['damage_dealt'] ?? 0)
                                + ($pve_soloStats['damage_dealt'] ?? 0) + ($pve2Stats['damage_dealt'] ?? 0)
                                + ($pve3Stats['damage_dealt'] ?? 0) + ($pvp2Stats['damage_dealt'] ?? 0)
                                + ($pvp3Stats['damage_dealt'] ?? 0);


                            $averageDamage = $totalBattles > 0 ? $totalDamageDealt / $totalBattles : 0;

                            //calculate total wins
                            $totalWins = ($pvpStats['wins'] ?? 0) + ($pveStats['wins'] ?? 0)
                                + ($clubStats['wins'] ?? 0) + ($rankStats['wins'] ?? 0)
                                + ($rank_div2Stats['wins'] ?? 0) + ($rank_div3Stats['wins'] ?? 0)
                                + ($pve_soloStats['wins'] ?? 0) + ($pve2Stats['wins'] ?? 0)
                                + ($pve3Stats['wins'] ?? 0) + ($pvp2Stats['wins'] ?? 0)
                                + ($pvp3Stats['wins'] ?? 0);

                            //calculate total frags
                            $totalFrags = ($pvpStats['frags'] ?? 0) + ($pveStats['frags'] ?? 0)
                                + ($clubStats['frags'] ?? 0) + ($rankStats['frags'] ?? 0)
                                + ($rank_div2Stats['frags'] ?? 0) + ($rank_div3Stats['frags'] ?? 0)
                                + ($pve_soloStats['frags'] ?? 0) + ($pve2Stats['frags'] ?? 0)
                                + ($pve3Stats['frags'] ?? 0) + ($pvp2Stats['frags'] ?? 0)
                                + ($pvp3Stats['frags'] ?? 0);


                            // Calculate survival rate
                            $totalSurvivedBattles = ($pvpStats['survived_battles'] ?? 0) + ($pveStats['survived_battles'] ?? 0) + ($clubStats['survived_battles'] ?? 0) + ($rankStats['survived_battles'] ?? 0);
                            $survivalRate = $totalBattles > 0 ? ($totalSurvivedBattles / $totalBattles) * 100 : 0;



                            Log::info("Processing ship stats", [
                                'account_id' => $playerId,
                                'ship_id' => $ship->ship_id,
                                'pvp_battles' => $pvpStats['battles'] ?? 0,
                                'pve_battles' => $pveStats['battles'] ?? 0,
                                'club_battles' => $clubStats['battles'] ?? 0,
                                'rank_battles' => $rankStats['battles'] ?? 0,
                                'total_battles' => $totalBattles,
                                'distance' => 'distance',
                            ]);

                            //wn8
                            $wn8 =  $this->calculateWN8($ship, $totalBattles, $totalFrags, $totalWins, $totalDamageDealt);

                            //total_player_wn8
                            $total_player_wn8 = $this->totalPlayerWN8($playerId);
                            //wn8 per type / category of a ship 
                            $wn8_category = $this->determineCategoryWN8($wn8);

                            Log::info("Ship WN8 by category", [
                                'ship_name' => $shipName,
                                'ship_type' => $shipType,
                                'WN8' => $wn8_category,
                            ]);
                            // Use ship->id instead of ship_id from API
                            PlayerShip::updateOrCreate(
                                [
                                    'account_id' => $playerId,
                                    'ship_id' => $shipStats['ship_id']
                                ],
                                [
                                    'player_name' => $playerName,
                                    'battles_played' => $totalBattles,
                                    'wins_count' => $totalWins,
                                    'damage_dealt' => $totalDamageDealt,
                                    'average_damage' => $averageDamage,
                                    'frags' => $totalFrags,
                                    'survival_rate' => $survivalRate,
                                    'ship_name' => $shipName,
                                    'ship_type' => $shipType,
                                    'ship_tier' => $shipTier,
                                    'distance' => $shipStats['distance'],
                                    'wn8' => $wn8,
                                    'total_player_wn8' => $total_player_wn8,
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

                        $this->totalPlayerWN8($playerId);
                    }
                } else {
                    Log::error("Failed to fetch player ships", [
                        'account_id' => $playerId,
                        'status' => $response->status(),
                        'response' => $response->body()
                    ]);
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
}
