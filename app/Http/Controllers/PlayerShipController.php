<?php

namespace App\Http\Controllers;

use App\Models\PlayerShip;
use App\Services\PlayerShipService;
use App\Services\ClanMemberService;
use App\Services\PlayerService;

use Illuminate\Http\Request;

class PlayerShipController extends Controller
{
    protected $playerShipService;
    protected $clanMemberService;

    protected $playerService;
    public function __construct(PlayerShipService $playerShipService, ClanMemberService $clanMemberService, PlayerService $playerService)
    {
        $this->playerShipService = $playerShipService;
        $this->clanMemberService = $clanMemberService;
        $this->playerService = $playerService;
    }

    //BLADE
    public function getPlayerPageStats($name, $account_id)
    {
        $metaTitle = "$name - WN8 player statistics for World of Warships";
        $metaDescription = "Latest statistics for player $name in World of Warships, WN8 daily, weekly and monthly updates and statistic.";
        $metaKeywords = "WN8, World of Warships, Statistics, Player statistics, $name";

        // Get player info
        $playerInfo = $this->clanMemberService->getPlayerMemberInfo($account_id, $name);
        $playerVehicleInfo = $this->playerShipService->getPlayerVehicleData($account_id, $name);
        if (!$playerInfo) {
            abort(404, 'Player not found');
        }


        return view('player', [
            'metaSite' => [
                'metaTitle' => $metaTitle,
                'metaDescription' => $metaDescription,
                'metaKeywords' => $metaKeywords,
            ],
            'playerInfo' => $playerInfo ?? null,
            'playerStatistics' => [] ?? null,
            'playerVehicles' => $playerVehicleInfo ?: [],
        ]);
    }

    public function getHomePageStats()
    {

        $topPlayersLast24Hours = $this->playerShipService->getTopPlayersLast24Hours();
        $topPlayersLast7Days = $this->playerShipService->getTopPlayersLast7Days();
        $topPlayersLastMonth = $this->playerShipService->getTopPlayersLastMonth();
        $topPlayersOverall = $this->playerShipService->getTopPlayersOverall();
        $topClans = $this->clanMemberService->getTopClans();

        $metaTitle = 'WN8 - Player statistics in World of Warships';
        $metaDescription = 'This page provide you with latest information on World of Warships players and clans, WN8 stats, improvement with daily updates.';
        $metaKeywords = 'WN8, World of Warships, Statistics, Player statistics';

        return view('home', [
            'metaSite' => [
                'metaTitle' => $metaTitle,
                'metaDescription' => $metaDescription,
                'metaKeywords' => $metaKeywords,
            ],
            'statistics' => [
                'topPlayersLast24Hours' => $topPlayersLast24Hours,
                'topPlayersLast7Days' => $topPlayersLast7Days,
                'topPlayersLastMonth' => $topPlayersLastMonth,
                'topPlayersOverall' => $topPlayersOverall,
                'topClans' => $topClans,

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
