<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\OpenpayKey;
use Illuminate\Http\Request;

class OpenpayKeysController extends Controller
{
    public function getKeys()
    {
        try {
            $keys = OpenpayKey::first();
            return response()->json([
                'success' => true,
                'data'    => $keys,
            ], 200);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    public function getPublicKeys()
    {
        try {
            $keys = OpenpayKey::first();
            return response()->json([
                'success' => true,
                'data' => [
                    'openpay_id'           => $keys->openpay_id,
                    'openpay_public_key'   => $keys->openpay_public_key,
                ],
            ], 200);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    public function getGoogleMapsKey()
    {
        return response()->json([
            'success' => true,
            'data' => [
                'google_maps_api_key' => env('GOOGLE_MAPS_API_KEY', ''),
            ],
        ], 200);
    }

    public function updateKeys(Request $request)
    {
        try {
            $request->validate([
                'openpay_id'           => 'required|string',
                'openpay_secret'       => 'required|string',
                'openpay_public_key'   => 'required|string',
            ]);

            OpenpayKey::updateOrCreate(
                ['id' => 1],
                $request->only([
                    'openpay_id',
                    'openpay_secret',
                    'openpay_public_key',
                ])
            );

            return response()->json(['success' => true, 'message' => 'Credenciales actualizadas'], 200);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }
}
