<?php

namespace App\Services;

use App\Models\PlayerAchievement;
use App\Models\Player;
use App\Models\Achievement;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class PlayerAchievementService
{
    protected $apiUrl = 'https://api.worldofwarships.eu/wows/account/achievements/';
    protected $apiKey;

    public function __construct()
    {
        $this->apiKey = config('services.wargaming.api_key');
    }

    public function fetchAndStorePlayerAchievements()
    {
        try {
            // Fetch all player account IDs (paginated)
            Player::chunk(1000, function ($players) {
                // Load achievement names and IDs from the database
                $achievementMap = Achievement::pluck('achievement_name', 'achievement_id')->toArray();
                Log::info("Loaded achievements", ['achievements_count' => count($achievementMap)]);

                // Collect account IDs to fetch in batches
                $accountIds = $players->pluck('account_id')->toArray();
                Log::info("Loaded players", ['players_count' => count($accountIds)]);

                // Process in smaller batches to avoid URL length issues
                $batchSize = 100; // Adjust batch size to prevent URI too large error
                foreach (array_chunk($accountIds, $batchSize) as $accountBatch) {
                    // API request for each batch
                    $response = Http::get($this->apiUrl, [
                        'application_id' => $this->apiKey,
                        'account_id' => implode(',', $accountBatch) // Pass a small batch of account IDs
                    ]);

                    // Handle the response and error logging
                    if ($response->successful()) {
                        $data = $response->json();

                        // Ensure the data is valid
                        if (isset($data['status']) && $data['status'] === 'ok') {
                            foreach ($accountBatch as $accountId) {
                                if (isset($data['data'][$accountId])) {
                                    $achievementsData = $data['data'][$accountId];

                                    // Process both 'battle' and 'progress' achievements
                                    $achievementsToInsert = [];
                                    foreach (['battle', 'progress'] as $type) {
                                        foreach ($achievementsData[$type] ?? [] as $achievementId => $count) {
                                            // Lookup achievement name by ID
                                            $achievementName = $achievementMap[$achievementId] ?? 'Unknown achievement';

                                            // Collect achievements to insert in bulk
                                            $achievementsToInsert[] = [
                                                'account_id' => $accountId,
                                                'achievement_id' => $achievementId,
                                                'achievement_name' => $achievementName,
                                                'achievement_type' => $type,
                                                'achievement_count' => $count,
                                                'created_at' => now(),
                                                'updated_at' => now(),
                                            ];
                                        }
                                    }

                                    // Insert all achievements in bulk
                                    if (count($achievementsToInsert) > 0) {
                                        PlayerAchievement::insertOrIgnore($achievementsToInsert);
                                    }
                                }
                            }
                        }
                    } else {
                        // Log the status code and error message for debugging
                        $this->logApiError($response);
                    }
                }
            });
        } catch (\Exception $e) {
            Log::error("Error fetching achievements: " . $e->getMessage());
        }
    }

    /**
     * Log the API error response
     *
     * @param \Illuminate\Http\Client\Response $response
     */
    protected function logApiError($response)
    {
        // Check if the response is HTML, and if so, log a warning
        if ($response->status() === 414) {
            Log::warning("API Error - Request URI Too Large", [
                'status_code' => $response->status(),
                'error_message' => 'Request URI Too Large - The account batch may be too large.'
            ]);
        } elseif ($response->status() >= 400 && $response->status() < 500) {
            Log::warning("Client-side API Error", [
                'status_code' => $response->status(),
                'error_message' => $response->body()
            ]);
        } elseif ($response->status() >= 500) {
            Log::error("Server-side API Error", [
                'status_code' => $response->status(),
                'error_message' => $response->body()
            ]);
        } else {
            Log::info("API Response - Status Code: {$response->status()}", [
                'response' => $response->body()
            ]);
        }
    }
}
