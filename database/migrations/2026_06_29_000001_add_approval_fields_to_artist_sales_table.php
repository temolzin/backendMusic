<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

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
        $columns = array_values(array_filter([
            'approval_status',
            'approval_deadline',
            'approval_responded_at',
            'openpay_customer_id',
        ], function ($column) {
            return Schema::hasColumn('artist_sales', $column);
        }));

        if (!empty($columns)) {
            Schema::table('artist_sales', function (Blueprint $table) use ($columns) {
                $table->dropColumn($columns);
            });
        }
    }
};
