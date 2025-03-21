<?php

namespace App\Http\Controllers;

use App\Models\PlayerShip;
use App\Services\PlayerShipService;
use App\Services\ClanMemberService;
use App\Services\ClanService;
use App\Services\PlayerService;
use Illuminate\Support\Facades\Log;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

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
    public function getPlayerPageStats($locale, $server, $name, $account_id)
    {
        // Set locale and server in session
        app()->setLocale($locale);
        session(['locale' => $locale, 'server' => strtoupper($server)]);

        $metaTitle = "$name - WN8 player statistics for World of Warships";
        $metaDescription = "Latest statistics for player $name in World of Warships, WN8 daily, weekly and monthly updates and statistic.";
        $metaKeywords = "WN8, World of Warships, Statistics, Player statistics, $name";

        // Get player info
        $playerInfo = $this->clanMemberService->getPlayerMemberInfo($account_id, $name);
        $playerVehicleInfo = $this->playerShipService->getPlayerVehicleData($account_id, $name);
        // If player doesn't exist in our database, try to fetch their stats
        if (!$playerInfo) {
            Log::info("Player not found in database, attempting to fetch from API", [
                'name' => $name,
                'account_id' => $account_id
            ]);

            // Try to fetch the player's data from the API
            $fetchSuccess = $this->playerShipService->fetchSinglePlayerStats($name, $account_id);

            if ($fetchSuccess) {
                // If fetch was successful, try to get player info again
                $playerInfo = $this->clanMemberService->getPlayerMemberInfo($account_id, $name);
            }

            // If we still don't have player info, show a temporary processing page
            if (!$playerInfo) {
                return view('player', [
                    'metaSite' => [
                        'metaTitle' => "$name - WN8 player statistics for World of Warships",
                        'metaDescription' => "Latest statistics for player $name in World of Warships, WN8 daily, weekly and monthly updates and statistic.",
                        'metaKeywords' => "WN8, World of Warships, Statistics, Player statistics, $name",
                    ],
                    'playerInfo' => [
                        'name' => $name,
                        'createdAt' => 'Processing...',
                        'clanName' => '',
                        'clanId' => null
                    ],
                    'playerStatistics' => [
                        'overall' => [
                            'processing' => true,
                            'message' => "We're fetching this player's statistics. Please refresh in about a minute."
                        ]
                    ],
                    'playerVehicles' => [],
                    'server' => request('server', 'EU'),
                ]);
            }
        }

        $playerStatisticsLastDay = $this->playerShipService->getPlayerStatsLastDay($account_id);
        $playerStatisticsLastWeek = $this->playerShipService->getPlayerStatsLastWeek($account_id);
        $playerStatisticsLastMonth = $this->playerShipService->getPlayerStatsLastMonth($account_id);
        $playerStatisticsOverall = $this->playerShipService->getPlayerStatsOverall($name, $account_id);

        if ($playerStatisticsLastWeek['battles'] === null || $playerStatisticsLastWeek['battles'] === '-') {
            Log::info("Weekly stats empty for player {$account_id}, using daily stats instead");
            $playerStatisticsLastWeek = $playerStatisticsLastDay;
        }

        // Check if monthly stats are empty (null battles) and use weekly stats as fallback
        if ($playerStatisticsLastMonth['battles'] === null || $playerStatisticsLastMonth['battles'] === '-') {
            Log::info("Monthly stats empty for player {$account_id}, using weekly stats instead");
            $playerStatisticsLastMonth = $playerStatisticsLastWeek;
        }


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
        // Use a single cache key for all homepage statistics
        $statistics = Cache::remember('homepage_statistics', now()->addHours(12), function () {
            return [
                'topPlayersLast24Hours' => $this->playerShipService->getTopPlayersLast24Hours(),
                'topPlayersLast7Days' => $this->playerShipService->getTopPlayersLast7Days(),
                'topPlayersLastMonth' => $this->playerShipService->getTopPlayersLastMonth(),
                'topPlayersOverall' => $this->playerShipService->getTopPlayersOverall(),
                'topClans' => $this->clanService->getTopClans(),
            ];
        });

        return view('home', [
            'metaSite' => [
                'metaTitle' => __('seo_home_title'),
                'metaDescription' => __('seo_home_content'),
                'metaKeywords' => __('seo_home_keywords')
            ],
            'statistics' => $statistics
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


    public function getNullNames()
    {
        $this->playerShipService->getNullNamePlayersNames();
    }



    public function getAccountIdByUsername(Request $request)
    {
        $username = $request->input('username');

        if (empty($username)) {
            return response()->json([
                'success' => false,
                'message' => 'No username provided'
            ], 400);
        }

        $result = $this->playerShipService->updatePlayerStats($username);

        return response()->json($result);
    }
}
