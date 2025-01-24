<?php

namespace App\Http\Controllers;

use App\Services\ClanService;
use Illuminate\Support\Facades\Log;

class ClanController extends Controller
{
    protected $ClanService;

    public function __construct(ClanService $clanService)
    {
        $this->ClanService = $clanService;
    }

    public function fetchAndStoreClans()
    {
        $result = $this->ClanService->fetchAndStoreClans();
        return response()->json($result, 201);
    }

    public function getClanPage($name, $id)
    {
        $metaTitle = "$name - WN8 clan statistics in World of Warships";
        $metaDescription = "Latest statistics for clan $name in World of Warships, WN8 daily, weekly and monthly updates and statistic.";
        $metaKeywords = "WN8, World of Warships, Statistics, Clan statistics, $name";

        return view('clan', [
            'metaSite' => [
                'metaTitle' => $metaTitle,
                'metaDescription' => $metaDescription,
                'metaKeywords' => $metaKeywords,
            ],
            'shortName' => $name,
            'fullName' => 'clanFullName',
            'clanDescription' => 'Clan description',
            'members' => [
                [
                    'name' => 'Member name 1',
                    'wn8Month' => 2454,
                    'battlesMonth' => 35,
                    'wn8' => 2532,
                    'winRate' => 56.43,
                    'battles' => 1243,
                    'lastBattle' => '12/1/2015',
                    'position' => 'Private',
                    'joined' => '5/7/2024',
                ],
                [
                    'name' => 'Member name 2',
                    'wn8Month' => 1932,
                    'battlesMonth' => 23,
                    'wn8' => 2304,
                    'winRate' => 47.15,
                    'battles' => 943,
                    'lastBattle' => '15/1/2015',
                    'position' => 'Private',
                    'joined' => '2/5/2024',
                ],
            ],
        ]);
    }
}
