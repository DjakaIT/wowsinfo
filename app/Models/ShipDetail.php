<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ShipDetail extends Model
{
    // Specify the table name if it's not the default plural form of the model name
    protected $table = 'wiki_vehicles';

    // If you don't have timestamps in this table, disable them
    public $timestamps = false;

    // You can define additional properties like casts, relationships, etc.
}