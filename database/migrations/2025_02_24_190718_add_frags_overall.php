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
            $table->unsignedInteger('frags_overall')->nullable()->after('battles_played');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('player_ships', function (Blueprint $table) {
            $table->dropColumn('frags_overall');
        });
    }
};
