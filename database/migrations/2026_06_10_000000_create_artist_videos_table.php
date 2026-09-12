<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateArtistVideosTable extends Migration
{
    public function up()
    {
        Schema::create('artist_videos', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('artist_id')->nullable();
            $table->string('youtube_url');
            $table->timestamps();
        });
        Schema::table('artist_videos', function (Blueprint $table) {
            $table->foreign('artist_id')->references('id')->on('artists')->onDelete('set null');
        });
    }

    public function down()
    {
        Schema::dropIfExists('artist_videos');
    }
}
