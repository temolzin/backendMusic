<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

class CreateArtistPayoutMethodsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('artist_payout_methods', function (Blueprint $table) {
            $table->id();
            $table->foreignId('artist_id')
                  ->constrained('artists')
                  ->onDelete('cascade'); 
            $table->string('bank_name');
            $table->string('account_holder'); 
            $table->string('clabe', 18);      
            $table->string('rfc', 13)->nullable();
            
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        DB::statement('DROP TABLE IF EXISTS artist_payout_methods CASCADE;');
    }
}
