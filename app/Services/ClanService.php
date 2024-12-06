<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

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
}
