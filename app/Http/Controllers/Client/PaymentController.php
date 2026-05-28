<?php

namespace App\Http\Controllers\Client;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Artist;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;

class PaymentController extends Controller
{

    public function create(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'token' => 'required|string',
            'amount' => 'required|integer|min:1',
            'artistList' => 'required|array|min:1',
            'artistList.*.artist_id' => 'required|integer|exists:artists,id',
            'artistList.*.hours' => 'required|integer|min:1',
        ]);

        if ($validator->fails()) {
            return response()->json(['success' => false, 'errors' => $validator->errors()], 422);
        }

        $clientAmount = (int) $request->input('amount');
        $artistItems = $request->input('artistList');

        $calculatedTotal = 0;

        foreach ($artistItems as $item) {
            $artist = Artist::find($item['artist_id']);
            if (!$artist) {
                return response()->json(['success' => false, 'message' => 'Artista no encontrado'], 404);
            }

            $pricePerHour = (float) $artist->price_hour;
            $hours = (int) $item['hours'];

            $calculatedTotal += (int) round($pricePerHour * $hours * 100);
        }

        if ($calculatedTotal !== $clientAmount) {
            return response()->json([
                'success' => false,
                'message' => 'Monto inválido: el total enviado no coincide con el calculado por el servidor',
                'calculated_total' => $calculatedTotal
            ], 400);
        }

        DB::beginTransaction();
        try {

            DB::commit();

            return response()->json(["success" => true, "message" => "Pago procesado correctamente"]);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error procesando pago: ' . $e->getMessage(), ['exception' => $e]);

            return response()->json(['success' => false, 'message' => 'Error procesando el pago'], 500);
        }
    }
}
