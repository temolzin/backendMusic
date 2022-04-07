<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateShoppingsCardsDetailsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('shoppings_cards_details', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('shopping_card_id')->unsigned();
            $table->bigInteger('artist_id')->unsigned();
            $table->integer('hours');
            $table->double('price');
            $table->timestamps();

        });

        Schema::table('shoppings_cards_details', function (Blueprint $table) {
            $table->foreign('shopping_card_id')->references('id')->on('shoppings_cards');
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
        Schema::dropIfExists('shoppings_cards_details');
    }
}
