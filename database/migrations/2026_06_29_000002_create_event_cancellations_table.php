<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('event_cancellations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('artist_sale_id')->constrained('artist_sales')->onDelete('cascade');
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->text('cancellation_reason');
            $table->decimal('penalty_percentage', 5, 2)->nullable();
            $table->decimal('penalty_amount', 10, 2)->nullable();
            $table->timestamp('refunded_at')->nullable();
            $table->boolean('penalty_paid')->default(false);
            $table->timestamps();
        });

    }

    public function down()
    {
        Schema::dropIfExists('event_cancellations');
    }
};
