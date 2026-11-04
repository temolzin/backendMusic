<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddExtraKmFieldsToArtistSalesTable extends Migration
{
    public function up()
    {
        Schema::table('artist_sales', function (Blueprint $table) {
            $table->decimal('extra_km_distance', 8, 2)->nullable()->after('google_place_id');
            $table->decimal('extra_km_cost', 10, 2)->nullable()->after('extra_km_distance');
        });
    }

    public function down()
    {
        Schema::table('artist_sales', function (Blueprint $table) {
            $table->dropColumn(['extra_km_distance', 'extra_km_cost']);
        });
    }
}
