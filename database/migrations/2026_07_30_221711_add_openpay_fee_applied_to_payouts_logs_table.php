<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddOpenpayFeeAppliedToPayoutsLogsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('payouts_logs', function (Blueprint $table) {
            $table->boolean('openpay_fee_applied')->default(true)->after('amount');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('payouts_logs', function (Blueprint $table) {
            $table->dropColumn('openpay_fee_applied');
        });
    }
}
