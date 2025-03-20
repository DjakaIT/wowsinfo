<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up()
    {
        // Create a temporary table approach - works in MySQL
        DB::statement('CREATE TEMPORARY TABLE temp_player_ships AS
                      SELECT MIN(id) as id
                      FROM player_ships
                      GROUP BY account_id, ship_id');

        // Delete records not in our temporary table
        DB::statement('DELETE FROM player_ships
                      WHERE id NOT IN (SELECT id FROM temp_player_ships)');

        // Drop the temporary table
        DB::statement('DROP TEMPORARY TABLE IF EXISTS temp_player_ships');

        // Then add the constraint
        Schema::table('player_ships', function (Blueprint $table) {
            $table->unique(['account_id', 'ship_id'], 'player_ship_unique');
        });
    }

    public function down()
    {
        Schema::table('player_ships', function (Blueprint $table) {
            $table->dropUnique('player_ship_unique');
        });
    }
};
