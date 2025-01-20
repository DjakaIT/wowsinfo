<?php
// app/Services/ClanService.php

namespace App\Services;

use App\Models\Clan;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class ClanService
{
    protected $apiKey;

    public function __construct()
    {
        $this->apiKey = config('services.wargaming.api_key');
    }

    public function getClans($server, $page = 1, $limit = 100)
    {
        $baseUrls = [
            'eu' => 'https://api.worldofwarships.eu',
            'na' => 'https://api.worldofwarships.com',
            'asia' => 'https://api.worldofwarships.asia',
        ];

        $url = "{$baseUrls[$server]}/wows/clans/list/";

        try {
            $response = retry(3, function () use ($url, $page, $limit) {
                return Http::timeout(60)->get($url, [
                    'application_id' => $this->apiKey,
                    'page_no' => $page,
                    'limit' => $limit,
                ]);
            }, 1000);

            if ($response->failed()) {
                Log::error("API Request failed with status: " . $response->status());
                Log::error("Full API Response", ['response' => $response->body()]);
                return null;
            }

            return $response->json();
        } catch (\Exception $e) {
            Log::error("Exception during API Call: " . $e->getMessage());
            return null;
        }
    }

    public function fetchAndStoreClans()
    {
        Log::info("Reached fetchAndStoreClans method");

        $servers = ['eu', 'na', 'asia'];
        $limit = 100;

        foreach ($servers as $server) {
            $page = 1;
            $hasMore = true;

            Log::info("Fetching clans from server: " . strtoupper($server));

            while ($hasMore) {
                $clans = $this->getClans($server, $page, $limit);

                if ($clans && isset($clans['data'])) {
                    foreach ($clans['data'] as $clanData) {
                        $clanCreated = isset($clanData['created_at']) ? Carbon::createFromTimestamp($clanData['created_at'])->toDateTimeString() : null;
                        $membersCount = isset($clanData['members_count']) ? (int) $clanData['members_count'] : null;

                        $storedClan = Clan::updateOrCreate(
                            ['clan_id' => $clanData['clan_id']],
                            [
                                'name' => $clanData['name'],
                                'tag' => $clanData['tag'],
                                'server' => strtoupper($server),
                                'clan_created' => $clanCreated,
                                'members_count' => $membersCount,
                            ]
                        );

                        Log::info("Stored clan", [
                            'clan_id' => $clanData['clan_id'],
                            'name' => $storedClan->name,
                            'tag' => $storedClan->tag,
                            'server' => strtoupper($server),
                            'clan_created' => $storedClan->clan_created,
                            'members_count' => $storedClan->members_count,
                        ]);
                    }

                    Log::info("Fetched page {$page} from server: " . strtoupper($server));

                    $page++;
                    $hasMore = count($clans['data']) === $limit;
                } else {
                    $hasMore = false;
                    Log::warning("No more clans to fetch from server: " . strtoupper($server));
                }
            }
        }

        return ['message' => 'Clans fetched and stored successfully'];
    }


    public function getTopClans()
    {
        return Clan::select('clan_id', DB::raw('name as clan_name'), DB::raw('MAX(clanwn8) as total_clan_wn8'))
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

    public function calculateClanWN8()
    {
        Log::info("Calculating WN8 for all clans");

        Clan::chunk(100, function ($clans) {
            foreach ($clans as $clan) {
                // Get distinct total_player_wn8 per member
                $memberWN8s = DB::table('clan_members')
                    ->join('player_ships', 'clan_members.account_id', '=', 'player_ships.account_id')
                    ->where('clan_members.clan_id', $clan->clan_id)
                    ->groupBy('player_ships.account_id')
                    ->select(DB::raw('MAX(player_ships.total_player_wn8) as wn8'))
                    ->get();

                $sumOfWN8 = $memberWN8s->sum('wn8');
                $memberCount = $memberWN8s->count();
                $clanWN8 = $memberCount > 0 ? round($sumOfWN8 / $memberCount) : 0;

                $clan->update(['clanwn8' => $clanWN8]);

                Log::info("Debug clan WN8", [
                    'clan_id' => $clan->clan_id,
                    'sumOfWN8' => $sumOfWN8,
                    'memberCount' => $memberCount,
                    'calculated_clan_wn8' => $clanWN8,
                ]);
            }
        });

        Log::info("Completed WN8 calculations for all clans");
    }
}
