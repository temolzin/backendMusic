<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddReminderSentAtToArtistSalesTable extends Migration
{
    public function up()
    {
        Schema::table('artist_sales', function (Blueprint $table) {
            $table->timestamp('reminder_sent_at')->nullable()->after('event_hours');
        });
    }

    public function down()
    {
        if (Schema::hasColumn('artist_sales', 'reminder_sent_at')) {
            Schema::table('artist_sales', function (Blueprint $table) {
                $table->dropColumn('reminder_sent_at');
            });
        }
    }
}
