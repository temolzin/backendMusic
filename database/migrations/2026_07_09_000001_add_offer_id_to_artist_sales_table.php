<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

class AddOfferIdToArtistSalesTable extends Migration
{
    public function up()
    {
        Schema::table('artist_sales', function (Blueprint $table) {
            $table->unsignedBigInteger('offer_id')->nullable()->after('artist_id');
            $table->foreign('offer_id')->references('id')->on('offers')->onDelete('set null');
        });
    }

    public function down()
    {
        DB::statement('ALTER TABLE artist_sales DROP CONSTRAINT IF EXISTS artist_sales_offer_id_foreign CASCADE');

        Schema::table('artist_sales', function (Blueprint $table) {
            $table->dropColumn('offer_id');
        });
    }
}
