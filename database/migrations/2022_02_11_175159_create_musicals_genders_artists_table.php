<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateMusicalsGendersArtistsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('musicals_genders_artists', function (Blueprint $table) {
            $table->bigInteger('artist_id')->unsigned();
            $table->bigInteger('musical_gender_id')->unsigned();

        });

        Schema::table('musicals_genders_artists', function (Blueprint $table) {
            $table->foreign('artist_id')->references('id')->on('artists');
            $table->foreign('musical_gender_id')->references('id')->on('musicals_genders');

        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('musicals_genders_artists');
    }
}
