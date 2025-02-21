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
        Schema::create('clans', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('clan_id')->unique();
            $table->string('name', 255);
            $table->string('tag', 15);
            /* $table->foreignId('creator')->constrained('players')->onDelete('cascade');
            $table->foreignId('leader')->constrained('players')->onDelete('cascade'); */
            $table->enum('server', ['EU', 'NA', 'ASIA']);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('clans');
    }
};
