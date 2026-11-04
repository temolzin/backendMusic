<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

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
        if (Schema::hasColumn('artist_sales', 'offer_id')) {
            Schema::table('artist_sales', function (Blueprint $table) {
                $table->dropForeign(['offer_id']);
                $table->dropColumn('offer_id');
            });
        }
    }
}
