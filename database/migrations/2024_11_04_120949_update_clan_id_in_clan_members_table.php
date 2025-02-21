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
        Schema::table('clan_members', function (Blueprint $table) {
            $table->unsignedBigInteger('clan_id')->nullable()->change();
        });
    }

    public function down()
    {
        Schema::table('clan_members', function (Blueprint $table) {
            $table->unsignedBigInteger('clan_id')->nullable(false)->change(); // revert to NOT NULL
        });
    }
};
