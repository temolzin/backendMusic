<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration {
    public function up(): void
    {
    }

    public function down(): void
    {
        DB::table('artist_sales')->where('approval_status', 'cancelled')->update(['approval_status' => 'rejected']);
    }
};
