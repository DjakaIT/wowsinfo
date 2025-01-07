<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Clan extends Model
{
    use HasFactory;


    protected $fillable = [
        'name',
        'tag',
        'server',
        'clan_id',
        'members_count',
        'clan_created'
    ];

    //defines a OneToMany relationship with players table in the db
    public function players()
    {
        return $this->hasMany(Player::class, 'clan_id', 'clan_id');
    }

    public function battles()
    {
        return $this->hasMany(Battle::class);
    }
}
