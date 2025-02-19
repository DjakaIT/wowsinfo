<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Ship extends Model
{
    use HasFactory;


    protected $fillable = [

        'ship_id',
        'name',
        'tier',
        'type',
        'nation'
    ];


    public function player()
    {
        return $this->hasMany(PlayerShip::class);
    }

    public function Battle()
    {
        return $this->hasMany(BattleParticipant::class);
    }

    public function scopeByTier($query, $tier)
    {
        return $query->where('tier', $tier);
    }

    public function scopeByNation($query, $nation)
    {
        return $query->where('nation', $nation);
    }

    // Zlaja dodavao
    public function detail()
    {
        return $this->hasOne(ShipDetail::class, 'ship_id', 'ship_id');
    }
}
