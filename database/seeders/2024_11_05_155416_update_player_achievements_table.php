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
        Schema::table('player_achievements', function (Blueprint $table) {
            $table->dropForeign(['player_id']);
            $table->dropColumn(['player_id', 'date_earned', 'count']);
            $table->string('achievement_name');
            $table->string('achievement_type');
            $table->integer('achievement_count');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('player_achievements', function (Blueprint $table) {
            $table->integer('player_id');
            $table->string('description');
            $table->integer('count');
            $table->dropColumn(['achievement_name', 'achievement_type', 'achievement_count']);
            $table->foreign('player_id')->references('id')->on('players');
        });
    }
};
