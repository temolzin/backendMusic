<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;


return new class extends Migration {
    public function up(): void
    {
        Schema::create('support_tickets', function (Blueprint $table) {
            $table->id();
            $table->foreignId('artist_sale_id')->constrained('artist_sales')->onDelete('cascade');
            $table->foreignId('reporter_user_id')->constrained('users')->onDelete('cascade');
            $table->enum('category', ['no_show', 'delay', 'bad_service', 'cancellation', 'other']);
            $table->text('description');
            $table->enum('status', ['open', 'under_review', 'resolved', 'rejected'])->default('open');
            $table->enum('resolution_type', ['full_refund', 'partial_refund', 'no_action'])->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        DB::statement('DROP TABLE IF EXISTS support_tickets CASCADE;');
    }
};
