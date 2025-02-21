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
        Schema::table('player_statistics', function (Blueprint $table) {
            $table->dropColumn([
                'players_kills',
                'avg_xp',
                'win_rate',
                'wn8'
            ]);
            $table->string('nickname', 255)->nullable();
            $table->integer('private_battle_life_time');
            $table->float('private_gold');
            $table->integer('private_port');
            $table->integer('losses');
            $table->integer('survived_battles');
            $table->integer('frags');
            $table->float('xp');
            $table->float('distance');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('player_statistics', function (Blueprint $table) {
            $table->integer('players_kills');
            $table->float('avg_xp');
            $table->float('win_rate');
            $table->bigInteger('wn8');

            $table->dropColumn([
                'nickname',
                'private_battle_life_time',
                'private_gold',
                'private_port',
                'losses',
                'survived_batles',
                'frags',
                'xp',
                'distance'
            ]);
        });
    }
};
