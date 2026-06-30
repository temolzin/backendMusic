<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('artist_sales', function (Blueprint $table) {
            $table->enum('approval_status', ['pending_approval', 'accepted', 'rejected', 'expired'])
                ->default('accepted')
                ->after('event_status');
            $table->timestamp('approval_deadline')->nullable()->after('approval_status');
            $table->timestamp('approval_responded_at')->nullable()->after('approval_deadline');
            $table->string('openpay_customer_id')->nullable()->after('openpay_transaction_id');
        });
    }

    public function down(): void
    {
        DB::statement('
            ALTER TABLE artist_sales 
            DROP COLUMN approval_status CASCADE,
            DROP COLUMN approval_deadline CASCADE,
            DROP COLUMN approval_responded_at CASCADE,
            DROP COLUMN openpay_customer_id CASCADE
        ');
    }
};
