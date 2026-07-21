<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

class AddAccountStatusToUsersTable extends Migration
{
    public function up()
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('account_status')->default('active')->after('remember_token');
        });
    }

    public function down()
    {
        DB::statement('ALTER TABLE users DROP COLUMN account_status CASCADE;');
    }
}
