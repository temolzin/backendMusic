<?php

namespace App\Http\Controllers\Artist;

use App\Http\Controllers\Controller;
use App\Models\Artist;
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
                'discount_percentage' => 'required|numeric|min:1|max:100',
                'start_date'          => 'required|date',
                'end_date'            => 'required|date|after:start_date',
            ]);

            $artist = Artist::where('user_id', Auth::user()->id)->firstOrFail();

            $offer = Offer::create([
                'artist_id'           => $artist->id,
                'description'         => $request->description,
                'discount_percentage' => $request->discount_percentage,
                'start_date' => Carbon::parse($request->start_date)->utc(),
                'end_date'   => Carbon::parse($request->end_date)->utc(),
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
                'discount_percentage' => 'required|numeric|min:1|max:100',
                'start_date'          => 'required|date',
                'end_date'            => 'required|date|after:start_date',
            ]);

            $artist = Artist::where('user_id', Auth::user()->id)->firstOrFail();
            $offer = Offer::where('id', $id)->where('artist_id', $artist->id)->firstOrFail();

            $offer->update([
                'description'         => $request->description,
                'discount_percentage' => $request->discount_percentage,
                'start_date' => Carbon::parse($request->start_date)->utc(),
                'end_date'   => Carbon::parse($request->end_date)->utc(),
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
            $offer->delete();
            return response()->json(['success' => true, 'message' => 'Oferta eliminada'], 200);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }
}
