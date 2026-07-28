<?php

namespace App\Http\Controllers;

use App\Models\ClientRefund;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Openpay\Data\Openpay;
use App\Models\OpenpayKey;
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

  public function processRefund(Request $request, $id)
  {
    $refund = ClientRefund::with('cancellation.artistSale')->findOrFail($id);

    if ($refund->status === 'processed') {
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
        'status'            => 'processed',
        'openpay_refund_id' => $openpayResponse->id,
        'authorized_by'     => auth()->id()
      ]);

      $refund->cancellation->update([
        'refunded_at' => now()
      ]);

      DB::commit();

      return response()->json([
        'message' => '¡Reembolso procesado con éxito en OpenPay!',
        'data'    => $refund
      ], 200);
    } catch (Exception $e) {
      DB::rollBack();
      return response()->json([
        'message' => 'Error al procesar el reembolso en OpenPay: ' . $e->getMessage()
      ], 500);
    }
  }
}
