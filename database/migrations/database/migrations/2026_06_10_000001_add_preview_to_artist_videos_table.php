<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddPreviewToArtistVideosTable extends Migration
{
    public function up()
    {
        Schema::table('artist_videos', function (Blueprint $table) {
            $table->string('title')->nullable()->after('youtube_url');
            $table->string('thumbnail')->nullable()->after('title');
        });
    }

    public function down()
    {
        Schema::table('artist_videos', function (Blueprint $table) {
            $table->dropColumn(['title', 'thumbnail']);
        });
    }
}
