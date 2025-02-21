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
        Schema::table('player_achievements', function (Blueprint $table) {
            // Drop the foreign key constraint
            $table->dropForeign('player_achievements_achievement_id_foreign');
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
            // Re-add the foreign key constraint (if needed)
            $table->foreign('achievement_id')->references('id')->on('achievements');
        });
    }
};
