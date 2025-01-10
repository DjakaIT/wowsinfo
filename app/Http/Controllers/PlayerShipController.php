<?php

namespace App\Http\Controllers;

use App\Models\PlayerShip;
use App\Services\PlayerShipService;
use App\Services\ClanMemberService;

use Illuminate\Http\Request;

class PlayerShipController extends Controller
{
    protected $playerShipService;
    protected $clanMemberService;
    public function __construct(PlayerShipService $playerShipService,  ClanMemberService $clanMemberService)
    {
        $this->playerShipService = $playerShipService;
        $this->clanMemberService = $clanMemberService;
    }

    //BLADE
    public function getPlayerPageStats()
    {
        // $PLNAME je promenljiva treba se zameniti stvarnim imenom igraca
        $metaTitle = '$PLNAME - WN8 player statistics for World of Warships';
        $metaDescription = 'Latest statistics for player $PLNAME  in World of Warships, WN8 daily, weekly and monthly updates and statistic.';
        $metaKeywords = 'WN8, World of Warships, Statistics, Player statistics, $PLNAME';

        return view('player', [
            'metaSite' => [
                'metaTitle' => $metaTitle,
                'metaDescription' => $metaDescription,
                'metaKeywords' => $metaKeywords,
            ],
            'playerInfo' => [
                'name' => 'Player 1',
                'wid' => 111,
                'createdAt' => '01.12.2023',
                'clanName' => 'Clan 1',
                'clanId' => 333
            ],
            'playerStatistics' => [
                'overall' => [
                    'battles' => 2000,
                    'wins' => 59.7, // percentage
                    'tier' => '7,7',
                    'survived' => 48.59, // perventage
                    'damage' => 70.968,
                    'frags' => '1,13',
                    'spotted' => '0,18',
                    'xp' => 1.889,
                    'capture' => 1000, // ??? type ???
                    'defend' => 1000, // ??? type ???
                    'pr' => 2800, // ??? type ???
                    'wn8' => 3200 // ??? type ???
                ],
                'lastDay' => [ // last day only
                    'battles' => 4,
                    'wins' => 40.3, // percentage
                    'tier' => '8',
                    'survived' => 67.12, // perventage
                    'damage' => 6.322,
                    'frags' => '2,11',
                    'spotted' => '0,18',
                    'xp' => 652,
                    'capture' => 300, // ??? type ???
                    'defend' => 155, // ??? type ???
                    'pr' => 2005, // ??? type ???
                    'wn8' => 2890 // ??? type ???
                ],
                'lastWeek' => [ // last 7 days
                    'battles' => 22,
                    'wins' => 48.9, // percentage
                    'tier' => '8,2',
                    'survived' => 37.12, // perventage
                    'damage' => 12.500,
                    'frags' => '2,15',
                    'spotted' => '0,44',
                    'xp' => 790,
                    'capture' => 400, // ??? type ???
                    'defend' => 390, // ??? type ???
                    'pr' => 2980, // ??? type ???
                    'wn8' => 2750 // ??? type ???
                ],
                'lastMonth' => [ // Last 25 days
                    'battles' => 154,
                    'wins' => 60.3, // percentage
                    'tier' => '8,1',
                    'survived' => 60.4, // perventage
                    'damage' => 20.548,
                    'frags' => '2,12',
                    'spotted' => '0,56',
                    'xp' => 980,
                    'capture' => 824, // ??? type ???
                    'defend' => 759, // ??? type ???
                    'pr' => 2299, // ??? type ???
                    'wn8' => 3145 // ??? type ???
                ]
            ],
            'playerVehicles' => [
                [
                    'nation' => 'Germany',
                    'name' => 'Vehicle name',
                    'tier' => 2,
                    'battles' => 38,
                    'frags' => 34,
                    'damage' => 4.280,
                    'wins' => 67.46, // percentage
                    'wn8' => 1754,
                    'image' => 'image url', // ??? url ???
                    'description' => 'Vehicle description',
                    'wid' => 555
                ],
                [
                    'nation' => 'Japan',
                    'name' => 'Vehicle name',
                    'tier' => 4,
                    'battles' => 45,
                    'frags' => 32,
                    'damage' => 7.490,
                    'wins' => 36.46, // percentage
                    'wn8' => 980,
                    'image' => 'image url', // ??? url ???
                    'description' => 'Vehicle description',
                    'wid' => 555
                ]
            ],
        ]);
    }

    // Ovo bi trebalo u kontroler za homepage ili statistiku
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
