<?php

namespace App\Http\Controllers;

use App\Models\Artist;
use App\Models\ArtistRating;
use Illuminate\Http\Request;

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
}
