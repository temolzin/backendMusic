<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('artist_sales', function (Blueprint $table) {
            $table->timestamp('hour_reminder_sent_at')->nullable()->after('reminder_sent_at');
        });
    }

    public function down(): void
    {
        Schema::table('artist_sales', function (Blueprint $table) {
            $table->dropColumn('hour_reminder_sent_at');
        });
    }
};
