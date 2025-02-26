<?php

namespace App\Http\Controllers;

use App\Services\ClanService;
use App\Services\ClanMemberService;
use Illuminate\Support\Facades\DB;
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


    // Add this new method to your existing ClanController
    public function refreshClanShipStats($clanId = null)
    {
        try {
            // If no clan_id is provided, show an error or a form to select a clan
            if (!$clanId) {
                // You could return a view here with a form to select a clan
                return view('admin.refresh-clan-stats', [
                    'clans' => \App\Models\Clan::select('clan_id', 'name', 'tag')->get()
                ]);
            }

            // Validate that the clan exists
            $clan = \App\Models\Clan::where('clan_id', $clanId)->first();
            if (!$clan) {
                return response()->json(['error' => 'Clan not found'], 404);
            }

            // Dispatch a job to fetch and store player ships for this clan
            $playerShipService = app(\App\Services\PlayerShipService::class);
            $result = $playerShipService->fetchAndStorePlayerShipsForClan($clanId);

            if ($result) {
                Log::info("Manual refresh of clan ship stats completed", ['clan_id' => $clanId]);
                return response()->json([
                    'success' => true,
                    'message' => "Stats update initiated for clan {$clan->name} [{$clan->tag}]"
                ]);
            } else {
                return response()->json(['error' => 'No players found for this clan'], 404);
            }
        } catch (\Exception $e) {
            Log::error("Error refreshing clan ship stats", [
                'clan_id' => $clanId,
                'error' => $e->getMessage()
            ]);
            return response()->json(['error' => 'Failed to refresh clan stats: ' . $e->getMessage()], 500);
        }
    }

    public function getClanPage($name, $id)
    {
        $metaTitle = "$name - WN8 clan statistics in World of Warships";
        $metaDescription = "Latest statistics for clan $name in World of Warships, WN8 daily, weekly and monthly updates and statistic.";
        $metaKeywords = "WN8, World of Warships, Statistics, Clan statistics, $name";

        // Call the service to get clan member data (including last month stats)
        $members = $this->clanMemberService->getClanMemberData($id);
        $fullName = !empty($members) ? $members[0]['fullName'] : '';

        $memberIds = collect($members)->pluck('account_id')->toArray();
        $playerShipsCount = DB::table('player_ships')
            ->whereIn('account_id', $memberIds)
            ->distinct('account_id')
            ->count('account_id');

        // If less than 80% of members have stats, trigger a background update
        if ($playerShipsCount < count($memberIds) * 0.8) {
            dispatch(new \App\Jobs\FetchClanMemberShipsJob($id));
        }

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
