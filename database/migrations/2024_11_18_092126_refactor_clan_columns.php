<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('clans', function (Blueprint $table) {
            $table->dateTime('new_clan_created')->nullable();
        });

        DB::statement('UPDATE clans SET new_clan_created = FROM_UNIXTIME(clan_created)');

        Schema::table('clans', function (Blueprint $table) {
            $table->dropColumn('clan_created');
        });

        Schema::table('clans', function (Blueprint $table) {
            $table->renameColumn('new_clan_created', 'clan_created');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('clans', function (Blueprint $table) {
            $table->integer('new_clan_created')->nullable();
        });

        DB::statement('UPDATE clans SET new_clan_created = UNIX_TIMESTAMP(clan_created)');

        Schema::table('clans', function (Blueprint $table) {
            $table->dropColumn('clan_created');
        });

        Schema::table('clans', function (Blueprint $table) {
            $table->renameColumn('new_clan_created', 'clan_created');
        });
    }
};
