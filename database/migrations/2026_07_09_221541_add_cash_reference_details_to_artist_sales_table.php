<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('artist_sales', function (Blueprint $table) {
            $table->string('cash_reference')->nullable()->after('openpay_transaction_id');
            $table->string('cash_barcode_url')->nullable()->after('cash_reference');
            $table->timestamp('cash_due_date')->nullable()->after('cash_barcode_url');
        });
    }

    public function down(): void
    {
        $columns = array_values(array_filter([
            'cash_reference',
            'cash_barcode_url',
            'cash_due_date',
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
