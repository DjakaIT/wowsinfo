<?php

namespace App\Http\Controllers;

use App\Services\ClanMemberService;
use Illuminate\Support\Facades\Log;
use Illuminate\Http\Request;

class ClanMemberController extends Controller
{
    protected $clanMemberService;

    public function __construct(ClanMemberService $clanMemberService)
    {
        $this->clanMemberService = $clanMemberService;
    }

    public function fetchCreationDate($player, $serverKey)
    {
        Log::info("Starting account creation date fetch process");

        try {
            $startTime = now();
            $this->clanMemberService->fetchAccountCreationDate($player, $serverKey);
            $endTime = now();

            $duration = $startTime->diffInSeconds($endTime);

            Log::info("Successfully completed account creation date fetch", [
                'duration_seconds' => $duration
            ]);

            return response()->json([
                'message' => 'Account creation date has been successfully fetched.',
                'duration_seconds' => $duration
            ], 200);
        } catch (\Exception $e) {
            Log::error("Failed to fetch account creation date", [
                'error' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'error' => 'Failed to fetch account creation date',
                'message' => $e->getMessage()
            ], 500);
        }
    }
    public function updateClanMembers()
    {
        Log::info("Starting clan members update process");

        try {
            $startTime = now();
            $this->clanMemberService->fetchAndStoreClanMembers();
            $endTime = now();

            $duration = $startTime->diffInSeconds($endTime);

            Log::info("Successfully completed clan members update", [
                'duration_seconds' => $duration
            ]);

            return response()->json([
                'message' => 'Clan members data has been successfully updated.',
                'duration_seconds' => $duration
            ], 200);
        } catch (\Exception $e) {
            Log::error("Failed to update clan members", [
                'error' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'error' => 'Failed to update clan members',
                'message' => $e->getMessage()
            ], 500);
        }
    }
}
