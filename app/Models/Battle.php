<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Battle extends Model
{
    use HasFactory;

    protected $fillable = [
        'battle_id',
        'battle_date',
        'duration',
        'map_name',
        'battle_type'
    ];


    public function players()
    {
        return $this->belongsToMany(Player::class, 'battle_participants');
    }


    //filter battles by result
    public function scopeByResult($query, $result)
    {
        return $query->where('result', $result);
    }

    public function scopeByType($query, $type)
    {
        return $query->where('battle_type', $type);
    }

    public function scopeByMap($query, $map)
    {
        return $query->where('map_name', $map);
    }

    public function getMVP() {}
}
