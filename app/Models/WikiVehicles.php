<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class WikiVehicles extends Model
{

    protected $table = 'wiki_vehicles';
    protected $fillable = [
        'ship_id',
        'images',
        'tier',
        'name',
        'nation',
        'type',
        'raw_data'
    ];
}
