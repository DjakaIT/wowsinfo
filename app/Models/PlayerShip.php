<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PlayerShip extends Model
{
    use HasFactory;

    protected $fillable = [
        'account_id',
        'ship_id',
        'player_name',
        'last_battle_time',
        'ship_name',
        'ship_type',
        'ship_nation',
        'ship_tier',
        'battles_played',
        'frags',
        'xp',
        'distance',
        'total_player_wn8',
        'wins_count',
        'damage_dealt',
        'average_damage',
        'created_at',
        'updated_at',
        'survival_rate',
        'capture',
        'defend',
        'spotted',
        'pve_battles',
        'pve_wins',
        'wn8',
        'pve_frags',
        'pve_xp',
        'pve_survived_battles',
        'pvp_battles',
        'pvp_wins',
        'pvp_frags',
        'pvp_xp',
        'pvp_survived_battles',
        'club_battles',
        'club_wins',
        'club_frags',
        'club_xp',
        'club_survived_battles',
        'rank_battles',
        'rank_wins',
        'rank_frags',
        'rank_xp',
        'rank_survived_battles',
    ];



    public function player()
    {
        return $this->belongsTo(Player::class);
    }

    public function ship()
    {
        return $this->belongsTo(Ship::class);
    }
}
