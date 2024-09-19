<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class UpdateGameMatchesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('game_matches', function (Blueprint $table) {
            $table->string('score')->nullable();
            $table->json('stats')->nullable(); // Adding new columns
        });
    }

    public function down()
    {
        Schema::table('game_matches', function (Blueprint $table) {
            $table->dropColumn('score');
            $table->dropColumn('stats'); // Dropping columns on rollback
        });
    }
}
