<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::statement('ALTER TABLE artist_sales ALTER COLUMN openpay_transaction_id DROP NOT NULL');
    }

    public function down(): void
    {
        DB::table('artist_sales')
            ->whereNull('openpay_transaction_id')
            ->update(['openpay_transaction_id' => 'rollback-default-value']); 

        DB::statement('ALTER TABLE artist_sales ALTER COLUMN openpay_transaction_id SET NOT NULL');
    }
};
