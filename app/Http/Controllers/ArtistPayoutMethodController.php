<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\ArtistPayoutMethod;
use App\Models\Artist;
use Illuminate\Support\Facades\Validator;

class ArtistPayoutMethodController extends Controller
{
    public function show(Request $request)
    {
        $artist = Artist::where('user_id', $request->user()->id)->first();

        if (!$artist) {
            return response()->json(['message' => 'Perfil de artista no encontrado.'], 404);
        }

        $payoutMethod = $artist->payoutMethod;

        return response()->json([
            'success' => true,
            'data' => $payoutMethod
        ], 200);
    }

    public function store(Request $request)
    {
        $artist = Artist::where('user_id', $request->user()->id)->first();

        if (!$artist) {
            return response()->json(['message' => 'No autorizado o perfil de artista no encontrado.'], 404);
        }

        if ($artist->payoutMethod) {
            return response()->json(['message' => 'Los datos de cobro ya existen. Use el método de actualización.'], 400);
        }

        $this->validatePayoutData($request);

        $payoutMethod = ArtistPayoutMethod::create([
            'artist_id' => $artist->id,
            'bank_name' => $request->bank_name,
            'account_holder' => $request->account_holder,
            'clabe' => $request->clabe,
            'rfc' => $request->rfc ? strtoupper($request->rfc) : null,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Datos de cobro guardados correctamente.',
            'data' => $payoutMethod
        ], 201); 
    }

    public function update(Request $request)
    {
        $artist = Artist::where('user_id', $request->user()->id)->first();

        if (!$artist) {
            return response()->json(['message' => 'No autorizado o perfil de artista no encontrado.'], 404);
        }

        $payoutMethod = $artist->payoutMethod;

        if (!$payoutMethod) {
            return response()->json(['message' => 'No existen datos de cobro para actualizar.'], 404);
        }

        $this->validatePayoutData($request);

        $payoutMethod->update([
            'bank_name' => $request->bank_name,
            'account_holder' => $request->account_holder,
            'clabe' => $request->clabe,
            'rfc' => $request->rfc ? strtoupper($request->rfc) : null,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Datos de cobro actualizados correctamente.',
            'data' => $payoutMethod
        ], 200);
    }
    
    private function validatePayoutData(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'bank_name' => 'required|string|max:100',
            'account_holder' => 'required|string|max:255',
            'clabe' => 'required|digits:18', 
            'rfc' => 'nullable|string|min:12|max:13',
        ], [
            'clabe.digits' => 'La CLABE interbancaria debe tener exactamente 18 dígitos.',
            'clabe.required' => 'La CLABE es obligatoria.',
            'account_holder.required' => 'El nombre del titular es requerido.',
            'bank_name.required' => 'El banco es requerido.'
        ]);

        if ($validator->fails()) {
            response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422)->send();
            exit;
        }
    }
}
