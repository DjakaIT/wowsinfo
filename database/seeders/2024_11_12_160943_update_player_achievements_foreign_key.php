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
        // Fix the data type of account_id to match players table
        Schema::table('player_achievements', function (Blueprint $table) {
            // Change account_id to be of the same type as in the players table
            $table->bigInteger('account_id')->unsigned()->change();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        // Revert the change in case of rollback
        Schema::table('player_achievements', function (Blueprint $table) {
            $table->bigInteger('account_id')->change();
        });
    }
};
