<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('player_ships', function (Blueprint $table) {
            // Only remove player_id column if it exists
            if (Schema::hasColumn('player_ships', 'player_id')) {
                $table->dropColumn('player_id');
            }

            // Add the account_id column and foreign key if not present
            if (!Schema::hasColumn('player_ships', 'account_id')) {
                $table->unsignedBigInteger('account_id');
                $table->foreign('account_id')->references('account_id')->on('players')->onDelete('cascade');
            }
        });
    }

    public function down()
    {
        Schema::table('player_ships', function (Blueprint $table) {
            $table->dropForeign(['account_id']);
            $table->dropColumn('account_id');

            $table->unsignedBigInteger('player_id')->nullable();
            $table->foreign('player_id')->references('id')->on('players')->onDelete('cascade');
        });
    }
};
