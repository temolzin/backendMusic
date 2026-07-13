<?php

namespace App\Http\Controllers\Artist;

use App\Http\Controllers\Controller;
use App\Models\Artist;
use App\Models\ArtistSale;
use App\Models\Offer;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class OfferController extends Controller
{
    public function index()
    {
        try {
            $artist = Artist::where('user_id', Auth::user()->id)->firstOrFail();
            $offers = Offer::where('artist_id', $artist->id)->orderBy('created_at', 'desc')->get();

            $offers->each(function ($offer) {
                $offer->has_pending_sale = ArtistSale::where('offer_id', $offer->id)
                    ->where('approval_status', ArtistSale::APPROVAL_STATUS_PENDING)
                    ->exists();
            });

            return response()->json(['success' => true, 'offers' => $offers], 200);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    public function store(Request $request)
    {
        try {
            $request->validate([
                'description'         => 'required|string',
                'discount_percentage' => 'required|numeric|min:1|max:90',
                'start_date'          => 'required|date',
                'end_date'            => 'required|date|after:start_date',
            ]);

            $artist = Artist::where('user_id', Auth::user()->id)->firstOrFail();

            $offer = Offer::create([
                'artist_id'           => $artist->id,
                'description'         => $request->description,
                'discount_percentage' => $request->discount_percentage,
                'start_date' => $request->start_date,
                'end_date' => $request->end_date,
                'is_active'           => now()->between($request->start_date, $request->end_date),
            ]);

            return response()->json(['success' => true, 'offer' => $offer], 201);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    public function update(Request $request, $id)
    {
        try {
            $request->validate([
                'description'         => 'required|string',
                'discount_percentage' => 'required|numeric|min:1|max:90', 
                'start_date'          => 'required|date',
                'end_date'            => 'required|date|after:start_date',
            ]);

            $artist = Artist::where('user_id', Auth::user()->id)->firstOrFail();
            $offer = Offer::where('id', $id)->where('artist_id', $artist->id)->firstOrFail();

            $offer->update([
                'description'         => $request->description,
                'discount_percentage' => $request->discount_percentage,
                'start_date' => $request->start_date,
                'end_date' => $request->end_date,
                'is_active'           => now()->between($request->start_date, $request->end_date),
            ]);

            return response()->json(['success' => true, 'offer' => $offer], 200);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    public function destroy($id)
    {
        try {
            $artist = Artist::where('user_id', Auth::user()->id)->firstOrFail();
            $offer = Offer::where('id', $id)->where('artist_id', $artist->id)->firstOrFail();

            $hasPendingSale = ArtistSale::where('offer_id', $offer->id)
                ->where('approval_status', ArtistSale::APPROVAL_STATUS_PENDING)
                ->exists();

            if ($hasPendingSale) {
                return response()->json([
                    'success' => false,
                    'message' => 'No puedes eliminar esta oferta: hay una venta en espera de tu respuesta (aceptar/rechazar) que la utilizó. Podrás eliminarla una vez que esa solicitud se resuelva.',
                ], 422);
            }

            $offer->delete();
            return response()->json(['success' => true, 'message' => 'Oferta eliminada'], 200);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }
}
