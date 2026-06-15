<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('artist_sales', function (Blueprint $table) {
            if (!Schema::hasColumn('artist_sales', 'payment_method')) {
                $table->string('payment_method')->nullable()->after('event_status');
            }
            if (!Schema::hasColumn('artist_sales', 'store')) {
                $table->string('store')->nullable()->after('payment_method');
            }
        });
    }

    public function down()
    {
        Schema::table('artist_sales', function (Blueprint $table) {
            $columns = ['payment_method', 'store'];
            foreach ($columns as $column) {
                if (Schema::hasColumn('artist_sales', $column)) {
                    $table->dropColumn($column);
                }
            }
        });
    }
};
