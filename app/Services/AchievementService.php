<?php

namespace App\Services;

use App\Models\Achievement;
use App\Models\Player;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class AchievementService
{
    protected $apiKey;

    public function __construct()
    {
        $this->apiKey = config('services.wargaming.api_key');
    }

    public function getAchievements()
    {
        Log::info('getAchievements method called');

        $url = "https://api.worldofwarships.eu/wows/encyclopedia/achievements/?application_id={$this->apiKey}";

        try {
            $response = Http::get($url);

            if ($response->failed()) {
                Log::error("API request failed with status: " . $response->status());
                Log::error("FULL API RESPONSE: " . ['response' => $response->body()]);
                return null;
            }

            if ($response->successful()) {
                Log::info("API success response: ", $response->json());
                return $response->json();
            }
        } catch (\Exception $e) {
            Log::error("Exception during the API call: " . $e->getMessage());
        }

        return null;
    }
}
