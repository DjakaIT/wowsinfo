<?php
// app/Services/ClanService.php

namespace App\Services;

use App\Models\Clan;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

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
                        Clan::updateOrCreate(
                            ['clan_id' => $clanData['clan_id']],
                            [
                                'name' => $clanData['name'],
                                'tag' => $clanData['tag'],
                                'server' => strtoupper($server),
                                'clan_created' => $clanCreated,
                                'members_count' => $membersCount
                            ]
                        );
                        Log::info("Stored clan with ID: " . $clanData['clan_id'] . " on server: " . strtoupper($server));
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
}
