<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('player_ships', function (Blueprint $table) {
            $table->dropColumn('private_battle_life_time');
            $table->integer('club_battles')->nullable();
            $table->integer('club_wins')->nullable();
            $table->integer('club_survived_battles')->nullable();
            $table->integer('rank_battles')->nullable();
            $table->integer('rank_wins')->nullable();
            $table->integer('rank_frags')->nullable();
            $table->integer('rank_xp')->nullable();
            $table->integer('rank_survived_battles')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('player_ships', function (Blueprint $table) {
            $table->integer('private_battle_life_time');
            $table->dropColumn([
                'club_battles',
                'club_wins',
                'club_survived_battles',
                'rank_battles',
                'rank_wins',
                'rank_frags',
                'rank_xp',
                'rank_survived_battles'
            ]);
        });
    }
};
