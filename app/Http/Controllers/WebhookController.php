<?php

namespace App\Http\Controllers;

use App\Models\ArtistSale;
use App\Models\OpenpayKey;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class WebhookController extends Controller
{
    public function handleOpenpay(Request $request)
    {
        $payload = $request->all();
        $type = $payload['type'] ?? null;
        $eventId = $payload['id'] ?? null;

        Log::info('OpenPay webhook recibido', ['type' => $type, 'event_id' => $eventId]);

        if ($type === 'verification') {
            return response()->json(['code' => $payload['verification_code'] ?? ''], 200);
        }

        if ($eventId && Cache::has('webhook_' . $eventId)) {
            Log::info('Webhook ya procesado', ['event_id' => $eventId]);
            return response()->json(['message' => 'OK'], 200);
        }

        $transaction = $payload['transaction'] ?? null;

        if (!$transaction) {
            return response()->json(['message' => 'OK'], 200);
        }

        $transactionId = $transaction['id'] ?? ($transaction['transaction_id'] ?? null);

        if (!$transactionId) {
            return response()->json(['message' => 'OK'], 200);
        }

        $sale = ArtistSale::where('openpay_transaction_id', $transactionId)->first();

        if (!$sale) {
            Log::warning('Webhook: venta no encontrada', ['transaction_id' => $transactionId]);
            return response()->json(['message' => 'OK'], 200);
        }

        Cache::put('webhook_' . $eventId, true, now()->addHours(24));

        if ($type === 'charge.succeeded') {
            $method = $transaction['method'] ?? '';

            if ($method === 'card' && $sale->status === ArtistSale::PAYMENT_STATUS_AUTHORIZED) {
                $sale->status = ArtistSale::PAYMENT_STATUS_COMPLETED;
                $sale->save();

                Log::info('Webhook: pago con tarjeta confirmado', [
                    'sale_id' => $sale->id,
                    'transaction_id' => $transactionId
                ]);
            }

            if (($method === 'store' || $method === 'bank_account') && $sale->status === ArtistSale::PAYMENT_STATUS_PENDING) {
                $sale->status = ArtistSale::PAYMENT_STATUS_COMPLETED;
                $sale->save();

                Log::info('Webhook: pago en efectivo confirmado', [
                    'sale_id' => $sale->id,
                    'transaction_id' => $transactionId
                ]);
            }
        }

        if ($type === 'charge.refunded') {
            $method = $transaction['method'] ?? '';

            if ($method === 'card') {
                if ($sale->status === ArtistSale::PAYMENT_STATUS_COMPLETED || $sale->status === ArtistSale::PAYMENT_STATUS_AUTHORIZED) {
                    $sale->status = ArtistSale::PAYMENT_STATUS_CANCELLED;
                    $sale->save();

                    Log::info('Webhook: reembolso de tarjeta procesado', [
                        'sale_id' => $sale->id,
                        'transaction_id' => $transactionId
                    ]);
                }
            }
        }

        if ($type === 'charge.failed') {
            Log::warning('Webhook: cargo fallido', [
                'sale_id' => $sale->id,
                'transaction_id' => $transactionId,
                'error' => $transaction['error_code'] ?? null
            ]);
        }

        if ($type === 'charge.cancelled') {
            Log::info('Webhook: cargo cancelado', [
                'sale_id' => $sale->id,
                'transaction_id' => $transactionId
            ]);
        }

        return response()->json(['message' => 'OK'], 200);
    }
}
