<?php

namespace App\Http\Controllers;

use App\Models\Ship;
use Illuminate\Http\Request;
use App\Services\ShipService;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

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
            'modulesImages' => [
                __('wiki_engine') => 'https://wows-gloss-icons.wgcdn.co/icons/module/icon_module_Engine_8a3a974ed03540ecbcbff0646581c5757c2b732956189372797319a43826f504.png',
                'artillery' => 'https://wows-gloss-icons.wgcdn.co/icons/module/icon_module_Artillery_dea4595bc2cd93d9ce334c9b5a8d3d0738bd57088de2a5ac144aba65e5113e02.png',
                'torpedoes' => 'https://wows-gloss-icons.wgcdn.co/icons/module/icon_module_Torpedoes_708da4505863050c47bacaed4f081b16ad953443dbf304000fa8901c4d280234.png',
                'hull' => 'https://wows-gloss-icons.wgcdn.co/icons/module/icon_module_Hull_8b65981f2dc5ee48f07f85187e8622aec1abc2b4e9399b1c6f054d4dbf055467.png',
                'fire_control' => 'https://wows-gloss-icons.wgcdn.co/icons/module/icon_module_Suo_1c13698e19a8e9d88086d5b00361d1e3217c848ae9680b39a14310a3287f9dc9.png',
                'fighter' => 'https://wows-gloss-icons.wgcdn.co/icons/module/icon_module_Fighter_1cdc6a0ce1badb857cd67224faebbcc60ec07509433eb32d82ce76a7527ce406.png',
                'dive_bomber' => 'https://wows-gloss-icons.wgcdn.co/icons/module/icon_module_DiveBomber_d4a50f64173abc810143bebcf0b25ebbd3369707a33292044fdc1f87ba52393b.png',
                'torpedo_bomber' => 'https://wows-gloss-icons.wgcdn.co/icons/module/icon_module_TorpedoBomber_617f8f57215238afd6cf163eaa8eb886e514b1a1cb2ea9d27d996f9f3629becb.png',
                'sonar' => 'https://wows-gloss-icons.wgcdn.co/icons/module/icon_module_Sonar_4eb5e83d9d28acbe17715ffdcf5401a450bdcdca53c636f7dc1f5c72d38ed311.png',
            ],
            'nationImages' => [
                'usa' => 'https://wiki.wgcdn.co/images/f/f2/Wows_flag_USA.png',
                'pan_asia' => 'https://wiki.wgcdn.co/images/3/33/Wows_flag_Pan_Asia.png',
                'ussr' => 'https://wiki.wgcdn.co/images/0/04/Wows_flag_Russian_Empire_and_USSR.png',
                'europe' => 'https://wiki.wgcdn.co/images/5/52/Wows_flag_Europe.png',
                'japan' => 'https://wiki.wgcdn.co/images/5/5b/Wows_flag_Japan.png',
                'uk' => 'https://wiki.wgcdn.co/images/3/34/Wows_flag_UK.png',
                'germany' => 'https://wiki.wgcdn.co/images/6/6b/Wows_flag_Germany.png',
                'netherlands' => 'https://wiki.wgcdn.co/images/c/c8/Wows_flag_Netherlands.png',
                'italy' => 'https://wiki.wgcdn.co/images/d/d1/Wows_flag_Italy.png',
                'france' => 'https://wiki.wgcdn.co/images/7/71/Wows_flag_France.png',
                'commonwealth' => 'https://wiki.wgcdn.co/images/9/9a/Wows_flag_Commonwealth.png',
                'spain' => 'https://wiki.wgcdn.co/images/thumb/e/ea/Flag_of_Spain_%28state%29.jpg/80px-Flag_of_Spain_%28state%29.jpg',
                'pan_america' => 'https://wiki.wgcdn.co/images/9/9e/Wows_flag_Pan_America.png',
            ],
            'typeImages' => [
                'cruiser' => 'https://wiki.wgcdn.co/images/f/f5/Wows-cruiser-icon.png',
                'battleship' => 'https://wiki.wgcdn.co/images/2/24/Wows-battleship-icon.png',
                'destroyer' => 'https://wiki.wgcdn.co/images/d/d2/Wows-destroyer-icon.png',
                'aircarrier' => 'https://wiki.wgcdn.co/images/d/d8/Wows-aircarrier-icon.png',
                'submarine' => '',
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
                'Cruiser',
                'Battleship',
                'Destroyer',
                'Air Carrier',
                'Submarine'
            ],
        ]);
    }

    public function getWikiNationPage($nation)
    {
        $metaTitle = 'World of Warships - Battleships wiki - wows.WN8.info';
        $metaDescription = 'World of Warships battleships information wiki page';
        $metaKeywords = 'WN8, World of Warships, ship, ships, warships, warship, wiki, battleships, battleship, description, information, info, modules, configuration';
        $shipsByNation = Ship::where('nation', $nation)
            ->orderBy('type')
            ->with('detail') // Eager load ShipDetail relationship
            ->get()
            ->groupBy('type');
        $orderedShips = $shipsByNation->map(fn($group) => $group->map(fn($ship) => [
            'name' => $ship->name,
            'id' => $ship->id,
            'image' => optional($ship->detail)->images ? json_decode($ship->detail->images, true)['small'] : null,
        ])->values())->toArray();

        return view('wikiNation', [
            'metaSite' => [
                'metaTitle' => $metaTitle,
                'metaDescription' => $metaDescription,
                'metaKeywords' => $metaKeywords,
            ],
            'nation' => $nation, // Ovde ide iz parametra nacija
            'description' => __("_wiki_nation_{$nation}_description"),

            'types' => $orderedShips,
        ]);
    }

    public function getWikiTypePage($type)
    {
        $metaTitle = 'World of Warships - Battleships wiki - wows.WN8.info';
        $metaDescription = 'World of Warships battleships information wiki page';
        $metaKeywords = 'WN8, World of Warships, ship, ships, warships, warship, wiki, battleships, battleship, description, information, info, modules, configuration';
        $formattedType = Str::studly(str_replace('_', '', $type));
        $shipsByType = Ship::where('type', $formattedType)
            ->orderBy('nation') // Group ships by nation
            ->with('detail') // Eager load ShipDetail relationship
            ->get()
            ->groupBy('nation'); // Group ships by nation

        // Now map over the ships and prepare the data to return

        $orderedShips = $shipsByType->map(fn($group) => $group->map(fn($ship) => [
            'name' => $ship->name,
            'id' => $ship->id,
            'image' => optional($ship->detail)->images ? json_decode($ship->detail->images, true)['small'] : null,
        ])->values())->toArray();

        return view('wikiType', [
            'metaSite' => [
                'metaTitle' => $metaTitle,
                'metaDescription' => $metaDescription,
                'metaKeywords' => $metaKeywords,
            ],
            'type' => $type, // Ovde ide iz parametra nacija
            'description' =>  __('_wiki_type_' . str_replace(' ', '', ucwords(str_replace('_', ' ', $type))) . '_description'),
            'nationImages' => [
                'usa' => 'https://wiki.wgcdn.co/images/f/f2/Wows_flag_USA.png',
                'pan_asia' => 'https://wiki.wgcdn.co/images/3/33/Wows_flag_Pan_Asia.png',
                'ussr' => 'https://wiki.wgcdn.co/images/0/04/Wows_flag_Russian_Empire_and_USSR.png',
                'europe' => 'https://wiki.wgcdn.co/images/5/52/Wows_flag_Europe.png',
                'japan' => 'https://wiki.wgcdn.co/images/5/5b/Wows_flag_Japan.png',
                'uk' => 'https://wiki.wgcdn.co/images/3/34/Wows_flag_UK.png',
                'germany' => 'https://wiki.wgcdn.co/images/6/6b/Wows_flag_Germany.png',
                'netherlands' => 'https://wiki.wgcdn.co/images/c/c8/Wows_flag_Netherlands.png',
                'italy' => 'https://wiki.wgcdn.co/images/d/d1/Wows_flag_Italy.png',
                'france' => 'https://wiki.wgcdn.co/images/7/71/Wows_flag_France.png',
                'commonwealth' => 'https://wiki.wgcdn.co/images/9/9a/Wows_flag_Commonwealth.png',
                'spain' => 'https://wiki.wgcdn.co/images/thumb/e/ea/Flag_of_Spain_%28state%29.jpg/80px-Flag_of_Spain_%28state%29.jpg',
                'pan_america' => 'https://wiki.wgcdn.co/images/9/9e/Wows_flag_Pan_America.png',
            ],
            'nations' => $orderedShips,
        ]);
    }

    public function getWikiVehiclePage($nation, $type, $ship)
    {
        $metaTitle = 'World of Warships - Battleships wiki - wows.WN8.info';
        $metaDescription = 'World of Warships battleships information wiki page';
        $metaKeywords = 'WN8, World of Warships, ship, ships, warships, warship, wiki, battleships, battleship, description, information, info, modules, configuration';
        $shipId = request()->query('shipId');
        $ship = Ship::find($shipId);
        $shipDetails = $ship->detail->toArray();
        $decodedData = json_decode($shipDetails["raw_data"], true);
        // CONSTRUCT MODULES
        // Base URL for images
        $imageBaseUrl = "https://wows-gloss-icons.wgcdn.co/icons/module/icon_module_";
        $moduleImages = [
            __('wiki_engine') => 'https://wows-gloss-icons.wgcdn.co/icons/module/icon_module_Engine_8a3a974ed03540ecbcbff0646581c5757c2b732956189372797319a43826f504.png',
            'Torpedoes' => 'https://wows-gloss-icons.wgcdn.co/icons/module/icon_module_Torpedoes_708da4505863050c47bacaed4f081b16ad953443dbf304000fa8901c4d280234.png',
            'Suo' => 'https://wows-gloss-icons.wgcdn.co/icons/module/icon_module_Suo_1c13698e19a8e9d88086d5b00361d1e3217c848ae9680b39a14310a3287f9dc9.png',
            'Sonar' => 'https://wows-gloss-icons.wgcdn.co/icons/module/icon_module_Sonar_4eb5e83d9d28acbe17715ffdcf5401a450bdcdca53c636f7dc1f5c72d38ed311.png',
            'FlightControl' => 'https://wows-gloss-icons.wgcdn.co/icons/module/icon_module_Engine_8a3a974ed03540ecbcbff0646581c5757c2b732956189372797319a43826f504.png',
            'DiveBomber' => 'https://wows-gloss-icons.wgcdn.co/icons/module/icon_module_DiveBomber_d4a50f64173abc810143bebcf0b25ebbd3369707a33292044fdc1f87ba52393b.png',
            'Artillery' => 'https://wows-gloss-icons.wgcdn.co/icons/module/icon_module_Artillery_dea4595bc2cd93d9ce334c9b5a8d3d0738bd57088de2a5ac144aba65e5113e02.png',
            'Hull' => 'https://wows-gloss-icons.wgcdn.co/icons/module/icon_module_Hull_8b65981f2dc5ee48f07f85187e8622aec1abc2b4e9399b1c6f054d4dbf055467.png',
            'Fighter' => 'https://wows-gloss-icons.wgcdn.co/icons/module/icon_module_Fighter_1cdc6a0ce1badb857cd67224faebbcc60ec07509433eb32d82ce76a7527ce406.png',
            'TorpedoBomber' => 'https://wows-gloss-icons.wgcdn.co/icons/module/icon_module_TorpedoBomber_617f8f57215238afd6cf163eaa8eb886e514b1a1cb2ea9d27d996f9f3629becb.png'
        ];

        // Process modules
        $modules = [
            'default' => [],
            'upgrades' => []
        ];

        foreach ($decodedData['modules_tree'] as $module) {
            $moduleData = [
                'type' => $module['type'],
                'name' => $module['name'],
                'image' => $moduleImages[$module['type']]
            ];

            if ($module['is_default']) {
                $modules['default'][] = $moduleData;
            } else {
                $modules['upgrades'][] = $moduleData;
            }
        }

        // END CONSTRUCT MODULES
        // PEFORMANCE OBJECT CREATION
        $performance = [];

        foreach ($decodedData['default_profile'] as $key => $value) {
            if (is_array($value) && isset($value['total']) && is_numeric($value['total'])) {
                $performance[$key] = $value;
            }
        }
        // END OF PERFORMANCE OBJECT CREATION
        // dump($decodedData);
        return view('wikiVehicle', [
            'metaSite' => [
                'metaTitle' => $metaTitle,
                'metaDescription' => $metaDescription,
                'metaKeywords' => $metaKeywords,
            ],
            'name' => $decodedData['name'],
            'image' => $decodedData['images']['large'],
            'description' => __('_wiki_type_' . str_replace(' ', '', ucwords(str_replace('_', ' ', $type))) . '_description'),
            'nation' => $nation,
            'type' => $type,
            'tier' => $decodedData['tier'],
            'price_credit' => $decodedData['price_credit'],
            'price_gold' => $decodedData['price_gold'],
            'modules' => $modules,
            'performance' => $performance,
            'armament' => $decodedData['default_profile']['weaponry'], // ovo je weaponry u default_profile,
            'details' => [
                'hull' => [
                    'health' => $decodedData['default_profile']['hull']['health'],
                    'anti_aircraft_barrels' => $decodedData['default_profile']['hull']['anti_aircraft_barrels'],
                    'artillery_barrels' => $decodedData['default_profile']['hull']['artillery_barrels'],
                    'atba_barrels' => $decodedData['default_profile']['hull']['atba_barrels'],
                    'torpedoes_barrels' => $decodedData['default_profile']['hull']['torpedoes_barrels'],
                    'planes_amount' => $decodedData['default_profile']['hull']['planes_amount'],
                ],
                'mobility' => [
                    'rudder_time' => $decodedData['default_profile']['mobility']['rudder_time'],
                    'total' => $decodedData['default_profile']['mobility']['total'],
                    'max_speed' => $decodedData['default_profile']['mobility']['max_speed'],
                    'turning_radius' => $decodedData['default_profile']['mobility']['turning_radius']
                ],
                'concealment' => [
                    'total' => $decodedData['default_profile']['concealment']['total'],
                    __('_detect_ distance_by_plane') => $decodedData['default_profile']['concealment']['detect_distance_by_plane'],
                    __('_detect_ distance_by_submarine') => $decodedData['default_profile']['concealment']['detect_distance_by_submarine'],
                    __('_detect_ distance_by_ship') => $decodedData['default_profile']['concealment']['detect_distance_by_ship']
                ],
                __('wiki_artillery') => $decodedData['default_profile']['artillery'],
                'atbas' => $decodedData['default_profile']['atbas'],
                'torpedos' => $decodedData['default_profile']['torpedoes'],
                'anti_aircraft' => $decodedData['default_profile']['anti_aircraft'],
                'submarine_sonar' => $decodedData['default_profile']['submarine_sonar'],
            ],
            'nationImages' => [
                'usa' => 'https://wiki.wgcdn.co/images/f/f2/Wows_flag_USA.png',
                'pan_asia' => 'https://wiki.wgcdn.co/images/3/33/Wows_flag_Pan_Asia.png',
                'ussr' => 'https://wiki.wgcdn.co/images/0/04/Wows_flag_Russian_Empire_and_USSR.png',
                'europe' => 'https://wiki.wgcdn.co/images/5/52/Wows_flag_Europe.png',
                'japan' => 'https://wiki.wgcdn.co/images/5/5b/Wows_flag_Japan.png',
                'uk' => 'https://wiki.wgcdn.co/images/3/34/Wows_flag_UK.png',
                'germany' => 'https://wiki.wgcdn.co/images/6/6b/Wows_flag_Germany.png',
                'netherlands' => 'https://wiki.wgcdn.co/images/c/c8/Wows_flag_Netherlands.png',
                'italy' => 'https://wiki.wgcdn.co/images/d/d1/Wows_flag_Italy.png',
                'france' => 'https://wiki.wgcdn.co/images/7/71/Wows_flag_France.png',
                'commonwealth' => 'https://wiki.wgcdn.co/images/9/9a/Wows_flag_Commonwealth.png',
                'spain' => 'https://wiki.wgcdn.co/images/thumb/e/ea/Flag_of_Spain_%28state%29.jpg/80px-Flag_of_Spain_%28state%29.jpg',
                'pan_america' => 'https://wiki.wgcdn.co/images/9/9e/Wows_flag_Pan_America.png',
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
