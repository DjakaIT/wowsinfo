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
        Schema::create('clan_members', function (Blueprint $table) {
            $table->id();
            $table->foreignId('players_id')->constrained('players')->onDelete('cascade');
            $table->foreignId('clans_id')->constrained('clans')->onDelete('cascade');
            $table->timestamp('joined_at');
            $table->timestamp('left_at')->nullable();
            $table->enum('role', ['member', 'officer', 'leader'])->default('member');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('clan_members');
    }
};
