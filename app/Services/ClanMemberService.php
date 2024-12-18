<?php

namespace App\Services;

use App\Models\Clan;
use App\Models\ClanMember;
use App\Models\PlayerShip;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;

class ClanMemberService
{
    protected $clansUrl = "https://api.worldofwarships.eu/wows/clans/info/";

    protected $apiKey;

    public function __construct()
    {
        $this->apiKey = config('services.wargaming.api_key');
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
            ClanMember::truncate();

            $clanIds = Clan::pluck('clan_id')->all();
            Log::info("Starting clan members update process", [
                'total_clans' => count($clanIds)
            ]);

            foreach ($clanIds as $clanId) {
                Log::info("Fetching data for clan", ['clan_id' => $clanId]);

                // Get clan info and member details including clan_name
                $clanResponse = Http::get($this->clansUrl, [
                    'application_id' => $this->apiKey,
                    'clan_id' => $clanId,
                    'extra' => 'members'
                ]);

                if ($clanResponse->successful()) {
                    $clanData = $clanResponse->json();

                    if (isset($clanData['data'][$clanId])) {
                        $clanInfo = $clanData['data'][$clanId];
                        $clanName = $clanInfo['name'];
                        $members = $clanInfo['members'] ?? []; // Default to empty array if `members` is null

                        Log::info("Found members in clan", [
                            'clan_id' => $clanId,
                            'member_count' => is_array($members) ? count($members) : 0
                        ]);

                        if (is_array($members) && count($members) > 0) {
                            foreach ($members as $memberId => $player) {
                                $total_player_wn8 = PlayerShip::where('account_id', $player['account_id'])->value('total_player_wn8');
                                $clan_wn8 = 0;
                                $player_count = 0;
                                if ($total_player_wn8 !== null) {
                                    $clan_wn8 += $total_player_wn8;
                                    $player_count++;
                                }
                                $total_clan_wn8 = $player_count > 0 ? round($clan_wn8 / $total_player_wn8) : 0;
                                try {
                                    ClanMember::updateOrCreate(
                                        ['account_id' => $player['account_id']],
                                        [
                                            'account_name' => $player['account_name'],
                                            'clan_id' => $clanId,
                                            'clan_name' => $clanName,
                                            'joined_at' => now()->setTimestamp($player['joined_at']),
                                            'role' => $player['role'],
                                            'total_clan_wn8' => $total_clan_wn8,
                                        ]
                                    );

                                    Log::info("Updated/Created clan member", [
                                        'account_id' => $player['account_id'],
                                        'account_name' => $player['account_name'],
                                        'clan_id' => $clanId,
                                        'clan_name' => $clanName
                                    ]);
                                } catch (\Exception $e) {
                                    Log::error("Error saving clan member", [
                                        'account_id' => $player['account_id'],
                                        'clan_id' => $clanId,
                                        'error' => $e->getMessage()
                                    ]);
                                }
                            }
                        } else {
                            Log::info("No members found for this clan", ['clan_id' => $clanId]);
                        }
                    } else {
                        Log::warning("No valid data found for clan", [
                            'clan_id' => $clanId,
                            'response_data' => $clanData
                        ]);
                    }
                } else {
                    Log::error("Failed to fetch clan data", [
                        'clan_id' => $clanId,
                        'status' => $clanResponse->status(),
                        'response' => $clanResponse->json()
                    ]);
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
}
