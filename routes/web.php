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

Route::get('/', function () {
    // DATA INFO
    // 1. List of 10 best players today -  tier is above 5 and 5+ battles
    // 2. List of 10 best players last 7 days - tier is above 5 and 30+ battles
    // 3. List of 10 best players last month (25 days) - tier is above 5 and 120+ battles
    // 4. List of 10 best players overall (28 days) - tier is above 5 and 500+ battles
    // 5. List of 10 best Clans
    return view('home', [
        'statistics' => [
            'topPlayersLast24Hours' => [
                ['name' => 'player 1', 'wid' => 234, 'wn8' => 2334],
                ['name' => 'player 2', 'wid' => 234, 'wn8' => 2245],
                ['name' => 'player 3', 'wid' => 234, 'wn8' => 1988],
                ['name' => 'player 4', 'wid' => 234, 'wn8' => 1800],
                ['name' => 'player 5', 'wid' => 234, 'wn8' => 1788],
                ['name' => 'player 6', 'wid' => 234, 'wn8' => 1501],
                ['name' => 'player 7', 'wid' => 234, 'wn8' => 1400],
                ['name' => 'player 8', 'wid' => 234, 'wn8' => 985],
                ['name' => 'player 9', 'wid' => 234, 'wn8' => 300],
                ['name' => 'player 10', 'wid' => 234, 'wn8' => 204],
            ],
            'topPlayersLastSevenDays' => [
                ['name' => 'player 1', 'wid' => 234, 'wn8' => 2334],
                ['name' => 'player 2', 'wid' => 234, 'wn8' => 2245],
                ['name' => 'player 3', 'wid' => 234, 'wn8' => 1988],
                ['name' => 'player 4', 'wid' => 234, 'wn8' => 1800],
                ['name' => 'player 5', 'wid' => 234, 'wn8' => 1788],
                ['name' => 'player 6', 'wid' => 234, 'wn8' => 1501],
                ['name' => 'player 7', 'wid' => 234, 'wn8' => 1400],
                ['name' => 'player 8', 'wid' => 234, 'wn8' => 985],
                ['name' => 'player 9', 'wid' => 234, 'wn8' => 300],
                ['name' => 'player 10', 'wid' => 234, 'wn8' => 204],
            ],
            'topPlayersLastMonth' => [
                ['name' => 'player 1', 'wid' => 234, 'wn8' => 2334],
                ['name' => 'player 2', 'wid' => 234, 'wn8' => 2245],
                ['name' => 'player 3', 'wid' => 234, 'wn8' => 1988],
                ['name' => 'player 4', 'wid' => 234, 'wn8' => 1800],
                ['name' => 'player 5', 'wid' => 234, 'wn8' => 1788],
                ['name' => 'player 6', 'wid' => 234, 'wn8' => 1501],
                ['name' => 'player 7', 'wid' => 234, 'wn8' => 1400],
                ['name' => 'player 8', 'wid' => 234, 'wn8' => 985],
                ['name' => 'player 9', 'wid' => 234, 'wn8' => 300],
                ['name' => 'player 10', 'wid' => 234, 'wn8' => 204],
            ],
            'topPlayersOverall' => [
                ['name' => 'player 1', 'wid' => 234, 'wn8' => 2334],
                ['name' => 'player 2', 'wid' => 234, 'wn8' => 2245],
                ['name' => 'player 3', 'wid' => 234, 'wn8' => 1988],
                ['name' => 'player 4', 'wid' => 234, 'wn8' => 1800],
                ['name' => 'player 5', 'wid' => 234, 'wn8' => 1788],
                ['name' => 'player 6', 'wid' => 234, 'wn8' => 1501],
                ['name' => 'player 7', 'wid' => 234, 'wn8' => 1400],
                ['name' => 'player 8', 'wid' => 234, 'wn8' => 985],
                ['name' => 'player 9', 'wid' => 234, 'wn8' => 300],
                ['name' => 'player 10', 'wid' => 234, 'wn8' => 204],
            ],
            'topClans' => [
                ['name' => 'clan 1', 'wid' => 234, 'wn8' => 2334],
                ['name' => 'clan 2', 'wid' => 234, 'wn8' => 2245],
                ['name' => 'clan 3', 'wid' => 234, 'wn8' => 1988],
                ['name' => 'clan 4', 'wid' => 234, 'wn8' => 1800],
                ['name' => 'clan 5', 'wid' => 234, 'wn8' => 1788],
                ['name' => 'clan 6', 'wid' => 234, 'wn8' => 1501],
                ['name' => 'clan 7', 'wid' => 234, 'wn8' => 1400],
                ['name' => 'clan 8', 'wid' => 234, 'wn8' => 985],
                ['name' => 'clan 9', 'wid' => 234, 'wn8' => 300],
                ['name' => 'clan 10', 'wid' => 234, 'wn8' => 204],
            ],
        ]
    ]);
});

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
    Route::get('/', [ClanMemberController::class, 'index']);
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
    Route::get('/', [ClanMemberController::class, 'index']);
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
