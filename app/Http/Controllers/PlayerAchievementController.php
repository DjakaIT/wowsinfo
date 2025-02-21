<?php

namespace App\Http\Controllers;

use App\Models\PlayerAchievement;
use Illuminate\Http\Request;
use App\Services\PlayerAchievementService;

class PlayerAchievementController extends Controller
{
    protected $playerAchievementService;

    public function __construct(PlayerAchievementService $playerAchievementService)
    {
        $this->playerAchievementService = $playerAchievementService;
    }

    public function storePlayerAchievements(Request $request)
    {
        $this->playerAchievementService->fetchAndStorePlayerAchievements();
        return response()->json(['message' => 'Player achievements stored successfully.']);
    }

    public function index()
    {
        return response()->json(PlayerAchievement::all());
    }

    public function show($id)
    {
        return response()->json(PlayerAchievement::findOrFail($id));
    }

    public function store(Request $request)
    {
        $validatedData = $request->validate([
            'account_id' => 'required|exists:players,account_id',
            'achievement_id' => 'required|exists:achievements,achievement_id',
            'achievement_count' => 'required|integer',
        ]);

        $playerAchievement = PlayerAchievement::create($validatedData);
        return response()->json($playerAchievement, 201);
    }

    public function update(Request $request, $id)
    {
        $playerAchievement = PlayerAchievement::findOrFail($id);

        $validatedData = $request->validate([
            'account_id' => 'required|exists:players,account_id',
            'achievement_id' => 'required|exists:achievements,achievement_id',
            'achievement_count' => 'required|integer',
        ]);

        $playerAchievement->update($validatedData);
        return response()->json($playerAchievement);
    }

    public function destroy($id)
    {
        PlayerAchievement::findOrFail($id)->delete();
        return response()->json(['message' => "Player's achievement deleted successfully from records."]);
    }
}
