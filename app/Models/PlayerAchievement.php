<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PlayerAchievement extends Model
{
    use HasFactory;

    protected $fillable = [
        'account_id',
        'achievement_id',
        'achievement_name',
        'achievement_type',
        'achievement_count'
    ];


    public function playerAchievements()
    {
        return $this->belongsToMany(Achievement::class, 'player_achievements');
    }
}
