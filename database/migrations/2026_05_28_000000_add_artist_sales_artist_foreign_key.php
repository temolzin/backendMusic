<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddArtistSalesArtistForeignKey extends Migration
{
    public function up()
    {
        Schema::table('artist_sales', function (Blueprint $table) {
            $table->dropForeign(['artist_id']);
        });

        Schema::table('artist_sales', function (Blueprint $table) {
            $table->foreign('artist_id')
                ->references('id')
                ->on('artists')
                ->onDelete('cascade');
        });
    }

    public function down()
    {
        Schema::table('artist_sales', function (Blueprint $table) {
            $table->dropForeign(['artist_id']);
        });

        Schema::table('artist_sales', function (Blueprint $table) {
            $table->foreign('artist_id')
                ->references('id')
                ->on('users')
                ->onDelete('cascade');
        });
    }
}
