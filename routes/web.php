<?php

use Illuminate\Support\Facades\Route;

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
