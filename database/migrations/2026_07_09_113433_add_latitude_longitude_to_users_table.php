<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
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
        $columns = array_values(array_filter(['latitude', 'longitude'], function ($column) {
            return Schema::hasColumn('users', $column);
        }));

        if (!empty($columns)) {
            Schema::table('users', function (Blueprint $table) use ($columns) {
                $table->dropColumn($columns);
            });
        }
    }
}
