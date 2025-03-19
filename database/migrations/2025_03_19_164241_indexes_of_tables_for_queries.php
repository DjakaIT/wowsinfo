<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('player_ships', function (Blueprint $table) {
            // Check if indexes exist before creating them
            $this->addIndexIfNotExists('player_ships', 'account_id');
            $this->addIndexIfNotExists('player_ships', 'ship_tier');
            $this->addIndexIfNotExists('player_ships', 'battles_played');
            $this->addIndexIfNotExists('player_ships', 'last_battle_time');
            $this->addIndexIfNotExists('player_ships', 'total_player_wn8');
        });

        Schema::table('clans', function (Blueprint $table) {
            $this->addIndexIfNotExists('clans', 'clan_id');
            $this->addIndexIfNotExists('clans', 'clanwn8');
        });
    }

    public function down(): void
    {
        Schema::table('player_ships', function (Blueprint $table) {
            $table->dropIndexIfExists('player_ships_account_id_index');
            $table->dropIndexIfExists('player_ships_ship_tier_index');
            $table->dropIndexIfExists('player_ships_battles_played_index');
            $table->dropIndexIfExists('player_ships_last_battle_time_index');
            $table->dropIndexIfExists('player_ships_total_player_wn8_index');
        });

        Schema::table('clans', function (Blueprint $table) {
            $table->dropIndexIfExists('clans_clan_id_index');
            $table->dropIndexIfExists('clans_clanwn8_index');
        });
    }

    // Helper method to check if an index exists before creating it
    private function addIndexIfNotExists($table, $column)
    {
        $indexName = "{$table}_{$column}_index";
        $indexes = DB::select("SHOW INDEXES FROM {$table} WHERE Key_name = ?", [$indexName]);

        if (empty($indexes)) {
            Schema::table($table, function (Blueprint $table) use ($column) {
                $table->index($column);
            });
        }
    }
};
