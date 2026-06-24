<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up()
    {
        if (Schema::hasColumn('artist_ratings', 'user_id')) {
            DB::statement('ALTER TABLE artist_ratings ALTER COLUMN user_id DROP NOT NULL');
        }
    }

    public function down()
    {
        Schema::disableForeignKeyConstraints();
        DB::statement('TRUNCATE TABLE artist_ratings CASCADE');
        Schema::enableForeignKeyConstraints();
    }
};
