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
        Schema::create('battles', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('battle_id')->unique();
            $table->timestamp('battle_date');
            $table->integer('duration');
            $table->string('map_name', 255);
            $table->enum('battle_type', ['Random', 'Ranked', 'Co-Op', 'Clan', 'Operation'])->default('Random');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('battles');
    }
};
