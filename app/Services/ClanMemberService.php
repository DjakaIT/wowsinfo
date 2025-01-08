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


    public function getTopClans()
    {
        return ClanMember::select('clan_id', DB::raw('clan_name as clan_name'), DB::raw('MAX(total_clan_wn8) as total_clan_wn8'))
            ->groupBy('clan_id', 'clan_name')
            ->orderByDesc('total_clan_wn8')
            ->limit(10)
            ->get()
            ->map(function ($clan) {
                return [
                    'name' => $clan->clan_name,
                    'wid' => $clan->clan_id,
                    'wn8' => $clan->total_clan_wn8
                ];
            })
            ->toArray();
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

                foreach ($clanIds as $clanId) {
                    Log::info("Fetching data for clan", ['clan_id' => $clanId, 'server' => strtoupper($serverKey)]);

                    $clansUrl = $baseUrl . "/wows/clans/info/";

                    // Define the rate limiter key
                    $rateLimitKey = "fetch-clan:$clanId:$serverKey";

                    // Attempt to fetch clan data with rate limiting
                    $executed = RateLimiter::attempt(
                        $rateLimitKey,
                        $perSecond = 20,
                        function () use ($clanId, $clansUrl, $serverKey) {
                            $clanResponse = Http::get($clansUrl, [
                                'application_id' => $this->apiKey,
                                'clan_id' => $clanId,
                                'extra' => 'members'
                            ]);

                            if ($clanResponse->successful()) {
                                $clanData = $clanResponse->json();

                                if (isset($clanData['data'][$clanId])) {
                                    $clanInfo = $clanData['data'][$clanId];
                                    $clanName = $clanInfo['name'];
                                    $members = $clanInfo['members'] ?? [];

                                    Log::info("Found members in clan", [
                                        'clan_id' => $clanId,
                                        'member_count' => is_array($members) ? count($members) : 0
                                    ]);

                                    $player_count = count($members);

                                    if (is_array($members) && $player_count > 0) {
                                        $sum_player_wn8 = 0;
                                        foreach ($members as $memberId => $player) {
                                            $total_player_wn8 = PlayerShip::where('account_id', $player['account_id'])->value('total_player_wn8');
                                            $total_player_wn8 = $total_player_wn8 !== null ? $total_player_wn8 : 0;

                                            if ($total_player_wn8 > 0) {
                                                $sum_player_wn8 += $total_player_wn8;
                                            }
                                            try {
                                                $createdAt = $this->fetchAccountCreationDate($player, $serverKey);

                                                ClanMember::updateOrCreate(
                                                    ['account_id' => $player['account_id']],
                                                    [
                                                        'account_name' => $player['account_name'],
                                                        'clan_id' => $clanId,
                                                        'clan_name' => $clanName,
                                                        'joined_at' => now()->setTimestamp($player['joined_at']),
                                                        'role' => $player['role'],
                                                        'account_created' => $createdAt,
                                                    ]
                                                );

                                                Log::info("Updated/Created clan member", [
                                                    'account_id' => $player['account_id'],
                                                    'account_name' => $player['account_name'],
                                                    'clan_id' => $clanId,
                                                    'server' => strtoupper($serverKey)
                                                ]);
                                            } catch (\Exception $e) {
                                                Log::error("Error saving clan member", [
                                                    'account_id' => $player['account_id'],
                                                    'clan_id' => $clanId,
                                                    'error' => $e->getMessage()
                                                ]);
                                            }
                                        }

                                        $total_clan_wn8 = $player_count > 0 ? round($sum_player_wn8 / $player_count) : 0;
                                        ClanMember::where('clan_id', $clanId)->update(['total_clan_wn8' => $total_clan_wn8]);
                                    } else {
                                        Log::info("No members found for this clan", ['clan_id' => $clanId]);
                                    }
                                } else {
                                    Log::warning("No valid data found for clan", [
                                        'clan_id' => $clanId,
                                        'server' => strtoupper($serverKey)
                                    ]);
                                }
                            } else {
                                Log::error("Failed to fetch clan data", [
                                    'clan_id' => $clanId,
                                    'server' => strtoupper($serverKey),
                                    'status' => $clanResponse->status()
                                ]);
                            }
                        },
                        $decayRate = 1
                    );

                    if (!$executed) {
                        Log::warning("Rate limit exceeded for clan ID: {$clanId} on server: " . strtoupper($serverKey));
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
}
