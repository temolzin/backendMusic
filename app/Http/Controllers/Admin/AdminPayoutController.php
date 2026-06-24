<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ArtistSale;
use Illuminate\Http\JsonResponse;

class AdminPayoutController extends Controller
{
    public function pendingPayouts(): JsonResponse
    {
        $sales = ArtistSale::with(['artist.payoutMethod'])
            ->where('status', 'completed')
            ->get();

        $formattedPayouts = $sales->map(function ($sale) {
            $amount = floatval($sale->amount);
            $openpayFee = floatval($sale->openpay_fee);
            $platformFee = $amount * 0.10;
            $netArtistPayout = $amount - $openpayFee - $platformFee;

            return [
                'sale_id' => $sale->id,
                'amount' => $amount,
                'openpay_fee' => $openpayFee,
                'platform_fee' => $platformFee,
                'net_artist_payout' => max(0, $netArtistPayout), 
                'event_date' => $sale->event_date,
                'event_hour' => $sale->event_hour,
                'event_status' => $sale->event_status,
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

        return response()->json([
            'success' => true,
            'message' => 'La liquidación ha sido marcada como pagada con éxito.'
        ], 200);
    }
}
