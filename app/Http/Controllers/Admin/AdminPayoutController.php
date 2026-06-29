<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ArtistSale;
use App\Models\EventCancellation;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;

class AdminPayoutController extends Controller
{
    public function pendingPayouts(): JsonResponse
    {
        $sales = ArtistSale::with(['artist.payoutMethod'])
            ->where('status', 'completed')
            ->where('event_status', '!=', 'cancelled')
            ->get();

        $artistPenalties = EventCancellation::select(
                'event_cancellations.*',
                'artist_sales.artist_id'
            )
            ->join('artist_sales', 'event_cancellations.artist_sale_id', '=', 'artist_sales.id')
            ->where('event_cancellations.penalty_paid', false)
            ->where('event_cancellations.penalty_amount', '>', 0)
            ->whereNotNull('event_cancellations.penalty_amount')
            ->get()
            ->groupBy('artist_id');

        $artistsWithShownPenalties = [];

        $formattedPayouts = $sales->map(function ($sale) use ($artistPenalties, &$artistsWithShownPenalties) {
            $amount = floatval($sale->amount);
            $openpayFee = floatval($sale->openpay_fee);
            $platformFee = $amount * 0.10;
            $netArtistPayout = $amount - $openpayFee - $platformFee;

            $artistId = $sale->artist_id;
            $penalties = collect($artistPenalties->get($artistId, []));
            $totalPenalties = $penalties->sum('penalty_amount');

            if ($totalPenalties > 0 && !in_array($artistId, $artistsWithShownPenalties)) {
                $artistsWithShownPenalties[] = $artistId;
                $adjustedNet = max(0, $netArtistPayout - $totalPenalties);
            }

            if (!($totalPenalties > 0 && !in_array($artistId, $artistsWithShownPenalties))) {
                $penalties = collect([]);
                $totalPenalties = 0;
                $adjustedNet = $netArtistPayout;
            }

            return [
                'sale_id' => $sale->id,
                'amount' => $amount,
                'openpay_fee' => $openpayFee,
                'platform_fee' => $platformFee,
                'net_artist_payout' => $netArtistPayout,
                'total_penalties' => $totalPenalties,
                'adjusted_net_payout' => $adjustedNet,
                'event_date' => $sale->event_date,
                'event_hour' => $sale->event_hour,
                'event_status' => $sale->event_status,
                'penalties' => $penalties->map(function ($p) {
                    return [
                        'sale_id' => $p->artist_sale_id,
                        'penalty_percentage' => $p->penalty_percentage,
                        'penalty_amount' => $p->penalty_amount,
                        'cancelled_at' => $p->created_at,
                        'cancellation_reason' => $p->cancellation_reason,
                    ];
                })->values(),
                'artist' => [
                    'id' => $sale->artist->id,
                    'name' => $sale->artist->name,
                    'payout_method' => $sale->artist->payoutMethod ? [
                        'bank_name' => $sale->artist->payoutMethod->bank_name,
                        'account_holder' => $sale->artist->payoutMethod->account_holder,
                        'clabe' => $sale->artist->payoutMethod->clabe,
                        'rfc' => $sale->artist->payoutMethod->rfc,
                    ] : null
                ]
            ];
        });

        return response()->json([
            'success' => true,
            'data' => $formattedPayouts
        ], 200);
    }

    public function releasePayout(int $saleId): JsonResponse
    {
        $sale = ArtistSale::find($saleId);
        if (!$sale) {
            return response()->json([
                'success' => false,
                'message' => 'No se encontró el registro de la venta.'
            ], 404);
        }

        if ($sale->event_status !== 'completed') {
            return response()->json([
                'success' => false,
                'message' => 'El evento físico aún no ha sido marcado como completado.'
            ], 400);
        }

        if ($sale->status === 'liquidated') {
            return response()->json([
                'success' => false,
                'message' => 'Esta liquidación ya fue pagada anteriormente.'
            ], 400);
        }

        $sale->status = 'liquidated';
        $sale->save();

        EventCancellation::whereIn('artist_sale_id', function ($query) use ($sale) {
            $query->select('id')
                ->from('artist_sales')
                ->where('artist_id', $sale->artist_id);
        })
            ->where('penalty_paid', false)
            ->where('penalty_amount', '>', 0)
            ->whereNotNull('penalty_amount')
            ->update(['penalty_paid' => true]);

        return response()->json([
            'success' => true,
            'message' => 'La liquidación ha sido marcada como pagada con éxito.'
        ], 200);
    }
}
