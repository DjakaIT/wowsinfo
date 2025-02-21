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
        // Modify player_achievements.achievement_id to be VARCHAR(255)
        Schema::table('player_achievements', function (Blueprint $table) {
            // Only change the column type if it's not already VARCHAR(255)
            $table->string('achievement_id', 255)->change();
        });

        // Ensure the achievement_id in the achievements table is VARCHAR(255)
        Schema::table('achievements', function (Blueprint $table) {
            $table->string('achievement_id', 255)->change();

            // Check if the unique constraint is already added before trying to add it
            if (!Schema::hasColumn('achievements', 'achievement_id') || !Schema::hasIndex('achievements', 'achievements_achievement_id_unique')) {
                $table->unique('achievement_id');
            }
        });

        // Add the foreign key constraint to the player_achievements table
        Schema::table('player_achievements', function (Blueprint $table) {
            $table->foreign('achievement_id')->references('achievement_id')->on('achievements');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('player_achievements', function (Blueprint $table) {
            $table->dropForeign(['achievement_id']);
        });

        Schema::table('achievements', function (Blueprint $table) {
            // Remove the unique constraint if necessary
            $table->dropUnique('achievements_achievement_id_unique');
        });

        Schema::table('player_achievements', function (Blueprint $table) {
            $table->bigInteger('achievement_id')->change(); // Revert back to the original type if needed
        });
    }
};
