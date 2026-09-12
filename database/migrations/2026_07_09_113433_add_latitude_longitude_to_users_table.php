<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class AddLatitudeLongitudeToUsersTable extends Migration
{
    public function up()
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('latitude', 50)->nullable()->after('country');
            $table->string('longitude', 50)->nullable()->after('latitude');
        });
    }

    public function down()
    {
        DB::statement('ALTER TABLE users DROP COLUMN IF EXISTS latitude CASCADE');
        DB::statement('ALTER TABLE users DROP COLUMN IF EXISTS longitude CASCADE');
    }
}
