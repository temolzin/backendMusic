<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddLocationAndExtraKmFieldsToQuotationsTable extends Migration
{
    public function up()
    {
        Schema::table('quotations', function (Blueprint $table) {
            $table->decimal('latitude', 10, 7)->nullable()->after('address');
            $table->decimal('longitude', 10, 7)->nullable()->after('latitude');
            $table->string('google_place_id')->nullable()->after('longitude');
            $table->decimal('extra_km_distance', 8, 2)->nullable()->after('price');
            $table->decimal('extra_km_cost', 10, 2)->nullable()->after('extra_km_distance');
            $table->decimal('base_price', 10, 2)->nullable()->after('extra_km_cost');
            $table->decimal('discount_percentage', 5, 2)->nullable()->after('base_price');
            $table->decimal('discount_amount', 10, 2)->nullable()->after('discount_percentage');
        });
    }

    public function down()
    {
        Schema::table('quotations', function (Blueprint $table) {
            $columns = ['latitude', 'longitude', 'google_place_id', 'extra_km_distance', 'extra_km_cost', 'base_price', 'discount_percentage', 'discount_amount'];
            $existing = array_intersect($columns, Schema::getColumnListing('quotations'));
            if (count($existing) > 0) {
                $table->dropColumn($existing);
            }
        });
    }
}
