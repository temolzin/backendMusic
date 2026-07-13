<?php
namespace App\Http\Controllers;

use App\Models\Artist;
use App\Models\ArtistSale;
use App\Models\ArtistRating;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ArtistRatingController extends Controller
{
    public function rateArtist(Request $request, $saleId)
    {
        $request->validate([
            'rating' => 'required|integer|min:0|max:5'
        ]);

        $artistSale = ArtistSale::where('id', $saleId)
                                ->where('customer_id', auth()->id())
                                ->firstOrFail();

        if ($artistSale->event_status !== ArtistSale::EVENT_STATUS_COMPLETED && $artistSale->status !== ArtistSale::PAYMENT_STATUS_COMPLETED) {
            return response()->json([
                'success' => false,
                'message' => 'Solo puedes calificar eventos que ya han finalizado.'
            ], 403);
        }

        match ((int)$request->rating) {
            0 => ArtistRating::where('artist_sale_id', $artistSale->id)->delete(),
            default => ArtistRating::updateOrCreate(
                ['artist_sale_id' => $artistSale->id, 'artist_id' => $artistSale->artist_id],
                ['rating' => $request->rating]
            ),
        };

        $message = ($request->rating == 0) ? 'Calificación eliminada' : '¡Calificación guardada con éxito!';

        $newAverage = ArtistRating::where('artist_id', $artistSale->artist_id)->avg('rating');

        return response()->json([
            'success' => true,
            'message' => $message,
            'new_average' => round($newAverage, 1)
        ]);
    }

    public function getUserRating($saleId)
    {
        $rating = ArtistRating::where('artist_sale_id', $saleId)->first();

        return response()->json([
            'rating' => $rating ? $rating->rating : 0
        ]);
    }
    
    public function averageRating()
    {
        try {
            $artist = Artist::where('user_id', Auth::user()->id)->first();
            if(!$artist){
                return response()->json([
                    'success' => false,
                    'message' => 'Artista no encontrado'
                ], 404);
            }

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
