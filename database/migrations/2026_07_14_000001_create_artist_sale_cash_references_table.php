<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('artist_sale_cash_references', function (Blueprint $table) {
            $table->id();
            $table->foreignId('artist_sale_id')->constrained('artist_sales')->onDelete('cascade');
            $table->string('cash_reference')->nullable();
            $table->string('cash_barcode_url')->nullable();
            $table->timestamp('cash_due_date')->nullable();
            $table->timestamps();
        });

        Schema::table('artist_sales', function (Blueprint $table) {
            $table->dropColumn(['cash_reference', 'cash_barcode_url', 'cash_due_date']);
        });
    }

    public function down(): void
    {
        Schema::table('artist_sales', function (Blueprint $table) {
            $table->string('cash_reference')->nullable();
            $table->string('cash_barcode_url')->nullable();
            $table->timestamp('cash_due_date')->nullable();
        });

        Schema::dropIfExists('artist_sale_cash_references');
    }
};
