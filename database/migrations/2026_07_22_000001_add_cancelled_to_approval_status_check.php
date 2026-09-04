<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration {
    public function up(): void
    {
        DB::statement('ALTER TABLE artist_sales DROP CONSTRAINT IF EXISTS artist_sales_approval_status_check');
        DB::statement("
            ALTER TABLE artist_sales
            ADD CONSTRAINT artist_sales_approval_status_check
            CHECK (approval_status IN ('pending_approval', 'accepted', 'rejected', 'expired', 'cancelled'))
        ");
    }

    public function down(): void
    {
        DB::statement('ALTER TABLE artist_sales DROP CONSTRAINT IF EXISTS artist_sales_approval_status_check');
        DB::statement("
            ALTER TABLE artist_sales
            ADD CONSTRAINT artist_sales_approval_status_check
            CHECK (approval_status IN ('pending_approval', 'accepted', 'rejected', 'expired'))
        ");
    }
};
