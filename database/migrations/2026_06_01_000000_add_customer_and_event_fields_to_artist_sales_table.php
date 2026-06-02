<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddCustomerAndEventFieldsToArtistSalesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('artist_sales', function (Blueprint $table) {
            $table->string('customer_first_name')->nullable()->after('amount');
            $table->string('customer_last_name')->nullable()->after('customer_first_name');
            $table->string('customer_email')->nullable()->after('customer_last_name');
            $table->string('customer_phone')->nullable()->after('customer_email');
            $table->string('customer_address')->nullable()->after('customer_phone');
            $table->string('customer_city')->nullable()->after('customer_address');
            $table->string('customer_state')->nullable()->after('customer_city');
            $table->string('customer_zip_code')->nullable()->after('customer_state');
            $table->date('event_date')->nullable()->after('customer_zip_code');
            $table->time('event_hour')->nullable()->after('event_date');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('artist_sales', function (Blueprint $table) {
            $table->dropColumn([
                'customer_first_name',
                'customer_last_name',
                'customer_email',
                'customer_phone',
                'customer_address',
                'customer_city',
                'customer_state',
                'customer_zip_code',
                'event_date',
                'event_hour',
            ]);
        });
    }
}
