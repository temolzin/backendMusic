<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up()
    {
        Schema::table('openpay_keys', function (Blueprint $table) {
            $table->boolean('openpay_sandbox_mode')->default(true)->after('openpay_public_key');
        });
    }

    public function down()
    {
        Schema::table('openpay_keys', function (Blueprint $table) {
            DB::statement('ALTER TABLE openpay_keys DROP COLUMN IF EXISTS openpay_sandbox_mode CASCADE');
        });
    }
};
