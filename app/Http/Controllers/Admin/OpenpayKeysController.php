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
                    'openpay_sandbox_mode' => $keys->openpay_sandbox_mode,
                ],
            ], 200);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    public function updateKeys(Request $request)
    {
        try {
            $request->validate([
                'openpay_id'           => 'required|string',
                'openpay_secret'       => 'required|string',
                'openpay_public_key'   => 'required|string',
                'openpay_sandbox_mode' => 'required|boolean',
            ]);

            OpenpayKey::updateOrCreate(
                ['id' => 1],
                $request->only([
                    'openpay_id',
                    'openpay_secret',
                    'openpay_public_key',
                    'openpay_sandbox_mode',
                ])
            );

            return response()->json(['success' => true, 'message' => 'Credenciales actualizadas'], 200);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }
}
