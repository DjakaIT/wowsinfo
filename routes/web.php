<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ClanController;
use App\Http\Controllers\PlayerController;
use App\Http\Controllers\ShipController;
use App\Http\Controllers\ClanMemberController;
use App\Http\Controllers\AchievementController;
use App\Http\Controllers\PlayerAchievementController;
use App\Http\Controllers\PlayerShipController;
use App\Http\Controllers\PlayerStatisticController;

Route::get('/', [PlayerShipController::class, 'getHomePageStats']); // Main home page


Route::prefix('clans')->group(function () {

    Route::get('/fetch', [ClanController::class, 'fetchAndStoreClans']);
    Route::get('/', [ClanController::class, 'index']);
    Route::get('/{id}', [ClanController::class, 'show']);
    Route::post('/', [ClanController::class, 'store']);
    Route::put('/{id}', [ClanController::class, 'update']);
    Route::delete('/{id}', [ClanController::class, 'destroy']);
});

Route::prefix('players')->group(function () {

    Route::get('/fetch', [PlayerController::class, 'fetchAndStorePlayers']);
    Route::get('/', [PlayerController::class, 'index']);
    Route::get('/{id}', [PlayerController::class, 'show']);
    Route::post('/', [PlayerController::class, 'store']);
    Route::put('/{id}', [PlayerController::class, 'update']);
    Route::delete('/{id}', [PlayerController::class, 'destroy']);
});



Route::prefix('ships')->group(function () {

    Route::get('/fetch', [ShipController::class, 'fetchAndStoreShips']);
    Route::get('/', [ShipController::class, 'index']);
    Route::get('/{id}', [ShipController::class, 'show']);
    Route::post('/', [ShipController::class, 'store']);
    Route::put('/{id}', [ShipController::class, 'update']);
    Route::delete('/{id}', [ShipController::class, 'destroy']);
});


Route::prefix('clan-members')->group(function () {

    Route::get('/fetch', [ClanMemberController::class, 'updateClanMembers']);
    Route::get('/{id}', [ClanMemberController::class, 'show']);
    Route::post('/', [ClanMemberController::class, 'store']);
    Route::put('/{id}', [ClanMemberController::class, 'update']);
    Route::delete('/{id}', [ClanMemberController::class, 'destroy']);
});

Route::prefix('achievements')->group(function () {

    Route::get('/fetch', [AchievementController::class, 'fetchAndStoreAchievements']);
    Route::get('/', [AchievementController::class, 'index']);
    Route::get('/{id}', [AchievementController::class, 'show']);
    Route::post('/', [AchievementController::class, 'store']);
    Route::put('/{id}', [AchievementController::class, 'update']);
    Route::delete('/{id}', [AchievementController::class, 'destroy']);
});

Route::prefix('player-achievements')->group(function () {

    Route::get('/fetch', [PlayerAchievementController::class, 'storePlayerAchievements']);
    Route::get('/', [PlayerAchievementController::class, 'index']);
    Route::get('/{id}', [PlayerAchievementController::class, 'show']);
    Route::post('/', [PlayerAchievementController::class, 'store']);
    Route::put('/{id}', [PlayerAchievementController::class, 'update']);
    Route::delete('/{id}', [PlayerAchievementController::class, 'destroy']);
});



Route::prefix('player-ships')->group(function () {

    Route::get('/fetch', [PlayerShipController::class, 'updatePlayerShips']);
    Route::get('/{id}/periodicplayerstats', [PlayerShipController::class, 'getPeriodicPlayerStats']);
    Route::get('/', [PlayerShipController::class, 'index']);
    Route::get('/{id}', [PlayerShipController::class, 'show']);
    Route::post('/', [PlayerShipController::class, 'store']);
    Route::put('/{id}', [PlayerShipController::class, 'update']);
    Route::delete('/{id}', [PlayerShipController::class, 'destroy']);
});


Route::prefix('player-stats')->group(function () {

    Route::get('/fetch', [PlayerStatisticController::class, 'updatePlayerStats']);
    Route::get('/', [PlayerStatisticController::class, 'index']);
    Route::get('/{id}', [PlayerStatisticController::class, 'show']);
    Route::post('/', [PlayerStatisticController::class, 'store']);
    Route::put('/{id}', [PlayerStatisticController::class, 'update']);
    Route::delete('/{id}', [PlayerStatisticController::class, 'destroy']);
});

Route::get('/player', function () {
    // DATA INFO
    // 1. List of 10 best players today -  tier is above 5 and 5+ battles
    // 2. List of 10 best players last 7 days - tier is above 5 and 30+ battles
    // 3. List of 10 best players last month (25 days) - tier is above 5 and 120+ battles
    // 4. List of 10 best players overall (28 days) - tier is above 5 and 500+ battles
    // 5. List of 10 best Clans
    return view('player', [
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
});
