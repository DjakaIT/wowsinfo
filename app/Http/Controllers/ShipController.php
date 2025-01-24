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
                'engine' => 'https://wows-gloss-icons.wgcdn.co/icons/module/icon_module_Engine_8a3a974ed03540ecbcbff0646581c5757c2b732956189372797319a43826f504.png',
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
                'Cruiser' => 'https://wiki.wgcdn.co/images/f/f5/Wows-cruiser-icon.png',
                'Battleship' => 'https://wiki.wgcdn.co/images/2/24/Wows-battleship-icon.png',
                'Destroyer' => 'https://wiki.wgcdn.co/images/d/d2/Wows-destroyer-icon.png',
                'AirCarrier' => 'https://wiki.wgcdn.co/images/d/d8/Wows-aircarrier-icon.png',
                'Submarine' => '',
            ],
            'modulesImages' => [
                'engine' => 'https://wows-gloss-icons.wgcdn.co/icons/module/icon_module_Engine_8a3a974ed03540ecbcbff0646581c5757c2b732956189372797319a43826f504.png',
                'artillery' => 'https://wows-gloss-icons.wgcdn.co/icons/module/icon_module_Artillery_dea4595bc2cd93d9ce334c9b5a8d3d0738bd57088de2a5ac144aba65e5113e02.png',
                'torpedoes' => 'https://wows-gloss-icons.wgcdn.co/icons/module/icon_module_Torpedoes_708da4505863050c47bacaed4f081b16ad953443dbf304000fa8901c4d280234.png',
                'hull' => 'https://wows-gloss-icons.wgcdn.co/icons/module/icon_module_Hull_8b65981f2dc5ee48f07f85187e8622aec1abc2b4e9399b1c6f054d4dbf055467.png',
                'fire_control' => 'https://wows-gloss-icons.wgcdn.co/icons/module/icon_module_Suo_1c13698e19a8e9d88086d5b00361d1e3217c848ae9680b39a14310a3287f9dc9.png',
                'fighter' => 'https://wows-gloss-icons.wgcdn.co/icons/module/icon_module_Fighter_1cdc6a0ce1badb857cd67224faebbcc60ec07509433eb32d82ce76a7527ce406.png',
                'dive_bomber' => 'https://wows-gloss-icons.wgcdn.co/icons/module/icon_module_DiveBomber_d4a50f64173abc810143bebcf0b25ebbd3369707a33292044fdc1f87ba52393b.png',
                'torpedo_bomber' => 'https://wows-gloss-icons.wgcdn.co/icons/module/icon_module_TorpedoBomber_617f8f57215238afd6cf163eaa8eb886e514b1a1cb2ea9d27d996f9f3629becb.png',
                'sonar' => 'https://wows-gloss-icons.wgcdn.co/icons/module/icon_module_Sonar_4eb5e83d9d28acbe17715ffdcf5401a450bdcdca53c636f7dc1f5c72d38ed311.png',
            ],
            'nations' => [
                'usa', 'pan_asia', 'ussr', 'europe', 'japan', 'uk', 'germany', 'netherlands', 'italy', 'france', 'commonwealth', 'spain', 'pan_america'
            ],
            'types' => [
                'cruiser', 'battleship', 'destroyer', 'air_carrier', 'submarine'
            ],
        ]);
    }

    public function getWikiNationPage($nation)
    {
        $metaTitle = 'World of Warships - Battleships wiki - wows.WN8.info';
        $metaDescription = 'World of Warships battleships information wiki page';
        $metaKeywords = 'WN8, World of Warships, ship, ships, warships, warship, wiki, battleships, battleship, description, information, info, modules, configuration';

        return view('wikiNation', [
            'metaSite' => [
                'metaTitle' => $metaTitle,
                'metaDescription' => $metaDescription,
                'metaKeywords' => $metaKeywords,
            ],
            'nation' => 'usa', // Ovde ide iz parametra nacija
            'description' => 'Usa description',
            'types' => [
                'cruiser' => [
                    [
                        'name' => 'Worcester',
                        'image' => 'https://wows-gloss-icons.wgcdn.co/icons/vehicle/medium/PASC210_f6f8a7492e7a6c7a78d7e0d181b2e8b0bf9845b8d394f7795f320e0405e5e5d1.png',
                    ],
                    [
                        'name' => 'Chester',
                        'image' => 'https://wows-gloss-icons.wgcdn.co/icons/vehicle/small/PASC002_c9e819fc252eb57edac28ecc2c968de69803d66415b88a10eca629d8a869fd1e.png',
                    ]
                ],
            ],
        ]);
    }

    public function getWikiTypePage($type)
    {
        $metaTitle = 'World of Warships - Battleships wiki - wows.WN8.info';
        $metaDescription = 'World of Warships battleships information wiki page';
        $metaKeywords = 'WN8, World of Warships, ship, ships, warships, warship, wiki, battleships, battleship, description, information, info, modules, configuration';

        return view('wikiType', [
            'metaSite' => [
                'metaTitle' => $metaTitle,
                'metaDescription' => $metaDescription,
                'metaKeywords' => $metaKeywords,
            ],
            'type' => 'cruiser', // Ovde ide iz parametra nacija
            'description' => 'Cruiser description',
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
            'nations' => [
                'usa' => [
                    [
                        'name' => 'Worcester',
                        'image' => 'https://wows-gloss-icons.wgcdn.co/icons/vehicle/medium/PASC210_f6f8a7492e7a6c7a78d7e0d181b2e8b0bf9845b8d394f7795f320e0405e5e5d1.png',
                    ],
                    [
                        'name' => 'Chester',
                        'image' => 'https://wows-gloss-icons.wgcdn.co/icons/vehicle/small/PASC002_c9e819fc252eb57edac28ecc2c968de69803d66415b88a10eca629d8a869fd1e.png',
                    ]
                ],
                'germany' => [
                    [
                        'name' => 'Worcester',
                        'image' => 'https://wows-gloss-icons.wgcdn.co/icons/vehicle/medium/PASC210_f6f8a7492e7a6c7a78d7e0d181b2e8b0bf9845b8d394f7795f320e0405e5e5d1.png',
                    ],
                    [
                        'name' => 'Chester',
                        'image' => 'https://wows-gloss-icons.wgcdn.co/icons/vehicle/small/PASC002_c9e819fc252eb57edac28ecc2c968de69803d66415b88a10eca629d8a869fd1e.png',
                    ]
                ],
            ],
        ]);
    }

    public function getWikiVehiclePage($nation, $type, $ship)
    {
        $metaTitle = 'World of Warships - Battleships wiki - wows.WN8.info';
        $metaDescription = 'World of Warships battleships information wiki page';
        $metaKeywords = 'WN8, World of Warships, ship, ships, warships, warship, wiki, battleships, battleship, description, information, info, modules, configuration';

        return view('wikiVehicle', [
            'metaSite' => [
                'metaTitle' => $metaTitle,
                'metaDescription' => $metaDescription,
                'metaKeywords' => $metaKeywords,
            ],
            'name' => 'Worecaster', // Ovde ide iz ime broda
            'image' => 'https://wows-gloss-icons.wgcdn.co/icons/vehicle/large/PASC210_213441d204a03dc87729c2bd1d73efcf3af1ccafb658b1da678eabe3b990dd01.png',
            'description' => 'At the beginning of World War II, the U.S. was working on cruisers with an armored deck capable of withstanding aerial bomb attacks. The ship was to have six twin-gun turrets placed at the fore and aft in a super-firing position. It soon became clear that the biggest threat came not from \"conventional\" bombers but from dive bombers and guided bombs. As a result, the thickness of the armor deck could be reduced. In January 1945, the first ship of the class, USS Worcester, was laid down. Although plans were initially made to construct ten ships, only two of them were actually completed as World War II was drawing to a close.',
            'nation' => 'Usa',
            'type' => 'Cruiser',
            'tier' => 'X',
            'price_credit' => 30000,
            'price_gold' => 25000,
            'modules' => [
                'default' => [
                    [
                        'type' => 'Engine',
                        'name' => 'Propulsion: 120,000 hp',
                        'image' => 'https://wows-gloss-icons.wgcdn.co/icons/module/icon_module_Engine_8a3a974ed03540ecbcbff0646581c5757c2b732956189372797319a43826f504.png',
                    ],
                    [
                        'type' => 'Suo', // Gun Fire controll system
                        'name' => 'Mk10 mod. 1',
                        'image' => 'https://wows-gloss-icons.wgcdn.co/icons/module/icon_module_Suo_1c13698e19a8e9d88086d5b00361d1e3217c848ae9680b39a14310a3287f9dc9.png',
                    ],
                ],
                'upgrades' => [
                    [
                        'type' => 'Engine',
                        'name' => 'Propulsion: 120,000 hp',
                        'image' => 'https://wows-gloss-icons.wgcdn.co/icons/module/icon_module_Engine_8a3a974ed03540ecbcbff0646581c5757c2b732956189372797319a43826f504.png',
                    ],
                    [
                        'type' => 'Suo', // Gun Fire controll system
                        'name' => 'Mk10 mod. 1',
                        'image' => 'https://wows-gloss-icons.wgcdn.co/icons/module/icon_module_Suo_1c13698e19a8e9d88086d5b00361d1e3217c848ae9680b39a14310a3287f9dc9.png',
                    ],
                ],
            ],
            'performance' => [ // u performance idu svi iz default_profile koji imaju total vrednost
                'mobility' => [
                    'rudder_time' => 8.1,
                    'total' => 55,
                    'max_speed' => 33,
                    'turning_radius' => 740
                ],
                'concealment' => [
                    'detect_distance_by_plane' => 8.5,
                    'total' => 55,
                    'detect_distance_by_submarine' => 8.5,
                    'detect_distance_by_ship' => 12.1
                ],
                'survivability' => [ // ovo je armour u default_profile
                    'total' => 63,
                ],
            ],
            'armament' => [ // ovo je weaponry u default_profile
                'torpedoes' => 0,
                'aircraft' => 0,
                'artillery' => 77,
                'anti_aircraft' => 91
            ],
            'details' => [
                'hull' => [
                    'health' => 45400,
                    'anti_aircraft_barrels' => 31,
                    'artillery_barrels' => 6,
                    'atba_barrels' => 0,
                    'torpedoes_barrels' => 0,
                    'planes_amount' => 0,
                ],
                'mobility' => [
                    'rudder_time' => 8.1,
                    'total' => 55,
                    'max_speed' => 33,
                    'turning_radius' => 740
                ],
                'concealment' => [
                    'total' => 55,
                    'detect_distance_by_plane' => 8.5,
                    'detect_distance_by_submarine' => 8.5,
                    'detect_distance_by_ship' => 12.1
                ],
                'artilery' => [
                    'max_dispersion' => 148,
                    'shot_delay' => 4,
                    'rotation_time' => 7.2,
                    'distance' => 16.7,
                    'slots' => [
                        '0' => [
                            'barrels' => 2,
                            'name' => '152 mm/47 DP Mk.16 in a turret',
                            'guns' => 6,
                        ],
                    ],
                    "gun_rate" => 13,
                    'shells' => [
                        'AP' => [
                            'burn_probability' => null,
                            'bullet_speed' => 762,
                            'name' => '152 mm AP 130 lb Mk35',
                            'damage' => 3200,
                            'bullet_mass' => 59,
                            'type' => 'AP',
                        ],
                        'HE' => [
                            'burn_probability' => 12,
                            'bullet_speed' => 812,
                            'name' => '152 mm HE Mk39',
                            'damage' => 2200,
                            'bullet_mass' => 48,
                            'type' => 'HE',
                        ],
                    ],
                ],
                'atbas' => [
                    'distance' => 6.8,
                    'slots' => [
                        '0' => [
                            'burn_probability' => 5,
                            'bullet_speed' => 900,
                            'name' => '105 mm Spr.Gr. Kz.',
                            'shot_delay' => 3.4,
                            'damage' => 1200,
                            'bullet_mass' => 15,
                            'type' => 'HE',
                            'gun_rate' => 17.9,
                        ],
                    ],
                ],
                'torpedos' => [
                    'visibility_dist' => 1.9,
                    'distance' => 12.5,
                    'torpedo_name' => 'G7e T3 mod. 0',
                    'reload_time' => 58,
                    'torpedo_speed' => 73,
                    'rotation_time' => 15,
                    'slots' => [
                        '0' => [
                            'barrels' => 1,
                            'caliber' => 533,
                            'name' => '533 mm',
                            'guns' => 6,
                        ],
                    ],
                    'max_damage' => 7533
                ],
                'anti_aircraft' => [
                    'slots' => [
                        '0' => [
                            'distance' => -1,
                            'avg_damage' => null,
                            'caliber' => 20,
                            'name' => '20 mm Oerlikon on a Mk.20 mount',
                            'guns' => 12
                        ],
                        '1' => [
                            'distance' => -1,
                            'avg_damage' => null,
                            'caliber' => 76,
                            'name' => '76.2 mm/50 Mk.22 on a Mk.34 mount',
                            'guns' => 2
                        ],
                        '2' => [
                            'distance' => -1,
                            'avg_damage' => null,
                            'caliber' => 76,
                            'name' => '76.2 mm/50 Mk.22 on a Mk.33 mount',
                            'guns' => 11
                        ],
                        '3' => [
                            'distance' => -1,
                            'avg_damage' => null,
                            'caliber' => 152,
                            'name' => '152 mm/47 DP Mk.16 in a turret',
                            'guns' => 6
                        ]
                    ],
                    'defense' => 91,
                ],
                'submarine_sonar' => [
                    'wave_duration_0' => 25,
                    'wave_duration_1' => 55,
                    'wave_shot_delay' => 8,
                    'wave_max_dist' => 12.5,
                    'wave_speed_max' => 500,
                    'wave_width' => 15,
                    'total' => 62
                ],
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
