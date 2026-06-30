<?php

namespace App\Http\Controllers\Artist;

use App\Http\Controllers\Controller;
use App\Models\ArtistSale;
use App\Models\Artist;
use App\Models\OpenpayKey;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;
use Openpay\Data\Openpay;
use Illuminate\Support\Facades\Log;

class ApprovalController extends Controller
{
    public function pendingRequests()
    {
        try {
            $user = Auth::user();
            $artist = Artist::where('user_id', $user->id)->first();

            if (!$artist) {
                return response()->json(['success' => false, 'message' => 'Perfil de artista no encontrado'], 404);
            }

            $pending = ArtistSale::where('artist_id', $artist->id)
                ->where('approval_status', 'pending_approval')
                ->whereNotNull('approval_deadline')
                ->where('approval_deadline', '>', Carbon::now())
                ->with('customer')
                ->oldest()
                ->get()
                ->map(function ($sale) {
                    $sale->time_remaining_seconds = Carbon::now()->diffInSeconds(
                        Carbon::parse($sale->approval_deadline),
                        false
                    );
                    return $sale;
                });

            return response()->json(['success' => true, 'sales' => $pending]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    public function accept($id)
    {
        try {
            $user = Auth::user();
            $artist = Artist::where('user_id', $user->id)->first();

            if (!$artist) {
                return response()->json(['success' => false, 'message' => 'Perfil de artista no encontrado'], 404);
            }

            $sale = ArtistSale::where('id', $id)
                ->where('artist_id', $artist->id)
                ->where('approval_status', 'pending_approval')
                ->first();

            if (!$sale) {
                return response()->json(['success' => false, 'message' => 'Solicitud no encontrada o ya fue respondida'], 404);
            }

            if (Carbon::now()->gt(Carbon::parse($sale->approval_deadline))) {
                $sale->approval_status = 'expired';
                $sale->save();
                return response()->json(['success' => false, 'message' => 'El tiempo para responder ha expirado'], 422);
            }

            $keys = OpenpayKey::first();
            $openpay = Openpay::getInstance($keys->openpay_id, $keys->openpay_secret, 'MX');
            Openpay::setProductionMode(false);

            if ($sale->payment_method === 'card' && $sale->openpay_transaction_id) {
                $charge = $openpay->charges->get($sale->openpay_transaction_id);
                $charge->capture([
                    'amount' => (float) $sale->amount,
                ]);
            }

            if ($sale->payment_method === 'cash') {
                $customerData = [
                    'name'             => $sale->customer_first_name,
                    'last_name'        => $sale->customer_last_name,
                    'email'            => $sale->customer_email,
                    'requires_account' => false,
                    'address'          => [
                        'line1'        => $sale->customer_address ?? 'Sin dirección',
                        'city'         => $sale->customer_city ?? 'Ciudad',
                        'state'        => $sale->customer_state ?? 'Estado',
                        'postal_code'  => $sale->customer_zip_code ?? '00000',
                        'country_code' => 'MX',
                    ],
                ];

                $chargeRequest = [
                    'method'      => 'store',
                    'amount'      => (float) $sale->amount,
                    'currency'    => 'MXN',
                    'description' => 'Reserva artista - Pago en efectivo',
                    'customer'    => $customerData,
                    'due_date'    => Carbon::now()->addHours(24)->format('Y-m-d\TH:i:s'),
                ];

                $charge = $openpay->charges->create($chargeRequest);
                $sale->openpay_transaction_id = $charge->id;
            }

            $sale->status = $sale->payment_method === 'card' ? 'completed' : 'pending';
            $sale->approval_status = 'accepted';
            $sale->approval_responded_at = Carbon::now();
            $sale->save();

            return response()->json(['success' => true, 'message' => 'Solicitud aceptada correctamente', 'sale' => $sale]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    public function reject($id)
    {
        try {
            $user = Auth::user();
            $artist = Artist::where('user_id', $user->id)->first();

            if (!$artist) {
                return response()->json(['success' => false, 'message' => 'Perfil de artista no encontrado'], 404);
            }

            $sale = ArtistSale::where('id', $id)
                ->where('artist_id', $artist->id)
                ->where('approval_status', 'pending_approval')
                ->first();

            if (!$sale) {
                return response()->json(['success' => false, 'message' => 'Solicitud no encontrada o ya fue respondida'], 404);
            }

            if ($sale->payment_method === 'card' && $sale->openpay_transaction_id) {
                try {
                    $keys = OpenpayKey::first();
                    $openpay = Openpay::getInstance($keys->openpay_id, $keys->openpay_secret, 'MX');
                    Openpay::setProductionMode(false);
                    $charge = $openpay->charges->get($sale->openpay_transaction_id);
                    $charge->refund(['description' => 'Artista rechazó la solicitud']);
                } catch (\Exception $e) {
                    Log::warning('No se pudo cancelar la autorización OpenPay: ' . $e->getMessage());
                }
            }
            
            $sale->status = 'cancelled';
            $sale->approval_status = 'rejected';
            $sale->approval_responded_at = Carbon::now();
            $sale->event_status = 'expired';
            $sale->save();

            return response()->json(['success' => true, 'message' => 'Solicitud rechazada']);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }
}
