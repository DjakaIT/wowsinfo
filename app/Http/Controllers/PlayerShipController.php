<?php

namespace App\Http\Controllers;

use App\Models\PlayerShip;
use App\Services\PlayerShipService;
use Illuminate\Http\Request;

class PlayerShipController extends Controller
{
    protected $playerShipService;

    public function __construct(PlayerShipService $playerShipService)
    {
        $this->playerShipService = $playerShipService;
    }

    public function getHomePageSTats()
    {

        $topPlayersLast24Hours = $this->playerShipService->getTopPlayersLast24Hours();

        return view('home', [
            'statistics' => [
                'topPlayersLast24Hours' => $topPlayersLast24Hours,
            ],
        ]);
    }
    public function updatePlayerShips()
    {
        $this->playerShipService->fetchAndStorePlayerShips();
        return response()->json(['message' => 'Player ship statistics fetched and stored successfully.']);
    }

    /*  public function getPeriodicPlayerStats($playerId, $period)
    {
        $this->playerShipService->getPlayerStatsByPeriod($playerId, $period);
    } */

    public function index()
    {
        $playerShips = PlayerShip::all();
        return response()->json($playerShips);
    }

    public function show($id)
    {
        $playerShip = PlayerShip::findOrFail($id);
        return response()->json($playerShip);
    }

    public function store(Request $request)
    {
        $validatedNewStatData = $request->validate([
            'player_id' => 'required|integer|unique:players,player_id',
            'ship_id' => 'required|integer|unique:ships, ship_id',
            'battles_played' => 'required|integer',
            'wins_count' => 'required|integer',
            'damage_dealt' => 'required|integer',
            'average_damage' => 'required|float',
            'frags' => 'required|integer',
            'survival_rate' => 'required|float'
        ]);

        $playerShip = PlayerShip::create($validatedNewStatData);
        return response()->json($playerShip, 201);
    }

    public function update(Request $request, $id)
    {
        $playerShip = PlayerShip::findOrFail($id);

        $validatedUpdatedStatData = $request->validate([
            'player_id' => 'required|integer|unique:players,player_id',
            'ship_id' => 'required|integer|unique:ships, ship_id',
            'battles_played' => 'required|integer',
            'wins_count' => 'required|integer',
            'damage_dealt' => 'required|integer',
            'average_damage' => 'required|float',
            'frags' => 'required|integer',
            'survival_rate' => 'required|float'
        ]);

        $playerShip->update($validatedUpdatedStatData);
        return response()->json($playerShip);
    }

    public function destroy($id)
    {
        $playerShip = PlayerShip::findOrFail($id);
        $playerShip->delete();

        return response()->json(['message' => "Player's ships stats deleted succesfully from records."]);
    }
}
