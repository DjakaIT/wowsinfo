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
        } else {
            Log::info("No members found for this clan", ['clan_id' => $clanId]);
        }
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
        $playerInfo = ClanMember::select('account_id', 'account_name as name', 'clan_id', 'clan_name as clanName', 'account_created as createdAt')
            ->where('account_id', $account_id)
            ->where('account_name', $name)
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
