<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('ticket_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('support_ticket_id')
                ->constrained('support_tickets')
                ->onDelete('cascade');
            $table->foreignId('changed_by_user_id')
                ->constrained('users')
                ->onDelete('cascade');
            $table->enum('status', ['open', 'under_review', 'resolved', 'rejected']);
            $table->enum('resolution_type', ['full_refund', 'partial_refund', 'no_action'])->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ticket_logs');
    }
};
