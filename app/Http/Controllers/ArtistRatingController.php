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
            'rating' => 'required|integer|min:0|max:5'
        ]);

        $artist = Artist::findOrFail($id);

        match ((int)$request->rating) {
            0 => ArtistRating::where('user_id', auth()->id())->where('artist_id', $artist->id)->delete(),
            default => ArtistRating::updateOrCreate(
                ['user_id' => auth()->id(), 'artist_id' => $artist->id],
                ['rating' => $request->rating]
            ),
        };

        $message = ($request->rating == 0) ? 'Calificación eliminada' : '¡Calificación guardada con éxito!';

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
