<?php

namespace App\Http\Controllers;

use App\Models\Player;
use Illuminate\Http\Request;
use App\Services\PlayerService;
use Illuminate\Support\Facades\Log;

class PlayerController extends Controller
{
    protected $PlayerService;

    public function __construct(PlayerService $playerService)
    {
        $this->PlayerService = $playerService;
    }

    public function updatePlayers($server, $search, $page = 1, $limit = 100)
    {
        $this->PlayerService->fetchAndStorePlayers($server, $search, $page, $limit);
        return response()->json(['message' => 'Method "fetchAndStorePlayers started succesfully.']);
    }
}
