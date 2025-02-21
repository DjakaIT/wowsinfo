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
            $table->unsignedInteger('battles_overall')->nullable()->after('battles_played');
            $table->unsignedInteger('survived_overall')->nullable()->after('battles_overall');
            $table->unsignedInteger('wins_count_overall')->nullable()->after('survived_overall');
            $table->unsignedBigInteger('damage_overall')->nullable()->after('wins_count_overall');
            // “dropped_capture_points” from API will be saved as defended_overall
            $table->unsignedInteger('defended_overall')->nullable()->after('damage_overall');
            // “capture_points” from API will be saved as captured_overall
            $table->unsignedInteger('captured_overall')->nullable()->after('defended_overall');
            $table->unsignedInteger('xp_overall')->nullable()->after('captured_overall');
            $table->unsignedInteger('spotted_overall')->nullable()->after('xp_overall');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('player_ships', function (Blueprint $table) {
            $table->dropColumn([
                'battles_overall',
                'survived_overall',
                'wins_count_overall',
                'damage_overall',
                'defended_overall',
                'captured_overall',
                'xp_overall',
                'spotted_overall'
            ]);
        });
    }
};
