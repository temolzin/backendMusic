<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\OpenpayKey;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class OpenpayKeysController extends Controller
{
    public function getKeys()
    {
        try {
            $keys = OpenpayKey::first();
            if (!$keys) {
                return response()->json([
                    'success' => false,
                    'message' => 'OpenPay no está configurado.',
                ], 503);
            }
            return response()->json([
                'success' => true,
                'data' => array_merge($keys->toArray(), [
                    'openpay_secret_set' => filled($keys->openpay_secret),
                ]),
            ], 200);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    public function getBalance()
    {
        try {
            $keys = OpenpayKey::first();
            if (!$keys || !filled($keys->openpay_id) || !filled($keys->openpay_secret)) {
                return response()->json([
                    'success' => true,
                    'data' => [
                        'configured' => false,
                        'balance' => null,
                    ],
                ]);
            }

            $baseUrl = $keys->openpay_sandbox_mode
                ? 'https://sandbox-api.openpay.mx/v1'
                : 'https://api.openpay.mx/v1';
            $openpayResponse = Http::acceptJson()
                ->withBasicAuth($keys->openpay_secret, '')
                ->withOptions(['connect_timeout' => 5])
                ->timeout(15)
                ->get($baseUrl . '/' . rawurlencode($keys->openpay_id) . '/balance');

            if (!$openpayResponse->successful()) {
                Log::warning('OpenPay balance request failed.', [
                    'status' => $openpayResponse->status(),
                ]);

                return response()->json([
                    'success' => false,
                    'message' => 'No se pudo consultar el saldo de OpenPay.',
                ], 502);
            }

            $responseData = $openpayResponse->json();
            $balance = is_array($responseData) ? ($responseData['currentBalance'] ?? null) : null;
            if (!is_numeric($balance)) {
                Log::warning('OpenPay returned an invalid balance response.', [
                    'response_fields' => is_array($responseData) ? array_keys($responseData) : [],
                    'current_balance_type' => gettype($balance),
                ]);

                return response()->json([
                    'success' => false,
                    'message' => 'OpenPay devolvió una respuesta de saldo no válida.',
                ], 502);
            }

            return response()->json([
                'success' => true,
                'data' => [
                    'configured' => true,
                    'balance' => (float) $balance,
                ],
            ]);
        } catch (\Throwable $e) {
            Log::error('OpenPay balance lookup failed.', [
                'exception' => get_class($e),
                'message' => $e->getMessage(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'No se pudo consultar el saldo de OpenPay.',
            ], 502);
        }
    }

    public function getPublicKeys()
    {
        try {
            $keys = OpenpayKey::first();
            if (!$keys) {
                return response()->json([
                    'success' => false,
                    'message' => 'OpenPay no está configurado.',
                ], 503);
            }
            return response()->json([
                'success' => true,
                'data' => [
                    'openpay_id' => $keys->openpay_id,
                    'openpay_public_key' => $keys->openpay_public_key,
                    'openpay_sandbox_mode' => $keys->openpay_sandbox_mode,
                ],
            ], 200);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    public function updateKeys(Request $request)
    {
        $data = $request->validate([
            'openpay_id' => 'required|string',
            'openpay_secret' => 'sometimes|required|string',
            'openpay_public_key' => 'required|string',
            'openpay_sandbox_mode' => 'required|boolean',
        ]);

        if (!OpenpayKey::query()->exists() && empty($data['openpay_secret'])) {
            return response()->json([
                'success' => false,
                'message' => 'La llave secreta es requerida.',
            ], 422);
        }

        try {
            $keys = OpenpayKey::first();

            $keys
                ? $keys->fill($data)->save()
                : OpenpayKey::create($data);

            return response()->json(['success' => true, 'message' => 'Credenciales actualizadas'], 200);
        } catch (\Throwable $e) {
            Log::error('OpenPay keys update failed.', [
                'exception' => get_class($e),
                'message' => $e->getMessage(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'No se pudieron guardar las credenciales.',
            ], 500);
        }
    }
}
