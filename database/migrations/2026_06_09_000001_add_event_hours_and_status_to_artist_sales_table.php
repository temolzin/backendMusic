<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('artist_sales', function (Blueprint $table) {
            $table->integer('event_hours')->nullable()->after('event_hour');
            $table->string('event_status', 20)->default('pending')->after('event_hours');
        });
    }

    public function down()
    {
        Schema::table('artist_sales', function (Blueprint $table) {
            $table->dropColumn(['event_hours', 'event_status']);
        });
    }
};
