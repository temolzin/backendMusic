<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

class AddEventTypeFieldsToArtistSalesTable extends Migration
{
    public function up()
    {
        Schema::table('artist_sales', function (Blueprint $table) {
            $table->unsignedBigInteger('event_type_id')->nullable()->after('event_hours');
            $table->string('event_type_detail')->nullable()->after('event_type_id');
            $table->foreign('event_type_id')->references('id')->on('event_types')->onDelete('set null');
        });
    }

    public function down()
    {
        DB::statement('ALTER TABLE artist_sales DROP CONSTRAINT IF EXISTS artist_sales_event_type_id_foreign CASCADE');

        Schema::table('artist_sales', function (Blueprint $table) {
            $table->dropColumn(['event_type_id', 'event_type_detail']);
        });
    }
}
