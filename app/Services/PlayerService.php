<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use App\Models\Player;
use Illuminate\Support\Facades\Log;
use App\Models\ClanMember;
use App\Models\Clan;
use Illuminate\Support\Facades\DB;

class PlayerService
{
    protected $apiKey;
    protected $baseUrls;

    public function __construct()
    {
        $this->apiKey = config('services.wargaming.api_key');

        // Base URLs for each server
        $this->baseUrls = [
            'eu' => 'https://api.worldofwarships.eu',
            'na' => 'https://api.worldofwarships.com',
            'asia' => 'https://api.worldofwarships.asia',
        ];
    }

    public function generateSearchTerms()
    {
        $terms = [];
        foreach (range('a', 'z') as $letter) {
            $terms[] = $letter;

            foreach (range('a', 'z') as $secondLetter) {
                $terms[] = $letter . $secondLetter;

                foreach (range('a', 'z') as $thirdLetter) {
                    $terms[] = $letter . $secondLetter . $thirdLetter;
                }
            }
        }
        return $terms;
    }

    public function fetchAndStorePlayers($serverOption)
    {
        $serverUrl = $this->baseUrls[$serverOption] ?? $this->baseUrls['eu'];
        $clans = Clan::where('server', $serverOption)->get();

        foreach ($clans as $clan) {
            foreach ($clan->members as $member) {
                $response = Http::get("{$serverUrl}/wows/account/info/", [
                    'application_id' => $this->apiKey,
                    'account_id' => $member->account_id,
                ]);

                $data = $response->json();

                if ($response->failed() || !isset($data['data'][$member->account_id])) {
                    Log::warning("Failed to fetch data for account_id: {$member->account_id}");
                    continue;
                }

                $playerInfo = $data['data'][$member->account_id];

                if (is_null($playerInfo)) {
                    Log::info("Player[{$playerInfo['nickname']}] null, SKIP!!!");
                    continue;
                }

                if (Player::where('account_id', $playerInfo['account_id'])->exists()) {
                    Log::info("Player[{$playerInfo['nickname']}] exist, SKIP!!!");
                    continue;
                }

                $player = new Player([
                    'account_id' => $playerInfo['account_id'],
                    'nickname' => $playerInfo['nickname'],
                    'server' => $serverOption,
                    'last_battle_time' => $playerInfo['last_battle_time'],
                    'account_created' => $playerInfo['created_at'],
                    'clan_name' => $clan->tag,
                    'clan_id' => $clan->clan_id,
                ]);

                $player->save();
                Log::info("Stored player with nickname: {$playerInfo['nickname']}");
            }
        }

        return response()->json(['message' => 'All players fetched and stored in database successfully'], 201);
    }


    public function getAllPlayers($server, $search, $page = 1, $limit = 100)
    {
        if (!isset($this->baseUrls[$server])) {
            Log::error("Invalid server specified: {$server}");
            return null;
        }

        $url = $this->baseUrls[$server] . "/wows/account/list/";
        try {
            $response = Http::get($url, [
                'application_id' => $this->apiKey,
                'search' => $search,
                'page_no' => $page,
                'limit' => $limit,
            ]);

            if ($response->failed()) {
                Log::error("Player API Request failed for server: {$server} with status: " . $response->status());
                return null;
            }

            $responseData = $response->json();

            if ($responseData['status'] === 'ok' && isset($responseData['data'])) {
                return $responseData['data'];
            } else {
                Log::error("Unexpected API response for server: {$server}", ['response' => $responseData]);
            }
        } catch (\Exception $e) {
            Log::error("Exception during Player API call: " . $e->getMessage());
        }

        return null;
    }

    public function fetchAccountCreatedDate($server, $accountId)
    {
        $url = $this->baseUrls[$server] . "/wows/account/info/";
        try {
            $response = Http::get($url, [
                'application_id' => $this->apiKey,
                'account_id' => $accountId,
            ]);

            if ($response->failed()) {
                Log::error("Account Info API Request failed for server: {$server} with status: " . $response->status());
                return null;
            }

            $responseData = $response->json();

            if ($responseData['status'] === 'ok' && isset($responseData['data'][$accountId]['created_at'])) {
                // Convert Unix timestamp to DATETIME format
                $createdAt = date('Y-m-d H:i:s', $responseData['data'][$accountId]['created_at']);

                // Update the database
                Player::where('account_id', $accountId)->update(['account_created' => $createdAt]);

                return $createdAt;
            } else {
                Log::error("Unexpected Account Info response for server: {$server}", ['response' => $responseData]);
            }
        } catch (\Exception $e) {
            Log::error("Exception during Account Info API call: " . $e->getMessage());
        }

        return null;
    }
}
