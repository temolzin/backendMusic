<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up()
    {
        Schema::table('musical_genders', function (Blueprint $table) {
            $table->string('image')->nullable()->after('color');
        });
    }

    public function down()
    {
        DB::statement('ALTER TABLE musical_genders DROP COLUMN image CASCADE');
    }
};
