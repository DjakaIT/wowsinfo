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


    public function getClanWN8()
    {
        $this->ClanService->calculateClanWN8();
    }
    public function fetchAndStoreClans()
    {
        $result = $this->ClanService->fetchAndStoreClans();
        return response()->json($result, 201);
    }
}
