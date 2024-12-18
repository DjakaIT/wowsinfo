<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class ShipService
{
    protected $apiKey;

    public function __construct()
    {
        $this->apiKey = config('services.wargaming.api_key');
    }

    public function getShips($page = 1, $limit = 100)
    {

        $url = "https://api.worldofwarships.eu/wows/encyclopedia/ships/?application_id={$this->apiKey}";


        try {
            $response = Http::get($url, [
                'application_id' => $this->apiKey,
                'page_no' => $page,
                'limit' => $limit,
            ]);

            if ($response->failed()) {
                Log::error("API request failed with status: " . $response->status());
                Log::error("Full API response: ", ['response' => $response->body()]);
                return null;
            }

            if ($response->successful()) {
                Log::info("API success response: ", $response->json());
                return $response->json();
            }
        } catch (\Exception $e) {
            Log::error("Exception during API call: " . $e->getMessage());
        }

        return null;
    }
}
