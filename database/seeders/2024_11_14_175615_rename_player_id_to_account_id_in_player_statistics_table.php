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
        Schema::table('player_statistics', function (Blueprint $table) {
            $table->dropForeign(['player_id']);

            // Rename column
            $table->renameColumn('player_id', 'account_id');

            // Create new foreign key constraint
            $table->foreign('account_id')->references('account_id')->on('players')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('player_statistics', function (Blueprint $table) {
            $table->dropForeign(['account_id']);

            // Rename column back to original
            $table->renameColumn('account_id', 'player_id');

            // Recreate original foreign key constraint
            $table->foreign('player_id')->references('id')->on('players')->onDelete('cascade');
        });
    }
};
