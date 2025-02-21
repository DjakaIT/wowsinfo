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
        Schema::table('clan_members', function (Blueprint $table) {

            $table->dropForeign(['players_id']);
            $table->dropForeign(['clans_id']);

            $table->dropColumn(['players_id', 'clans_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('clan_members', function (Blueprint $table) {
            $table->unsignedBigInteger('players_id')->nullable();
            $table->unsignedBigInteger('clans_id')->nullable();
        });
    }
};
