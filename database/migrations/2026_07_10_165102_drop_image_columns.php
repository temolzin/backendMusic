<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

class DropImageColumns extends Migration
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
        DB::statement('ALTER TABLE artists DROP COLUMN IF EXISTS image CASCADE');
        DB::statement('ALTER TABLE managers DROP COLUMN IF EXISTS image CASCADE');
        DB::statement('ALTER TABLE musical_genders DROP COLUMN IF EXISTS image CASCADE');
        DB::statement('ALTER TABLE users DROP COLUMN IF EXISTS image_profile CASCADE');
    }
}
