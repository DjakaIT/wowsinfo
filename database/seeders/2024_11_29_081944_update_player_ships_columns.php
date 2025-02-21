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
            $table->string('ship_name')->nullable();
            $table->string('ship_type')->nullable();
            $table->integer('ship_tier')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('player_ships', function (Blueprint $table) {
            $table->dropColumn([
                'ship_name',
                'ship_type',
                'ship_tier'
            ]);
        });
    }
};
