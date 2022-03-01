<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateGaleryArtistsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('galery_artists', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('artist_id')->nullable();
            $table->string('image');
            $table->timestamps();
        });
        Schema::table('galery_artists', function (Blueprint $table) {
            $table->foreign('artist_id')->references('id')->on('artists')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('galery_artists');
    }
}
