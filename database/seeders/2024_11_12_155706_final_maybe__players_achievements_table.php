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
            // Modify the achievement_id column type to VARCHAR(255)
            $table->string('achievement_id', 255)->change();
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
            // Revert back to BIGINT UNSIGNED (if needed)
            $table->bigInteger('achievement_id')->unsigned()->change();
        });
    }
};
