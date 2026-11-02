<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up()
    {
        if (Schema::hasColumn('artist_ratings', 'user_id')) {
            DB::statement('ALTER TABLE artist_ratings MODIFY user_id BIGINT UNSIGNED NULL');
        }
    }
    
    public function down()
    {
        Schema::disableForeignKeyConstraints();
        DB::table('artist_ratings')->truncate();
        Schema::enableForeignKeyConstraints();
    }
};
