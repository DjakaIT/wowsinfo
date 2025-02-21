<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('player_achievements', function (Blueprint $table) {
            $table->string('achievement_id', 255)->change();
        });
    }

    public function down(): void
    {
        Schema::table('player_achievements', function (Blueprint $table) {
            $table->bigInteger('achievement_id')->unsigned()->change();
        });
    }
};
