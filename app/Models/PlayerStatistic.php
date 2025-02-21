<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PlayerStatistic extends Model
{
    use HasFactory;

    protected $fillable = [
        'account_id',
        'nickname',
        'wn8',
        'private_battle_life_time',
        'private_gold',
        'private_port',
        'battles_played',
        'damage_dealt',
        'wins',
        'losses',
        'survived_battles',
        'frags',
        'xp',
        'distance'
    ];

    public function player()
    {
        return $this->belongsTo(Player::class);
    }



    //method to calculate win ratios

    public function winRate()
    {
        return $this->battles_played > 0 ? ($this->wins / $this->battles_played) * 100 : 0;
    }

    //method to calculate avg damage

    public function averageDamage()
    {
        return $this->battles_played > 0 ? round($this->damage_dealt / $this->battles_played) : 0;
    }




    public function scopeTopByWnScore($query, $limit = 15)
    {
        return $query->orderByDesc('wn8')->limit($limit);
    }
}
