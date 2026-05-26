<?php

namespace App\Http\Controllers;

use App\Models\Artist;
use App\Models\ArtistRating;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ArtistRatingController extends Controller
{
    public function rateArtist(Request $request, $id)
    {
        $request->validate([
            'rating' => 'required|integer|min:1|max:5'
        ]);

        $artist = Artist::findOrFail($id);

        ArtistRating::updateOrCreate(
            [
                'user_id' => auth()->id(),
                'artist_id' => $artist->id
            ],
            [
                'rating' => $request->rating
            ]
        );

        $newAverage = ArtistRating::where('artist_id', $artist->id)->avg('rating');

        return response()->json([
            'success' => true,
            'message' => '¡Calificación guardada con éxito!',
            'new_average' => round($newAverage, 1)
        ]);
    }

    public function getUserRating($id)
    {
        $rating = ArtistRating::where('user_id', auth()->id())
                            ->where('artist_id', $id)
                            ->first();

        return response()->json([
            'rating' => $rating ? $rating->rating : 0
        ]);
    }
    
    public function averageRating()
    {
        try {
            $artist = Artist::where('user_id', Auth::user()->id)->first();
            $avg = ArtistRating::where('artist_id', $artist->id)->avg('rating');
            $total = ArtistRating::where('artist_id', $artist->id)->count();
            return response()->json([
                'success' => true,
                'average' => round($avg ?? 0, 1),
                'total' => $total,
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 401);
        }
    }
}
