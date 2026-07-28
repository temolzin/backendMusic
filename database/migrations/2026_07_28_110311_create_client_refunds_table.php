<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

class CreateClientRefundsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('client_refunds', function (Blueprint $table) {
            $table->id();
            $table->foreignId('event_cancellation_id')->constrained('event_cancellations')->onDele('cascade');
            $table->foreignId('customer_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('authorized_by')->nullable()->constrained('users')->onDelete('set null');
            $table->decimal('refund_percentage', 5, 2);
            $table->decimal('refund_amount', 10, 2);
            $table->string('openpay_refund_id')->nullable();
            $table->string('status')->default('pending');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        DB::statement('DROP TABLE IF EXISTS client_refunds CASCADE;');
    }
}
