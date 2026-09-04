<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

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
        DB::statement('ALTER TABLE artist_sales DROP COLUMN IF EXISTS cash_reference CASCADE');
        DB::statement('ALTER TABLE artist_sales DROP COLUMN IF EXISTS cash_barcode_url CASCADE');
        DB::statement('ALTER TABLE artist_sales DROP COLUMN IF EXISTS cash_due_date CASCADE');
    }
};
