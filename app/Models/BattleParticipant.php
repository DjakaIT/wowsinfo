<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BattleParticipant extends Model
{
    use HasFactory;

    protected $fillable = [
        'player_id',
        'battle_id',
        'ship_id',
        'duration',
        'team',
        'victory',
        'damage_dealt',
        'frags',
        'xp_earned'
    ];

    public function player()
    {
        return $this->belongsTo(Player::class);
    }

    public function ship()
    {
        return $this->belongsTo(Ship::class);
    }


    public function battle()
    {
        return $this->belongsTo(Battle::class);
    }
}
