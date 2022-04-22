<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateArtistsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('artists', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('user_id')->unsigned()->unique();
            $table->string('name')->unique();
            $table->string('slug')->unique();
            $table->integer('members');
            $table->string('history');
            $table->string('zone');
            $table->double('price_hour');
            $table->string('image')->nullable();
            $table->double('extra_kilometre');
            $table->double('points')->nullable();
            $table->timestamps();
        });

        Schema::table('artists', function (Blueprint $table) {
            $table->foreign('user_id')->references('id')->on('users');
        });
    }
    

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('artist');
    }
}
