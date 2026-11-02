<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration {
    public function up(): void
    {
        DB::statement("ALTER TABLE artist_sales MODIFY approval_status ENUM('pending_approval', 'accepted', 'rejected', 'expired', 'cancelled') NOT NULL DEFAULT 'accepted'");
    }

    public function down(): void
    {
        DB::statement("ALTER TABLE artist_sales MODIFY approval_status ENUM('pending_approval', 'accepted', 'rejected', 'expired') NOT NULL DEFAULT 'accepted'");
    }
};
