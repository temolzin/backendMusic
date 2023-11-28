<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\ArtistSale;
use Illuminate\Support\Facades\Auth;

class SaleController extends Controller
{
    public function getSalesByArtistId($artistId)
    {
        try {
            $user = Auth::user();

            if ($user->id === $artistId || $user->isAdmin()) {
                $sales = ArtistSale::where('artist_id', $artistId)->get();
                return response()->json(['sales' => $sales], 200);
            } else {
                return response()->json(['message' => 'No autorizado'], 403);
            }
        } catch (\Exception $e) {
            return response()->json(['message' => 'Error al obtener las ventas'], 500);
        }
    }
}
