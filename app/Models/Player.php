<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Player extends Model
{
    use HasFactory;

    protected $fillable = [
        'account_id',
        'nickname',
        'server',
        'clan_id',
        'total_player_wn8',
        'account_created',
        'clan_name'
    ];


    //defines the relationship with Clan table
    public function clan()
    {
        return $this->BelongsTo(Clan::class, 'clan_id', 'clan_id');
    }

    public function achievements()
    {
        return $this->belongsToMany(Achievement::class, 'player_achievements');
    }

    public function battles()
    {
        return $this->belongsToMany(Battle::class, 'battle_participants');
    }



    //filter players by server

    public function scopeByServer($query, $server)
    {
        return $query->where('server', $server);
    }
}
