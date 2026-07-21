<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
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
        DB::statement('ALTER TABLE artist_sales DROP COLUMN IF EXISTS reminder_sent_at CASCADE');
    }
}
