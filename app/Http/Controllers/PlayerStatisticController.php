<?php

namespace App\Http\Controllers;

use App\Models\PlayerStatistic;
use App\Services\PlayerStatisticService;
use Illuminate\Http\Request;

class PlayerStatisticController extends Controller
{

    protected $playerStatisticService;

    public function __construct(PlayerStatisticService $playerStatisticService)
    {
        $this->playerStatisticService = $playerStatisticService;
    }

    public function updatePlayerStats()
    {
        $this->playerStatisticService->fetchAndStorePlayerStats();
        return response()->json(['message' => 'method fetchAndStorePlayerStats finished succesfully.']);
    }

    public function index()
    {
        $playerStatistic = PlayerStatistic::all();
        return response()->json($playerStatistic);
    }

    public function show($id)
    {
        $playerStatistic = PlayerStatistic::findOrFail($id);
        return response()->json($playerStatistic);
    }

    public function store(Request $request)
    {
        $validatedNewStatData = $request->validate([
            'player_id' => 'required|exists:players,id',
            'battles_played' => 'required|integer',
            'wins' => 'required|integer',
            'damage_dealt' => 'required|integer',
            'avg_xp' => 'required|integer',
            'win_rate' => 'required|numeric',
            'wn8' => 'required|numeric',
        ]);


        $playerStatistic = PlayerStatistic::create($validatedNewStatData);
        return response()->json($playerStatistic, 201);
    }

    public function update(Request $request, $id)
    {

        $playerStatistic = PlayerStatistic::findOrFail($id);

        $validatedUpdatedStatData = $request->validate([
            'player_id' => 'required|exists:players,id',
            'battles_played' => 'required|integer',
            'wins' => 'required|integer',
            'damage_dealt' => 'required|integer',
            'avg_xp' => 'required|integer',
            'win_rate' => 'required|numeric',
            'wn8' => 'required|numeric',
        ]);


        $playerStatistic->update($validatedUpdatedStatData);
        return response()->json($playerStatistic);
    }

    public function destroy($id)
    {
        $playerStatistic = PlayerStatistic::findOrFail($id);
        $playerStatistic->delete();

        return response()->json(['message' => "Player's stats deleted succesfully from records."]);
    }
}
