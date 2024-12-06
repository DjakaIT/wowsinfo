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
