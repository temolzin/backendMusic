<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class AddArtistSalesArtistForeignKey extends Migration
{
    public function up()
    {
        $unmappedSales = DB::table('artist_sales')
            ->leftJoin('artists', 'artist_sales.artist_id', '=', 'artists.user_id')
            ->whereNull('artists.id')
            ->count();

        if ($unmappedSales > 0) {
            throw new \RuntimeException(
                'No se puede migrar artist_sales.artist_id a artists.id porque algunas filas no tienen un artista asociado.'
            );
        }

        Schema::table('artist_sales', function (Blueprint $table) {
            $table->dropForeign(['artist_id']);
        });

        DB::table('artist_sales')
            ->join('artists', 'artist_sales.artist_id', '=', 'artists.user_id')
            ->update(['artist_sales.artist_id' => DB::raw('artists.id')]);

        Schema::table('artist_sales', function (Blueprint $table) {
            $table->foreign('artist_id')
                ->references('id')
                ->on('artists')
                ->onDelete('cascade');
        });
    }

    public function down()
    {
        $unmappedSales = DB::table('artist_sales')
            ->leftJoin('artists', 'artist_sales.artist_id', '=', 'artists.id')
            ->whereNull('artists.user_id')
            ->count();

        if ($unmappedSales > 0) {
            throw new \RuntimeException(
                'No se puede revertir artist_sales.artist_id a users.id porque algunas filas no tienen un usuario asociado.'
            );
        }

        Schema::table('artist_sales', function (Blueprint $table) {
            $table->dropForeign(['artist_id']);
        });

        DB::table('artist_sales')
            ->join('artists', 'artist_sales.artist_id', '=', 'artists.id')
            ->update(['artist_sales.artist_id' => DB::raw('artists.user_id')]);

        Schema::table('artist_sales', function (Blueprint $table) {
            $table->foreign('artist_id')
                ->references('id')
                ->on('users')
                ->onDelete('cascade');
        });
    }
}
