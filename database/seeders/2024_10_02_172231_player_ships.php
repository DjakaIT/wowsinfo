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
        Schema::create('player_ships', function (Blueprint $table) {
            $table->id();
            $table->foreignId('player_id')->constrained('players')->onDelete('cascade');
            $table->foreignId('ship_id')->constrained('ships')->onDelete('cascade');
            $table->integer('battles_played');
            $table->integer('wins_count');
            $table->bigInteger('damage_dealt');
            $table->bigInteger('average_damage');
            $table->integer('frags');
            $table->float('survival_rate');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('player_ships');
    }
};
