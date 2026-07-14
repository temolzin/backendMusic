<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        DB::statement('ALTER TABLE artists DROP COLUMN IF EXISTS image CASCADE');
        DB::statement('ALTER TABLE managers DROP COLUMN IF EXISTS image CASCADE');
        DB::statement('ALTER TABLE musical_genders DROP COLUMN IF EXISTS image CASCADE');
        DB::statement('ALTER TABLE users DROP COLUMN IF EXISTS image_profile CASCADE');
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
