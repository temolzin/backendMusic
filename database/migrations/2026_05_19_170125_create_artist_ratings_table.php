<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateArtistRatingsTable extends Migration
{
    public function up()
    {
        Schema::create('artist_ratings', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id');
            $table->unsignedBigInteger('artist_id');

            $table->tinyInteger('rating');
            $table->timestamps();

            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('artist_id')->references('id')->on('artists')->onDelete('cascade');

            $table->unique(['user_id', 'artist_id']);
        });
    }

    public function down()
    {
        Schema::dropIfExists('artist_ratings');
    }
}
