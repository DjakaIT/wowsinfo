<?php
/*
DROP DATABASE `wows-laravel`;
CREATE DATABASE `wows-laravel`;
USE `wows-laravel`;
*/

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Session;
use App\Http\Controllers\ClanController;
use App\Http\Controllers\PlayerController;
use App\Http\Controllers\ShipController;
use App\Http\Controllers\ClanMemberController;
use App\Http\Controllers\AchievementController;
use App\Http\Controllers\PlayerAchievementController;
use App\Http\Controllers\PlayerShipController;
use App\Http\Controllers\PlayerStatisticController;

// WEB ROUTES
// Homepage
Route::get('/', [PlayerShipController::class, 'getHomePageStats'])->name('home');
// Dashboard
Route::view('/dashboard', 'dashboard', [
    'metaSite' => [
        'metaTitle' => 'Dashboard - wows.wn8.info',
        'metaDescription' => 'Dashboard on wows.wn8.info',
        'metaKeywords' => 'WN8, World of Warships, Statistics, Player statistics, Dashboard',
    ],
])->name('dashboard');
// Login
Route::view('/login', 'login', [
    'metaSite' => [
        'metaTitle' => 'Login - wows.wn8.info',
        'metaDescription' => 'Login on wows.wn8.info',
        'metaKeywords' => 'WN8, World of Warships, Statistics, Player statistics, Login',
    ],
]);
Route::get('/verification', function (Request $request) {
    if ($request->query('status') === 'ok') {

        return view('verification', [
            'metaSite' => [
                'metaTitle' => 'Verification - wows.wn8.info',
                'metaDescription' => 'Verification on wows.wn8.info',
                'metaKeywords' => 'WN8, World of Warships, Statistics, Player statistics, Verification',
            ],
            'nickname' => $request->query('nickname'),
            'account_id' => $request->query('account_id'),
            'access_token' => $request->query('access_token'),
            'expires_at' => $request->query('expires_at')
        ]);
    }

    // If login failed, redirect to login page
    return redirect()->route('login')->with('error', 'Login failed. Please try again.');
});
// Player page
Route::get('/{locale}/{server}/player/{name}/{id}', [PlayerShipController::class, 'getPlayerPageStats'])
    ->name('player.page')
    ->where('locale', implode('|', config('app.available_locales')))
    ->where('server', 'eu|na|asia');

// Clan page
Route::get('/{locale}/{server}/clan/{name}/{id}', [ClanController::class, 'getClanPage'])
    ->name('clan.page')
    ->where('locale', implode('|', config('app.available_locales')))
    ->where('server', 'eu|na|asia');

// Wiki - group 
Route::prefix('{locale}/{server}/wiki')->group(function () {
    Route::get('/', [ShipController::class, 'getWikiHomePage'])->name('wiki.home');
    Route::get('/nation/{nation}', [ShipController::class, 'getWikiNationPage'])->name('wiki.nation');
    Route::get('/type/{type}', [ShipController::class, 'getWikiTypePage'])->name('wiki.type');
    Route::get('/vehicle/{nation}/{type}/{ship}/{shipId}', [ShipController::class, 'getWikiVehiclePage'])->name('wiki.vehicle');
})->where([
    'locale' => implode('|', config('app.available_locales')),
    'server' => 'eu|na|asia'
]);

// Add a redirect for the old wiki URLs
Route::redirect('/wiki', '/' . app()->getLocale() . '/eu/wiki');
Route::redirect('/wiki/nation/{nation}', '/' . app()->getLocale() . '/eu/wiki/nation/{nation}');
Route::redirect('/wiki/type/{type}', '/' . app()->getLocale() . '/eu/wiki/type/{type}');
// FAQ
Route::view('/faq', 'faq', [
    'metaSite' => [
        'metaTitle' => 'Frequently asked questions - wows.wn8.info',
        'metaDescription' => 'Frequently asked questions on wows.wn8.info',
        'metaKeywords' => 'WN8, World of Warships, Statistics, Player statistics, FAQ',
    ],
]);
// Imprint
Route::view('/imprint', 'imprint', [
    'metaSite' => [
        'metaTitle' => 'Imprint - wows.wn8.info',
        'metaDescription' => 'Imprint and terms on wows.wn8.info',
        'metaKeywords' => 'WN8, World of Warships, Statistics, Player statistics, Imprint',
    ],
]);
// Privacy
Route::view('/privacy', 'privacy', [
    'metaSite' => [
        'metaTitle' => 'Privacy policy - wows.wn8.info',
        'metaDescription' => 'Privacy policy for wows.wn8.info',
        'metaKeywords' => 'WN8, World of Warships, Statistics, Player statistics, Privacy policy',
    ],
]);
// Contact
Route::view('/contact', 'contact', [
    'metaSite' => [
        'metaTitle' => 'Contact - wows.wn8.info',
        'metaDescription' => 'Contact information for wows.wn8.info',
        'metaKeywords' => 'WN8, World of Warships, Statistics, Player statistics, Contact',
    ],
]);

//Server
Route::get('/server/{server}', function ($server) {
    $validServer = in_array(strtolower($server), ['eu', 'na', 'asia']) ? strtolower($server) : 'eu';

    $currentUrl = url()->previous();
    $locale = app()->getLocale();

    // Update the regex pattern to include wiki pages
    if (preg_match('#/[a-z]{2}/(eu|na|asia)/(player|clan|wiki)(.*)#', $currentUrl, $matches)) {
        $type = $matches[2]; // player, clan, or wiki
        $rest = $matches[3]; // The rest of the URL (including slashes)
        return redirect("/$locale/$server/$type$rest");
    }

    // Otherwise use the old behavior
    session(['server' => $validServer]);
    return redirect()->back();
});

Route::get('locale/{locale}', function ($locale) {
    if (in_array($locale, array_values(config('app.available_locales')))) {
        $currentUrl = url()->previous();
        $server = strtolower(session('server', 'eu'));

        // If we're on a player, clan, or wiki page, redirect to the new URL format with updated locale
        if (preg_match('#/[a-z]{2}/(eu|na|asia)/(player|clan|wiki)(.*)#', $currentUrl, $matches)) {
            $server = $matches[1];
            $type = $matches[2]; // player, clan, or wiki
            $rest = $matches[3]; // The rest of the URL (including slashes)
            return redirect("/$locale/$server/$type$rest");
        }

        // Otherwise use the old behavior
        Session::put('locale', $locale);
        app()->setLocale($locale);
        return redirect()->back();
    }

    return redirect()->back();
});
//START OF BACKEND ROUTES

//TO DO: API PARAMETERS FOR SHIP ROUTE

Route::post('/api/player/lookup', [PlayerShipController::class, 'getAccountIdByUsername'])->name('api.player.lookup');


Route::prefix('clans')->group(function () {

    Route::get('/fetch', [ClanController::class, 'fetchAndStoreClans']);
    // Route::get('/', [ClanController::class, 'index'])->name('clan.page');
    Route::get('/getwn8', [ClanController::class, 'getClanWN8']);
    Route::get('/{id}', [ClanController::class, 'show']);
    Route::post('/', [ClanController::class, 'store']);
    Route::put('/{id}', [ClanController::class, 'update']);
    Route::delete('/{id}', [ClanController::class, 'destroy']);
});

Route::prefix('players')->group(function () {

    Route::get('/fetch', [PlayerController::class, 'updatePlayers']);
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
    Route::get('/overall', [PlayerShipController::class, 'updateOverallPlayerShipStats']);
    Route::get('/null-names', [PlayerShipController::class, 'getNullNames']);
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
//END OF BACKEND ROUTES