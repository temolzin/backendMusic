<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('event_reminders', function (Blueprint $table) {
            $table->id();
            $table->foreignId('artist_sale_id')->constrained('artist_sales')->onDelete('cascade');
            $table->string('lapse');
            $table->timestamp('sent_at');
            $table->timestamps();
            $table->unique(['artist_sale_id', 'lapse']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('event_reminders');
    }
};
