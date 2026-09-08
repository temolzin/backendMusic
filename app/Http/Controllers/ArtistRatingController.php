<?php
namespace App\Http\Controllers;

use App\Models\Artist;
use App\Models\ArtistSale;
use App\Models\ArtistRating;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ArtistRatingController extends Controller
{
    public function rateArtist(Request $request, $saleId)
    {
        $request->validate([
            'rating' => 'required|integer|min:0|max:5',
            'comment' => 'nullable|string|max:1000'
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

        if ($this->hasRatingDeadlinePassed($artistSale)) {
            return response()->json([
                'success' => false,
                'message' => 'Ya no puedes calificar a este artista, el tiempo para calificar ha terminado.'
            ], 403);
        }

        match ((int)$request->rating) {
            0 => ArtistRating::where('artist_sale_id', $artistSale->id)->delete(),
            default => ArtistRating::updateOrCreate(
                ['artist_sale_id' => $artistSale->id, 'artist_id' => $artistSale->artist_id],
                ['rating' => $request->rating, 'comment' => $request->comment]
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

        $sale = ArtistSale::where('id', $saleId)->where('customer_id', auth()->id())->first();

        return response()->json([
            'rating' => $rating ? $rating->rating : 0,
            'comment' => $rating ? $rating->comment : null,
            'deadline_passed' => $sale ? $this->hasRatingDeadlinePassed($sale) : false,
        ]);
    }

    public function listArtistRatings($artistId)
    {
        $ratings = ArtistRating::with('artistSale.customer')
            ->where('artist_id', $artistId)
            ->whereNotNull('comment')
            ->where('comment', '!=', '')
            ->latest()
            ->get()
            ->map(function (ArtistRating $rating) {
                $customer = $rating->artistSale?->customer;

                return [
                    'id' => $rating->id,
                    'rating' => $rating->rating,
                    'comment' => $rating->comment,
                    'created_at' => $rating->created_at,
                    'user' => $customer
                        ? [
                            'name' => $customer->name,
                            'image' => $customer->image_profile,
                        ]
                        : null,
                ];
            });

        return response()->json(['data' => $ratings]);
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

    private function hasRatingDeadlinePassed(ArtistSale $artistSale)
    {
        if (!$artistSale->event_date || !$artistSale->event_hour) {
            return false;
        }

        $eventDate = $artistSale->event_date instanceof Carbon
            ? $artistSale->event_date->format('Y-m-d')
            : $artistSale->event_date;
        $eventHour = $artistSale->event_hour instanceof Carbon
            ? $artistSale->event_hour->format('H:i:s')
            : $artistSale->event_hour;
        $eventEnd = Carbon::parse($eventDate . ' ' . $eventHour)
            ->addHours((int) ($artistSale->event_hours ?? 0));

        return Carbon::now()->greaterThan($eventEnd->copy()->addHours(24));
    }
}
