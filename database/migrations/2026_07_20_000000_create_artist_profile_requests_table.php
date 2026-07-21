<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('artist_profile_requests', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users');
            $table->foreignId('artist_id')->nullable()->constrained('artists');
            $table->enum('request_type', ['creation', 'update']);
            $table->jsonb('proposed_data');
            $table->enum('approval_status', ['pending_approval', 'accepted', 'rejected'])
                ->default('pending_approval');
            $table->text('rejection_reason')->nullable();
            $table->timestamp('reviewed_at')->nullable();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        DB::statement('DROP TABLE IF EXISTS artist_profile_requests CASCADE');
    }
};
