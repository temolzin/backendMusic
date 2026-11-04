<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('artist_sales', function (Blueprint $table) {
            $table->string('openpay_transaction_id')->nullable()->change();
        });
    }

    public function down(): void
    {
        DB::table('artist_sales')
            ->whereNull('openpay_transaction_id')
            ->update(['openpay_transaction_id' => 'rollback-default-value']);

        Schema::table('artist_sales', function (Blueprint $table) {
            $table->string('openpay_transaction_id')->nullable(false)->change();
        });
    }
};
