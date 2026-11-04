<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

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
        if (Schema::hasColumn('openpay_keys', 'openpay_sandbox_mode')) {
            Schema::table('openpay_keys', function (Blueprint $table) {
                $table->dropColumn('openpay_sandbox_mode');
            });
        }
    }
};
