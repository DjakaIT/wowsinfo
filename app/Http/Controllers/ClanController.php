<?php

namespace App\Http\Controllers;

use App\Services\ClanService;
use App\Services\ClanMemberService;
use Illuminate\Support\Facades\Log;

class ClanController extends Controller
{
    protected $ClanService;
    protected $clanMemberService;

    public function __construct(ClanService $clanService, ClanMemberService $clanMemberService)
    {
        $this->ClanService = $clanService;
        $this->clanMemberService = $clanMemberService;
    }

    public function getClanWN8()
    {
        $this->ClanService->calculateClanWN8();
    }

    public function fetchAndStoreClans()
    {
        $result = $this->ClanService->fetchAndStoreClans();
        return response()->json($result, 201);
    }

    public function getClanMemberStats() {}

    public function getClanPage($name, $id)
    {
        $metaTitle = "$name - WN8 clan statistics in World of Warships";
        $metaDescription = "Latest statistics for clan $name in World of Warships, WN8 daily, weekly and monthly updates and statistic.";
        $metaKeywords = "WN8, World of Warships, Statistics, Clan statistics, $name";

        // Call the service to get clan member data (including last month stats)
        $members = $this->clanMemberService->getClanMemberData($id);
        $fullName = !empty($members) ? $members[0]['fullName'] : '';

        return view('clan', [
            'metaSite' => [
                'metaTitle' => $metaTitle,
                'metaDescription' => $metaDescription,
                'metaKeywords' => $metaKeywords,
            ],
            'shortName' => $name,
            'fullName' => $fullName,
            'clanDescription' => 'Clan description',
            'members' => $members,
        ]);
    }
}
