<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up()
    {
        Schema::create('event_cancellations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('artist_sale_id')->constrained('artist_sales')->onDelete('cascade');
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->text('cancellation_reason');
            $table->decimal('penalty_percentage', 5, 2)->nullable();
            $table->decimal('penalty_amount', 10, 2)->nullable();
            $table->timestamp('refunded_at')->nullable();
            $table->boolean('penalty_paid')->default(false);
            $table->timestamps();
        });

        if (Schema::hasColumn('artist_sales', 'cancelled_at')) {
            $cancelledSales = DB::table('artist_sales')
                ->whereNotNull('cancelled_at')
                ->get(['id', 'artist_id', 'cancelled_at', 'cancellation_reason', 'penalty_percentage', 'penalty_amount', 'refunded_at', 'penalty_paid']);

            foreach ($cancelledSales as $sale) {
                $artist = DB::table('artists')->where('id', $sale->artist_id)->first(['user_id']);
                if ($artist) {
                    DB::table('event_cancellations')->insert([
                        'artist_sale_id'      => $sale->id,
                        'user_id'              => $artist->user_id,
                        'cancellation_reason'  => $sale->cancellation_reason,
                        'penalty_percentage'   => $sale->penalty_percentage,
                        'penalty_amount'       => $sale->penalty_amount,
                        'refunded_at'          => $sale->refunded_at,
                        'penalty_paid'         => $sale->penalty_paid,
                        'created_at'           => $sale->cancelled_at,
                        'updated_at'           => $sale->cancelled_at,
                    ]);
                }
            }

            Schema::table('artist_sales', function (Blueprint $table) {
                $table->dropColumn([
                    'cancelled_at',
                    'cancellation_reason',
                    'penalty_percentage',
                    'penalty_amount',
                    'refunded_at',
                    'penalty_paid',
                ]);
            });
        }
    }

    public function down()
    {
        if (!Schema::hasColumn('artist_sales', 'cancelled_at')) {
            Schema::table('artist_sales', function (Blueprint $table) {
                $table->timestamp('cancelled_at')->nullable()->after('event_status');
                $table->text('cancellation_reason')->nullable()->after('cancelled_at');
                $table->decimal('penalty_percentage', 5, 2)->nullable()->after('cancellation_reason');
                $table->decimal('penalty_amount', 10, 2)->nullable()->after('penalty_percentage');
                $table->timestamp('refunded_at')->nullable()->after('penalty_amount');
                $table->boolean('penalty_paid')->default(false)->after('refunded_at');
            });
        }

        $cancellations = DB::table('event_cancellations')->get();
        foreach ($cancellations as $c) {
            DB::table('artist_sales')->where('id', $c->artist_sale_id)->update([
                'cancelled_at'         => $c->created_at,
                'cancellation_reason'  => $c->cancellation_reason,
                'penalty_percentage'   => $c->penalty_percentage,
                'penalty_amount'       => $c->penalty_amount,
                'refunded_at'          => $c->refunded_at,
                'penalty_paid'         => $c->penalty_paid,
            ]);
        }

        Schema::dropIfExists('event_cancellations');
    }
};
