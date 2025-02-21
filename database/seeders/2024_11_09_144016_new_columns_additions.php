<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
    {
        Schema::table('player_ships', function (Blueprint $table) {
            // Example of adding new columns if needed for your statistics
            $table->integer('distance')->nullable();
            $table->integer('pve_battles')->nullable();
            $table->integer('pve_wins')->nullable();
            $table->integer('pve_frags')->nullable();
            $table->integer('pve_xp')->nullable();
            $table->integer('pve_survived_battles')->nullable();
            $table->integer('pvp_battles')->nullable();
            $table->integer('pvp_wins')->nullable();
            $table->integer('pvp_frags')->nullable();
            $table->integer('pvp_xp')->nullable();
            $table->integer('pvp_survived_battles')->nullable();
            $table->integer('private_battle_life_time')->nullable();
            $table->integer('club_frags')->nullable();
            $table->integer('club_xp')->nullable();
        });
    }

    public function down()
    {
        Schema::table('player_ships', function (Blueprint $table) {
            $table->dropColumn([
                'distance',
                'pve_battles',
                'pve_wins',
                'pve_frags',
                'pve_xp',
                'pve_survived_battles',
                'pvp_battles',
                'pvp_wins',
                'pvp_frags',
                'pvp_xp',
                'pvp_survived_battles',
                'private_battle_life_time',
                'club_frags',
                'club_xp',
            ]);
        });
    }
};
