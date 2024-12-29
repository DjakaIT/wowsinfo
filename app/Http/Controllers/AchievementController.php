<?php


namespace App\Http\Controllers;

use App\Services\AchievementService;
use App\Models\Achievement;
use Illuminate\Support\Facades\Log;
use Illuminate\Http\Request;

class AchievementController extends Controller
{
    protected $achievementService;

    public function __construct(AchievementService $achievementService)
    {
        $this->achievementService = $achievementService;
    }

    public function fetchAndStoreAchievements()
    {
        Log::info("Reached fetchAndStoreAchievements method");


        $achievements = $this->achievementService->getAchievements();

        if ($achievements && isset($achievements['data']['battle'])) {
            foreach ($achievements['data']['battle'] as $achievementData) {
                Achievement::updateOrCreate(
                    ['achievement_id' => $achievementData['achievement_id']],
                    [
                        'achievement_name' => $achievementData['name'],
                        'description' => $achievementData['description'],
                        'image' => $achievementData['image'],
                        'image_inactive' => $achievementData['image_inactive'],
                        'type' => $achievementData['type'],
                        'sub_type' => $achievementData['sub_type'],
                        'max_progress' => $achievementData['max_progress'],
                        'is_progress' => $achievementData['is_progress'],
                    ]
                );

                Log::info("Stored achievement with ID: " . $achievementData['achievement_id']);
            }
        } else {
            Log::warning("No achievement data found or incorrect format.");
        }


        return response()->json(['message' => 'Achievements fetched and stored succesfully', 201]);
    }
}
