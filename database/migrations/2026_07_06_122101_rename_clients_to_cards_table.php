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
        Schema::rename('clients', 'cards');

        DB::statement('ALTER SEQUENCE clients_id_seq RENAME TO cards_id_seq;');
        DB::statement('ALTER INDEX clients_pkey RENAME TO cards_pkey;');
        DB::statement('ALTER TABLE cards RENAME CONSTRAINT clients_user_id_foreign TO cards_user_id_foreign;');
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        DB::statement('ALTER TABLE cards RENAME CONSTRAINT cards_user_id_foreign TO clients_user_id_foreign;');
        DB::statement('ALTER INDEX cards_pkey RENAME TO clients_pkey;');
        DB::statement('ALTER SEQUENCE cards_id_seq RENAME TO clients_id_seq;');

        Schema::rename('cards', 'clients');
    }
};
