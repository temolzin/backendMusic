<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('artist_sales', function (Blueprint $table) {
            $table->string('card_brand', 20)->nullable()->after('openpay_fee');
            $table->string('card_last_digits', 4)->nullable()->after('card_brand');
        });
    }

    public function down(): void
    {
        DB::statement('
            ALTER TABLE artist_sales
            DROP COLUMN card_brand CASCADE,
            DROP COLUMN card_last_digits CASCADE
        ');
    }
};
