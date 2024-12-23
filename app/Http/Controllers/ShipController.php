<?php

namespace App\Http\Controllers;

use App\Models\Ship;
use Illuminate\Http\Request;
use App\Services\ShipService;
use Illuminate\Support\Facades\Log;

class ShipController extends Controller
{
    //display all ships

    protected $ShipService;

    public function __construct(ShipService $shipService)
    {
        $this->ShipService = $shipService;
    }

    public function fetchAndStoreShips(Request $request)
    {

        Log::info("Reached fetchAndStoreShips method");

        $limit = 100;
        $page = 1;
        $hasMore = true;

        while ($hasMore) {
            $ships = $this->ShipService->getShips($page, $limit);

            if ($ships && isset($ships['data'])) {
                foreach ($ships['data'] as $shipData) {
                    Ship::updateOrCreate(
                        ['ship_id' => $shipData['ship_id']],
                        [
                            'name' => $shipData['name'],
                            'nation' => $shipData['nation'],
                            'type' => $shipData['type'],
                            'tier' => $shipData['tier'],
                            'is_premium' => $shipData['is_premium'] ?? false,
                        ]
                    );

                    Log::info("Stored ship with ID: " . $shipData['ship_id']);
                }

                $page++;
                $hasMore = count($ships['data']) === $limit;
            } else {
                $hasMore = false;
            }
        }

        return response()->json(['message' => 'Ships fetched and stored in database succesfully', 201]);
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
