<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateQuotationsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('quotations', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('artist_id');
            $table->date('event_date')->nullable();;
            $table->integer('event_hours');
            $table->string('city');
            $table->string('address');
            $table->string('phone');
            $table->string('email');
            $table->string('full_name');
            $table->float('price');
            $table->timestamps();

        });

        Schema::table('quotations', function (Blueprint $table) {
            $table->foreign('artist_id')->references('id')->on('artists');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('quotations');
    }
}
