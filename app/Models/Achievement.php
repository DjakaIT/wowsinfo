<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Achievement extends Model
{
    use HasFactory;

    protected $fillable = [
        'achievement_id',
        'achievement_name',
        'description',
        'image',
        'image_inactive',
        'type',
        'sub_type',
        'max_progress',
        'is_progress'
    ];

    public function players()
    {
        return $this->belongsToMany(Player::class, 'player_achievements');
    }
}
