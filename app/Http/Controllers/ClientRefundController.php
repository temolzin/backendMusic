<?php

namespace App\Http\Controllers;

use App\Models\ClientRefund;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Openpay\Data\Openpay;
use App\Models\OpenpayKey;
use App\Mail\EventRefundedNotification;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Exception;

class ClientRefundController extends Controller
{
    public function index()
    {
        $refunds = ClientRefund::with([
            'cancellation.artistSale',
            'customer',
            'authorizedBy'
        ])
            ->orderBy('created_at', 'desc')
            ->get();

        return response()->json($refunds, 200);
    }

    public function getPendingRefunds()
    {
        $refunds = ClientRefund::with([
            'cancellation.artistSale',
            'customer',
            'authorizedBy'
        ])
            ->where('status', ClientRefund::STATUS_PENDING)
            ->orderBy('created_at', 'desc')
            ->get();

        return response()->json([
            'success' => true,
            'data' => $refunds
        ], 200);
    }

    public function processRefund(Request $request, $id)
    {
        $refund = ClientRefund::with(['cancellation.artistSale.artist', 'cancellation.artistSale.customer', 'customer'])->findOrFail($id);

        if ($refund->status === ClientRefund::STATUS_PROCESSED) {
            return response()->json([
                'message' => 'Este reembolso ya fue procesado anteriormente.'
            ], 400);
        }

        $openpayTransactionId = $refund->cancellation->artistSale->openpay_transaction_id ?? null;

        if (!$openpayTransactionId) {
            return response()->json([
                'message' => 'No se encontró el ID de transacción de OpenPay asociado a esta venta.'
            ], 422);
        }

        DB::beginTransaction();

        try {
            $keys = OpenpayKey::first();

            if (!$keys) {
                return response()->json([
                    'message' => 'No se encontraron las configuraciones/llaves de OpenPay en la base de datos.'
                ], 500);
            }

            $openpay = Openpay::getInstance(
                $keys->openpay_id,
                $keys->openpay_secret,
                "MX",
                $request->ip()
            );
            Openpay::setProductionMode(!$keys->openpay_sandbox_mode);

            $charge = $openpay->charges->get($openpayTransactionId);
            $refundData = [
                'description' => 'Reembolso por cancelación de evento #' . $refund->cancellation->artist_sale_id,
                'amount'      => (float) $refund->refund_amount
            ];

            $openpayResponse = $charge->refund($refundData);
            $refund->update([
                'status'            => ClientRefund::STATUS_PROCESSED,
                'openpay_refund_id' => $openpayResponse->id,
                'authorized_by'     => auth()->id()
            ]);

            $refund->cancellation->update([
                'refunded_at' => now()
            ]);

            DB::commit();

            $this->sendRefundNotification($refund);

            return response()->json([
                'message' => ' ¡Reembolso procesado correctamente en OpenPay!',
                'data'    => $refund
            ], 200);
        } catch (Exception $e) {
            DB::rollBack();
            return response()->json([
                'message' => 'Error al procesar el reembolso en OpenPay: ' . $e->getMessage()
            ], 500);
        }
    }

    private function sendRefundNotification(ClientRefund $refund)
    {
        try {
            $sale = $refund->cancellation?->artistSale;
            $clientEmail = $refund->customer?->email ?? $sale?->customer?->email;

            if (!$clientEmail) {
                Log::warning("No se pudo enviar correo de reembolso para la devolución #{$refund->id}: Email de cliente no encontrado.");
                return;
            }

            Mail::to($clientEmail)->send(
                new EventRefundedNotification(
                    $sale,
                    $refund->refund_amount,
                    $refund->cancellation?->cancellation_reason
                )
            );
        } catch (\Throwable $e) {
            Log::warning('Error enviando correo de reembolso al cliente: ' . $e->getMessage());
        }
    }
}
