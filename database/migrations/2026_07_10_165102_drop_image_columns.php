<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        $imageColumns = [
            'artists' => 'image',
            'managers' => 'image',
            'musical_genders' => 'image',
            'users' => 'image_profile',
        ];

        foreach ($imageColumns as $tableName => $column) {
            if (Schema::hasColumn($tableName, $column)) {
                Schema::table($tableName, function (Blueprint $table) use ($column) {
                    $table->dropColumn($column);
                });
            }
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {

        Schema::table('artists', function (Blueprint $table) {
            $table->string('image')->nullable();
        });

        Schema::table('managers', function (Blueprint $table) {
            $table->string('image')->nullable();
        });

        Schema::table('musical_genders', function (Blueprint $table) {
            $table->string('image')->nullable();
        });

        Schema::table('users', function (Blueprint $table) {
            $table->string('image_profile')->nullable();
        });
    }
};
