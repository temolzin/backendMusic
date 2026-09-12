<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('artist_profile_requests', function (Blueprint $table) {
            $table->foreignId('authorized_by')
                ->nullable()
                ->after('reviewed_at')
                ->constrained('users')
                ->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('artist_profile_requests', function (Blueprint $table) {
            $table->dropConstrainedForeignId('authorized_by');
        });
    }
};
