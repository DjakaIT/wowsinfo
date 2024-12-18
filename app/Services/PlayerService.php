<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class PlayerService
{
    protected $apiKey;
    protected $baseUrls;

    public function __construct()
    {
        $this->apiKey = config('services.wargaming.api_key');

        // Base URLs for each server
        $this->baseUrls = [
            'eu' => 'https://api.worldofwarships.eu',
            'na' => 'https://api.worldofwarships.com',
            'asia' => 'https://api.worldofwarships.asia',
        ];
    }

    public function getAllPlayers($server, $search, $page = 1, $limit = 100)
    {
        if (!isset($this->baseUrls[$server])) {
            Log::error("Invalid server specified: {$server}");
            return null;
        }

        $url = $this->baseUrls[$server] . "/wows/account/list/";
        try {
            $response = Http::get($url, [
                'application_id' => $this->apiKey,
                'search' => $search,
                'page_no' => $page,
                'limit' => $limit,
            ]);

            if ($response->failed()) {
                Log::error("Player API Request failed for server: {$server} with status: " . $response->status());
                return null;
            }

            $responseData = $response->json();

            if ($responseData['status'] === 'ok' && isset($responseData['data'])) {
                return $responseData['data'];
            } else {
                Log::error("Unexpected API response for server: {$server}", ['response' => $responseData]);
            }
        } catch (\Exception $e) {
            Log::error("Exception during Player API call: " . $e->getMessage());
        }

        return null;
    }
}
