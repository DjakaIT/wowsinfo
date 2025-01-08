<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ClanMember extends Model
{
    use HasFactory;

    protected $fillable = [
        'account_id',
        'account_name',
        'joined_at',
        'left_at',
        'role',
        'clan_id',
        'clan_name',
        'total_clan_wn8',
        'account_created'
    ];

    protected $dates = [
        'joined_at',
        'left_at'
    ];



    public function scopeByActive($query)
    {
        return $query->whereNull('left_at');
    }

    public function clan()
    {
        return $this->BelongsTo(Clan::class, 'clan_id', 'clan_id');
    }
}
