<?php

namespace App\Services;

use App\Models\Player;
use App\Models\PlayerStatistic;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class PlayerStatisticService
{
    protected $apiUrl = 'https://api.worldofwarships.eu/wows/account/info/';
    protected $apiKey;

    public function __construct()
    {
        $this->apiKey = config('services.wargaming.api_key');
    }

    public function fetchAndStorePlayerStats()
    {
        Log::info('Starting fetchAndStorePlayerStats');

        try {
            $playerIds = Player::pluck('account_id')->all();
            Log::info('Data loaded', ['player_count' => count($playerIds)]);

            $batchSize = 100;
            for ($start = 0; $start < count($playerIds); $start += $batchSize) {
                $batchPlayerIds = array_slice($playerIds, $start, $batchSize);

                $response = Http::get($this->apiUrl, [
                    'application_id' => $this->apiKey,
                    'account_id' => implode(',', $batchPlayerIds),
                    'extra' => 'statistics.club,statistics.clan,statistics.pve,statistics.pvp_solo,statistics.rank_solo,private.port',
                ]);

                Log::info('API response for batch', ['response' => $response->json()]);


                if ($response->failed()) {
                    Log::error('API call failed for batch', ['batch_start' => $start]);
                    continue;
                }

                $data = $response->json();

                foreach ($batchPlayerIds as $playerId) {
                    if (!isset($data['data'][$playerId])) {
                        Log::warning("Player ID not found in response: $playerId");
                        continue;
                    }

                    $playerStats = $data['data'][$playerId];
                    $private = $playerStats['private'] ?? null;



                    if (is_null($private)) {
                        Log::warning("Private data is null for player ID: $playerId");
                        $privateBattleLifeTime = 0;
                        $privateGold = 0;
                        $privatePort = 0;
                    } else {
                        $privateBattleLifeTime = $private['battle_life_time'] ?? 0;
                        $privateGold = $private['gold'] ?? 0;
                        $privatePort = $private['port'] ?? 0;
                    }

                    Log::info("Processed private data for $playerId", [
                        'battle_life_time' => $privateBattleLifeTime,
                        'gold' => $privateGold,
                        'port' => $privatePort,
                    ]);

                    if (empty($playerStats)) {
                        Log::warning("No stats available for player ID: $playerId");
                        continue;
                    }

                    // Extract values
                    $nickname = $playerStats['nickname'] ?? null;

                    $private = $playerStats['private'] ?? [];
                    $privateBattleLifeTime = $private['battle_life_time'] ?? 0;
                    $privateGold = $private['gold'] ?? 0.0;
                    $privatePort = $playerStats['port'] ?? 0;
                    $statistics = $playerStats['statistics'] ?? [];

                    // Aggregate stats
                    $battlesPlayed = $statistics['battles'] ?? 0;
                    $distance = $statistics['distance'] ?? 0;
                    $damageDealtTotal = ($playerStats['statistics']['club']['damage_dealt'] ?? 0)
                        + ($playerStats['statistics']['pvp_solo']['damage_dealt'] ?? 0)
                        + ($playerStats['statistics']['pve']['damage_dealt'] ?? 0)
                        + ($playerStats['statistics']['rank_solo']['damage_dealt'] ?? 0);

                    $fragsTotal = ($playerStats['statistics']['club']['frags'] ?? 0)
                        + ($playerStats['statistics']['pvp_solo']['frags'] ?? 0)
                        + ($playerStats['statistics']['pve']['frags'] ?? 0)
                        + ($playerStats['statistics']['rank_solo']['frags'] ?? 0);

                    $xpTotal = ($playerStats['statistics']['club']['xp'] ?? 0)
                        + ($playerStats['statistics']['pvp_solo']['xp'] ?? 0)
                        + ($playerStats['statistics']['pve']['xp'] ?? 0)
                        + ($playerStats['statistics']['rank_solo']['xp'] ?? 0);

                    $winsTotal = ($playerStats['statistics']['club']['wins'] ?? 0)
                        + ($playerStats['statistics']['pvp_solo']['wins'] ?? 0)
                        + ($playerStats['statistics']['pve']['wins'] ?? 0)
                        + ($playerStats['statistics']['rank_solo']['wins'] ?? 0);

                    $lossesTotal = ($playerStats['statistics']['club']['losses'] ?? 0)
                        + ($playerStats['statistics']['pvp_solo']['losses'] ?? 0)
                        + ($playerStats['statistics']['pve']['losses'] ?? 0)
                        + ($playerStats['statistics']['rank_solo']['losses'] ?? 0);

                    $survivedBattlesTotal = ($playerStats['statistics']['club']['survived_battles'] ?? 0)
                        + ($playerStats['statistics']['pvp_solo']['survived_battles'] ?? 0)
                        + ($playerStats['statistics']['pve']['survived_battles'] ?? 0)
                        + ($playerStats['statistics']['rank_solo']['survived_battles'] ?? 0);




                    // Insert or update player statistics
                    PlayerStatistic::updateOrCreate(
                        ['account_id' => $playerId],
                        [
                            'nickname' => $nickname,
                            'battles_played' => $battlesPlayed,
                            'wins' => $winsTotal,
                            'losses' => $lossesTotal,
                            'distance' => $distance,
                            'damage_dealt' => $damageDealtTotal,
                            'frags' => $fragsTotal,
                            'xp' => $xpTotal,
                            'survived_battles' => $survivedBattlesTotal,
                            'private_battle_life_time' => $privateBattleLifeTime,
                            'private_gold' => $privateGold,
                            'private_port' => $privatePort,
                        ]
                    );
                }
            }
        } catch (\Exception $e) {
            Log::error('Error fetching player stats', ['error' => $e->getMessage()]);
        }
    }
}
