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
        Schema::table('clients', function (Blueprint $table) {
            $table->dropForeign('clients_user_id_foreign');
        });
        Schema::rename('clients', 'cards');
        DB::statement('ALTER SEQUENCE clients_id_seq RENAME TO cards_id_seq;');
        DB::statement('ALTER INDEX clients_pkey RENAME TO cards_pkey;');
        Schema::table('cards', function (Blueprint $table) {
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('cards', function (Blueprint $table) {
            $table->dropForeign('cards_user_id_foreign');
        });

        DB::statement('ALTER INDEX cards_pkey RENAME TO clients_pkey;');
        DB::statement('ALTER SEQUENCE cards_id_seq RENAME TO clients_id_seq;');

        Schema::rename('cards', 'clients');
        Schema::table('clients', function (Blueprint $table) {
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
        });
    }
};
