<?php

namespace App\Services;

use App\Models\Clan;
use App\Models\ClanMember;
use App\Models\PlayerShip;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\RateLimiter;

class ClanMemberService
{

    protected $apiKey;

    protected $baseUrls;



    public function __construct()
    {
        $this->apiKey = config('services.wargaming.api_key');

        $this->baseUrls = [
            'eu' => 'https://api.worldofwarships.eu',
            'na' => 'https://api.worldofwarships.com',
            'asia' => 'https://api.worldofwarships.asia',
        ];
    }






    public function fetchAndStoreClanMembers()
    {
        Log::info('fetchClanMembers method called');

        try {
            $clanIds = Clan::pluck('clan_id')->all();
            Log::info("Starting clan members update process", [
                'total_clans' => count($clanIds)
            ]);

            foreach ($this->baseUrls as $serverKey => $baseUrl) {
                Log::info("Processing server", ['server' => strtoupper($serverKey)]);

                $clansUrl = $baseUrl . "/wows/clans/info/";

                // Batch the clan IDs into groups of 100
                $batches = array_chunk($clanIds, 100);

                foreach ($batches as $batch) {
                    Log::info("Fetching data for clan batch", [
                        'batch_size' => count($batch),
                        'server' => strtoupper($serverKey)
                    ]);

                    // Define the rate limiter key
                    $rateLimitKey = "fetch-clan-batch:" . implode(',', $batch) . ":$serverKey";

                    // Attempt to fetch clan data with rate limiting
                    $executed = RateLimiter::attempt(
                        $rateLimitKey,
                        $perSecond = 20,
                        function () use ($batch, $clansUrl, $serverKey) {
                            $clanResponse = Http::get($clansUrl, [
                                'application_id' => $this->apiKey,
                                'clan_id' => implode(',', $batch),
                                'extra' => 'members'
                            ]);

                            Log::info("Raw API response for batch", [
                                'response' => $clanResponse->json(),
                                'server' => strtoupper($serverKey)
                            ]);

                            if ($clanResponse->successful()) {
                                $clanData = $clanResponse->json();

                                foreach ($batch as $clanId) {
                                    if (isset($clanData['data'][$clanId])) {
                                        $this->processClanData($clanData['data'][$clanId], $clanId, $serverKey);
                                    } else {
                                        Log::warning("No valid data found for clan in batch", [
                                            'clan_id' => $clanId,
                                            'server' => strtoupper($serverKey)
                                        ]);
                                    }
                                }
                            } else {
                                Log::error("Failed to fetch clan data for batch", [
                                    'batch' => implode(',', $batch),
                                    'server' => strtoupper($serverKey),
                                    'status' => $clanResponse->status()
                                ]);
                            }
                        },
                        $decayRate = 1
                    );



                    if (!$executed) {
                        Log::warning("Rate limit exceeded for batch on server: " . strtoupper($serverKey));
                        sleep(1);
                    }
                }
            }

            Log::info("Completed clan members update process");
        } catch (\Exception $e) {
            Log::error("Critical error in fetchAndStoreClanMembers", [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            throw $e;
        }
    }

    protected function processClanData($clanInfo, $clanId, $serverKey)
    {
        $clanName = $clanInfo['name'];
        $clanDescription = $clanInfo['description'];
        $members = $clanInfo['members'] ?? [];

        Log::info("Found members in clan", [
            'clan_id' => $clanId,
            'member_count' => count($members),
        ]);

        if (is_array($members) && count($members) > 0) {
            foreach ($members as $memberId => $player) {
                try {
                    $joinedAt = date('Y-m-d H:i:s', $player['joined_at']);

                    ClanMember::updateOrCreate(
                        ['account_id' => $player['account_id']],
                        [
                            'account_name' => $player['account_name'],
                            'clan_id' => $clanId,
                            'clan_name' => $clanName,
                            'joined_at' => $joinedAt,
                            'role' => $player['role'],
                        ]
                    );



                    Log::info("Updated/Created clan member", [
                        'account_id' => $player['account_id'],
                        'clan_id' => $clanId,
                        'server' => strtoupper($serverKey),
                    ]);
                } catch (\Exception $e) {
                    Log::error("Error saving clan member", [
                        'account_id' => $player['account_id'],
                        'clan_id' => $clanId,
                        'error' => $e->getMessage(),
                    ]);
                }
            }

            Clan::updateOrCreate(
                ['clan_id' => $clanId],
                [
                    'clan_description' => $clanDescription
                ]
            );
        } else {
            Log::info("No members found for this clan", ['clan_id' => $clanId]);
        }
    }
    public function getClanMemberData($clanId)
    {
        Log::info("Fetching clan member data for clan_id: {$clanId}");

        $members = DB::table('clan_members')
            ->leftJoin('player_ships', function ($join) {
                $join->on('clan_members.account_id', '=', 'player_ships.account_id');
            })
            ->join('clans', 'clan_members.clan_id', '=', 'clans.clan_id')
            ->where('clan_members.clan_id', $clanId)
            ->select(
                'clan_members.account_id',
                'clan_members.account_name as player_name',
                DB::raw('COALESCE(MAX(player_ships.total_player_wn8), 0) as wn8'),
                DB::raw('CASE WHEN SUM(player_ships.battles_played) > 0 THEN ROUND((SUM(player_ships.wins_count) / SUM(player_ships.battles_played))*100, 2) ELSE 0 END as win_rate'),
                DB::raw('COALESCE(SUM(player_ships.battles_played), 0) as battles'),
                DB::raw('MAX(player_ships.last_battle_time) as lastBattle'),
                'clan_members.role as position',
                'clan_members.joined_at as joined',
                'clans.tag as fullname',
                'clans.clan_description as description'
            )
            ->groupBy(
                'clan_members.account_id',
                'clan_members.account_name',
                'clan_members.role',
                'clan_members.joined_at',
                'clans.tag',
                'clans.clan_description'
            )
            ->get();

        Log::info("Found {$members->count()} members for clan_id: {$clanId}");

        $result = [];

        foreach ($members as $member) {
            $formattedLastBattleTime = $member->lastBattle ? date('Y-m-d H:i:s', $member->lastBattle) : null;

            $result[] = [
                'account_id'   => $member->account_id, // Adding account_id
                'name'         => $member->player_name,
                'wn8'          => $member->wn8 ?? 0,   // Ensure not null
                'winRate'      => $member->win_rate ?? 0, // Ensure not null
                'battles'      => $member->battles ?? 0,  // Ensure not null
                'lastBattle'   => $formattedLastBattleTime,
                'position'     => $member->position,
                'joined'       => $member->joined,
                'fullName'     => $member->fullname,
                'description'  => $member->description
            ];
        }

        // Debug logging to verify data
        Log::debug("Clan member data results", [
            'sample_member' => !empty($result) ? $result[0] : 'No members found'
        ]);

        return $result;
    }
    public function fetchAccountCreationDate($player, $serverKey)
    {
        // Ensure server is valid
        if (!isset($this->baseUrls[$serverKey])) {
            Log::error("Invalid server for account creation fetch", [
                'account_id' => $player['account_id'],
                'server' => $serverKey
            ]);
            return null;
        }

        $url = $this->baseUrls[$serverKey] . "/wows/account/info/";
        Log::info("Fetching account creation date", [
            'url' => $url,
            'account_id' => $player['account_id'],
            'server' => strtoupper($serverKey)
        ]);

        try {
            // Make API request to fetch account info
            $response = Http::get($url, [
                'application_id' => $this->apiKey,
                'account_id' => $player['account_id'],
            ]);

            if ($response->failed()) {
                Log::error("Account info API request failed", [
                    'account_id' => $player['account_id'],
                    'server' => strtoupper($serverKey),
                    'status' => $response->status()
                ]);
                return null;
            }

            $responseData = $response->json();
            Log::info("Account info API response", [
                'response' => $responseData,
                'server' => strtoupper($serverKey)
            ]);

            // Extract `created_at` if available
            if ($responseData['status'] === 'ok' && isset($responseData['data'][$player['account_id']]['created_at'])) {
                $createdAt = date('Y-m-d H:i:s', $responseData['data'][$player['account_id']]['created_at']);
                Log::info("Account creation date fetched", [
                    'account_id' => $player['account_id'],
                    'created_at' => $createdAt
                ]);

                // Update the clan_members table with account creation date
                ClanMember::where('account_id', $player['account_id'])->update(['account_created' => $createdAt]);

                return $createdAt;
            } else {
                Log::error("Unexpected Account Info response", [
                    'response' => $responseData,
                    'server' => strtoupper($serverKey)
                ]);
            }
        } catch (\Exception $e) {
            Log::error("Exception during API call to fetch account creation date", [
                'account_id' => $player['account_id'],
                'server' => strtoupper($serverKey),
                'error' => $e->getMessage()
            ]);
        }

        return null;
    }


    public function getPlayerMemberInfo($account_id, $name)
    {
        $playerInfo = ClanMember::join('clans', 'clan_members.clan_id', '=', 'clans.clan_id')
            ->select(
                'clan_members.account_id',
                'clan_members.account_name as name',
                'clan_members.clan_id',
                'clans.tag as clanName',
                'clan_members.account_created as createdAt'
            )
            ->where('clan_members.account_id', $account_id)
            ->where('clan_members.account_name', $name)
            ->first();


        // If not found in clan_members, fallback to player_ships table
        if (!$playerInfo) {
            $playerInfo = PlayerShip::select('account_id', 'player_name as name', DB::raw("'NOT IN A CLAN' as clanName"), 'created_at as createdAt')
                ->where('account_id', $account_id)
                ->first();
        }

        // If still not found, return a 404 response or custom error
        if (!$playerInfo) {
            Log::warning("Player not found in both clan_members and player_ships", ['account_id' => $account_id, 'name' => $name]);
            return null;
        }


        $playerData = [
            'name' => $playerInfo->name,
            'wid' => $playerInfo->account_id,
            'createdAt' => $playerInfo->createdAt,
            'clanName' => $playerInfo->clanName ?? null,
            'clanId' => $playerInfo->clan_id ?? null
        ];

        Log::info("Fetched player info", ['playerInfo' => $playerData]);
        return $playerData;
    }
}
