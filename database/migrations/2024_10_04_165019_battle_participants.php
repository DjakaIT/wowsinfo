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
        Schema::create('battle_participants', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('battle_id')->unique();
            $table->foreignId('player_id')->constrained('players')->onDelete('cascade');
            $table->foreignId('ship_id')->constrained('ships')->onDelete('cascade');
            $table->integer('duration');
            $table->enum('team', ['A', 'B']);
            $table->boolean('victory');
            $table->integer('damage_dealt');
            $table->integer('frags');
            $table->integer('xp_earned');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('battle_participants');
    }
};
