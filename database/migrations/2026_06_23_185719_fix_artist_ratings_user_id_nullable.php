<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up()
    {
        if (Schema::hasColumn('artist_ratings', 'user_id')) {
            Schema::table('artist_ratings', function (Blueprint $table) {
                $table->unsignedBigInteger('user_id')->nullable()->change();
            });
        }
    }
    
    public function down()
    {
        Schema::disableForeignKeyConstraints();
        DB::table('artist_ratings')->truncate();
        Schema::enableForeignKeyConstraints();

        if (Schema::hasColumn('artist_ratings', 'user_id')) {
            Schema::table('artist_ratings', function (Blueprint $table) {
                $table->unsignedBigInteger('user_id')->nullable(false)->change();
            });
        }
    }
};
