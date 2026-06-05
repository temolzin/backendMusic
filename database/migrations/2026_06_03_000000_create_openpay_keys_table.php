<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up()
    {
        Schema::create('openpay_keys', function (Blueprint $table) {
            $table->id();
            $table->string('openpay_id')->nullable();
            $table->string('openpay_secret')->nullable();
            $table->string('openpay_public_key')->nullable();
            $table->timestamps();
        });
    }

    public function down()
    {
        DB::statement('DROP TABLE IF EXISTS openpay_keys CASCADE');
    }
};
