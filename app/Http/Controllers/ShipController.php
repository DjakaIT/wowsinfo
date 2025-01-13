<?php

namespace App\Http\Controllers;

use App\Models\Ship;
use Illuminate\Http\Request;
use App\Services\ShipService;
use Illuminate\Support\Facades\Log;

class ShipController extends Controller
{
    // Display all ships

    protected $ShipService;

    public function __construct(ShipService $shipService)
    {
        $this->ShipService = $shipService;
    }

    public function fetchAndStoreShips()
    {
        Log::info("Starting ship fetch process");

        try {
            $limit = 100;
            $page = 1;
            $totalShips = 0;
            $hasMore = true;

            while ($hasMore) {
                $response = $this->ShipService->getShips($page, $limit);

                if (!$response || !isset($response['data'])) {
                    Log::error("Failed to fetch ships for page {$page}");
                    break;
                }

                $ships = $response['data'];

                foreach ($ships as $shipData) {
                    try {
                        Ship::updateOrCreate(
                            ['ship_id' => $shipData['ship_id']],
                            [
                                'name' => $shipData['name'] ?? 'Unknown',
                                'nation' => $shipData['nation'] ?? 'Unknown',
                                'type' => $shipData['type'] ?? 'Unknown',
                                'tier' => $shipData['tier'] ?? 0,
                                'is_premium' => $shipData['is_premium'] ?? false,
                            ]
                        );

                        $totalShips++;
                    } catch (\Exception $e) {
                        Log::error("Error storing ship", [
                            'ship_id' => $shipData['ship_id'] ?? 'unknown',
                            'error' => $e->getMessage()
                        ]);
                        continue;
                    }
                }

                // Check if we should continue pagination
                $hasMore = count($ships) === $limit;
                $page++;

                Log::info("Processed page {$page}, total ships so far: {$totalShips}");
            }

            Log::info("Ship fetch process completed", ['total_ships' => $totalShips]);
            return response()->json([
                'message' => 'Ships fetched and stored successfully',
                'total_ships' => $totalShips
            ], 201);
        } catch (\Exception $e) {
            Log::error("Fatal error in fetchAndStoreShips", [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'message' => 'Error fetching ships',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    // WIKI ROUTES
    // Ovo bi trebalo u kontroler za wiki
    public function getWikiHomePage()
    {
        $metaTitle = 'World of Warships - Battleships wiki - wows.WN8.info';
        $metaDescription = 'World of Warships battleships information wiki page';
        $metaKeywords = 'WN8, World of Warships, ship, ships, warships, warship, wiki, battleships, battleship, description, information, info, modules, configuration';

        return view('wiki', [
            'metaSite' => [
                'metaTitle' => $metaTitle,
                'metaDescription' => $metaDescription,
                'metaKeywords' => $metaKeywords,
            ],
            'nations' => [
                'usa',
                'pan_asia',
                'ussr',
                'europe',
                'japan',
                'uk',
                'germany',
                'netherlands',
                'italy',
                'france',
                'commonwealth',
                'spain',
                'pan_america'
            ],
            'types' => [
                'cruiser',
                'battleship',
                'destroyer',
                'air_carrier',
                'submarine'
            ],
        ]);
    }

    public function getWikiNationPage()
    {
        $metaTitle = 'World of Warships - Battleships wiki - wows.WN8.info';
        $metaDescription = 'World of Warships battleships information wiki page';
        $metaKeywords = 'WN8, World of Warships, ship, ships, warships, warship, wiki, battleships, battleship, description, information, info, modules, configuration';

        return view('wiki', [
            'metaSite' => [
                'metaTitle' => $metaTitle,
                'metaDescription' => $metaDescription,
                'metaKeywords' => $metaKeywords,
            ],
            'nations' => [
                'usa',
                'pan_asia',
                'ussr',
                'europe',
                'japan',
                'uk',
                'germany',
                'netherlands',
                'italy',
                'france',
                'commonwealth',
                'spain',
                'pan_america'
            ],
            'types' => [
                'cruiser',
                'battleship',
                'destroyer',
                'air_carrier',
                'submarine'
            ],
        ]);
    }

    public function index()
    {
        $ships = Ship::all();
        return response()->json($ships);
    }

    //display particular ships
    public function show($id)
    {
        $ships = Ship::findOrFail($id);
        return response()->json($ships);
    }


    public function displayWiki()
    {
        return view('wiki.index');
    }
    //return a nation
    public function showNation($nation)
    {
        return view('wiki.ship-nation', compact('nation'));
    }


    //return type of the ships
    public function showType($type)
    {
        return view('wiki.ship-type', compact('type'));
    }

    //return full ship and its details
    public function showShip($nation, $type, $ship)
    {
        return view('wiki.ship-details', compact('nation', 'type', 'ship'));
    }




    //save a ship
    public function store(Request $request)
    {

        $validatedShipData = $request->validate([
            'name' => 'required|string|max:150',
            'tier' => 'required|integer',
            'type' => 'required|integer',
            'nation' => 'required|string|max:80',
            'ship_id' => 'required|integer|unique:ships, ship_id'
        ]);

        $ship = Ship::create($validatedShipData);
        return response()->json($ship, 201);
    }

    public function update(Request $request, $id)
    {
        $ship = Ship::findOrFail($id);

        $validatedUpdatedShipData = $request->validate([
            'name' => 'required|string|max:150',
            'tier' => 'required|integer',
            'type' => 'required|integer',
            'nation' => 'required|string|max:80',
            'ship_id' => 'required|unique:ships, ship_id'
        ]);


        $ship->update($validatedUpdatedShipData);
        return response()->json($ship);
    }

    //delete a ship

    public function destroy($id)
    {
        $ship = Ship::findOrFail($id);
        $ship->delete();

        return response()->json(['message' => 'Ship deleted succesfully from records']);
    }
}
