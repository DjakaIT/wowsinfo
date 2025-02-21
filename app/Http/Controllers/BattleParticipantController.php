<?php

namespace App\Http\Controllers;

use App\Models\BattleParticipant;
use Illuminate\Http\Request;

class BattleParticipantController extends Controller
{
    public function index()
    {
        $battleParticipant = BattleParticipant::all();
        return response()->json($battleParticipant);
    }

    public function show($id)
    {
        $battleParticipant = BattleParticipant::findOrFail($id);
        return response()->json($battleParticipant);
    }

    public function store(Request $request)
    {
        $validatedNewData = $request->validate([
            'player_id' => 'required|integer|unique:players, player_id',
            'battle_id' => 'required|integer|unique:battles,battle_id',
            'ship_id' => 'required|integer|unique:ships,ship_id',
            'duration' => 'required|integer',
            'team' => 'required|in:A,B',
            'victory' => 'required|boolean',
            'damage_dealt' => 'required|',
            'frags' => 'required|integer',
            'xp_earned' => 'required|float'
        ]);


        $battleParticipant = BattleParticipant::create($validatedNewData);
        return response()->json($battleParticipant, 201);
    }

    public function update(Request $request, $id)
    {

        $battleParticipant = BattleParticipant::findOrFail($id);

        $validatedUpdateData = $request->validate([
            'player_id' => 'required|integer|unique:players, player_id',
            'battle_id' => 'required|integer|unique:battles,battle_id',
            'ship_id' => 'required|integer|unique:ships,ship_id',
            'duration' => 'required|integer',
            'team' => 'required|in:A,B',
            'victory' => 'required|boolean',
            'damage_dealt' => 'required|integer',
            'frags' => 'required|integer',
            'xp_earned' => 'required|float'
        ]);


        $battleParticipant->update($validatedUpdateData);
        return response()->json($battleParticipant);
    }

    public function destroy($id)
    {
        $battleParticipant = BattleParticipant::findOrFail($id);
        $battleParticipant->delete();

        return response()->json(['message' => "Battle Participant's stats deleted succesfully from records."]);
    }
}
