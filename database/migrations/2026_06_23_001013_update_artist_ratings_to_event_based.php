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
        Schema::table('artist_ratings', function (Blueprint $table) {
            $table->dropForeign(['user_id']);
            $table->dropColumn('user_id');

            $table->foreignId('artist_sale_id')
                ->after('id')
                ->constrained('artist_sales')
                ->onDelete('cascade');
            
            $table->unique(['artist_sale_id', 'artist_id']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('artist_ratings', function (Blueprint $table) {
            $table->dropForeign(['artist_sale_id']);
            $table->dropUnique(['artist_sale_id', 'artist_id']);
            $table->dropColumn('artist_sale_id');

            $table->foreignId('user_id')->after('id')->constrained('users')->onDelete('cascade');
        });
    }
};