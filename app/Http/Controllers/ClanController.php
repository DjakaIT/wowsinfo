<?php

namespace App\Http\Controllers;

use App\Models\Clan;
use Illuminate\Http\Request;
use App\Services\ClanService;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class ClanController extends Controller
{
    protected $ClanService;

    public function __construct(ClanService $clanService)
    {
        $this->ClanService = $clanService;
    }

    public function fetchAndStoreClans()
    {
        Log::info("Reached fetchAndStoreClans method");

        $servers = ['eu', 'na', 'asia'];
        $limit = 100;
        foreach ($servers as $server) {
            $page = 1;
            $hasMore = true;

            Log::info("Fetching clans from server: " . strtoupper($server));

            while ($hasMore) {
                $clans = $this->ClanService->getClans($server, $page, $limit);

                if ($clans && isset($clans['data'])) {
                    foreach ($clans['data'] as $clanData) {

                        $clanCreated = isset($clanData['created_at']) ? Carbon::createFromTimestamp($clanData['created_at'])->toDateTimeString() : null;
                        $membersCount = isset($clanData['members_count']) ? (int) $clanData['members_count'] : null;
                        Clan::updateOrCreate(
                            ['clan_id' => $clanData['clan_id']],
                            [
                                'name' => $clanData['name'],
                                'tag' => $clanData['tag'],
                                'server' => strtoupper($server),
                                'clan_created' => $clanCreated,
                                'members_count' => $membersCount
                            ]
                        );
                        Log::info("Stored clan with ID: " . $clanData['clan_id'] . " on server: " . strtoupper($server));
                    }


                    Log::info("Fetched page {$page} from server: " . strtoupper($server));

                    $page++;
                    $hasMore = count($clans['data']) === $limit;
                } else {
                    $hasMore = false;
                    Log::warning("No more clans to fetch from server: " . strtoupper($server));
                }
            }
        }

        return response()->json(['message' => 'Clans fetched and stored successfully'], 201);
    }
}
