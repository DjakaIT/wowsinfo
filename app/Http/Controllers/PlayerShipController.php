<?php

namespace App\Http\Controllers;

use App\Models\PlayerShip;
use App\Services\PlayerShipService;
use App\Services\ClanMemberService;
use App\Services\ClanService;
use App\Services\PlayerService;

use Illuminate\Http\Request;

class PlayerShipController extends Controller
{
    protected $playerShipService;

    protected $clanService;
    protected $clanMemberService;

    protected $playerService;
    public function __construct(PlayerShipService $playerShipService, ClanMemberService $clanMemberService, PlayerService $playerService, ClanService $clanService)
    {
        $this->playerShipService = $playerShipService;
        $this->clanMemberService = $clanMemberService;
        $this->playerService = $playerService;
        $this->clanService = $clanService;
    }

    //BLADE
    public function getPlayerPageStats($name, $account_id)
    {

        $server = request('server', session('server', 'EU'));
        session(['server' => $server]);

        $metaTitle = "$name - WN8 player statistics for World of Warships";
        $metaDescription = "Latest statistics for player $name in World of Warships, WN8 daily, weekly and monthly updates and statistic.";
        $metaKeywords = "WN8, World of Warships, Statistics, Player statistics, $name";

        // Get player info
        $playerInfo = $this->clanMemberService->getPlayerMemberInfo($account_id, $name);
        $playerVehicleInfo = $this->playerShipService->getPlayerVehicleData($account_id, $name);
        if (!$playerInfo) {
            abort(404, 'Player not found');
        }

        $playerStatisticsLastDay = $this->playerShipService->getPlayerStatsLastDay($account_id);
        $playerStatisticsLastWeek = $this->playerShipService->getPlayerStatsLastWeek($account_id);
        $playerStatisticsLastMonth = $this->playerShipService->getPlayerStatsLastMonth($account_id);
        $playerStatisticsOverall = $this->playerShipService->getPlayerStatsOverall($name, $account_id);


        $playerStatistics = [
            'lastDay' => $playerStatisticsLastDay,
            'lastWeek' => $playerStatisticsLastWeek,
            'lastMonth' => $playerStatisticsLastMonth,
            'overall' => $playerStatisticsOverall
        ];


        return view('player', [
            'metaSite' => [
                'metaTitle' => $metaTitle,
                'metaDescription' => $metaDescription,
                'metaKeywords' => $metaKeywords,
            ],
            'playerInfo' => $playerInfo ?? null,
            'playerStatistics' => $playerStatistics,
            'playerVehicles' => $playerVehicleInfo ?: [],
            'server' => $server,
        ]);
    }

    // Ovo bi trebalo u kontroler za homepage ili statistiku
    public function getHomePageStats()
    {

        $topPlayersLast24Hours = $this->playerShipService->getTopPlayersLast24Hours();
        $topPlayersLast7Days = $this->playerShipService->getTopPlayersLast7Days();
        $topPlayersLastMonth = $this->playerShipService->getTopPlayersLastMonth();
        $topPlayersOverall = $this->playerShipService->getTopPlayersOverall();
        $topClans = $this->clanService->getTopClans();

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


    public function updateOverallPlayerShipStats()
    {
        $this->playerShipService->fetchAndStoreOverallPlayerStats();
        return response()->json(['message' => 'Overall player ship statistics fetched and stored successfully.']);
    }
    public function updatePlayerShips()
    {
        $this->playerShipService->fetchAndStorePlayerShips();
        return response()->json(['message' => 'Player ship statistics fetched and stored successfully.']);
    }

    public function cachePlayerStatistics()
    {
        $this->playerShipService->cachePlayerStats();
        return response()->json(['message' => 'Player stats caching method invoked successfully.']);
    }

    public function cacheTopPlayers()
    {
        $this->playerShipService->cacheTopPlayersList();
        return response()->json(['message' => 'Top players caching method invoked successfully.']);
    }
}
