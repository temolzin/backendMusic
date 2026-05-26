<?php

namespace App\Http\Controllers\Artist;

use App\Http\Controllers\Controller;
use App\Models\Artist;
use App\Models\Quotations;
use Illuminate\Http\Request;

class ArtistBookingsController extends Controller
{
    /**
     * Get all bookings/quotations for a specific artist
     * 
     * @param int $artistId
     * @return \Illuminate\Http\JsonResponse
     */
    public function getArtistBookings($artistId)
    {
        try {
            $artist = Artist::find($artistId);
            
            if (!$artist) {
                return response()->json([
                    'message' => 'Artista no encontrado',
                    'data' => []
                ], 404);
            }

            $bookings = Quotations::where('artist_id', $artistId)
                ->orderBy('event_date', 'desc')
                ->get()
                ->map(function ($booking) {
                    return [
                        'id' => $booking->id,
                        'date' => $booking->event_date,
                        'eventName' => $booking->event_name ?? 'Evento sin nombre',
                        'clientName' => $booking->full_name,
                        'email' => $booking->email,
                        'phone' => $booking->phone,
                        'time' => $booking->event_hours . ' horas',
                        'location' => $booking->city . ', ' . $booking->address,
                        'city' => $booking->city,
                        'address' => $booking->address,
                        'price' => $booking->price,
                        'status' => ucfirst($booking->status),
                        'description' => $booking->event_name ?? null,
                        'created_at' => $booking->created_at,
                        'updated_at' => $booking->updated_at
                    ];
                });
            
            $bookings = $this->groupBookingsByDate($bookings);

            return response()->json([
                'success' => true,
                'message' => 'Contrataciones obtenidas exitosamente',
                'data' => $bookings,
                'count' => $bookings->count()
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al obtener las contrataciones',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get bookings for authenticated artist
     * 
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function getMyBookings(Request $request)
    {
        try {
            $user = $request->user();
            $artist = Artist::where('user_id', $user->id)->first();
            
            if (!$artist) {
                return response()->json([
                    'success' => false,
                    'message' => 'No eres un artista registrado',
                    'data' => []
                ], 403);
            }

            return $this->getArtistBookings($artist->id);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al obtener tus contrataciones',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get bookings by status
     * 
     * @param int $artistId
     * @param string $status
     * @return \Illuminate\Http\JsonResponse
     */
    public function getBookingsByStatus($artistId, $status)
    {
        try {
            $validStatuses = ['pendiente', 'confirmada'];
            
            if (!in_array(strtolower($status), $validStatuses)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Estado inválido. Debe ser: pendiente o confirmada',
                    'data' => []
                ], 400);
            }

            $bookings = Quotations::where('artist_id', $artistId)
                ->where('status', strtolower($status))
                ->orderBy('event_date', 'desc')
                ->get()
                ->map(function ($booking) {
                    return [
                        'id' => $booking->id,
                        'date' => $booking->event_date,
                        'eventName' => $booking->event_name ?? 'Evento sin nombre',
                        'clientName' => $booking->full_name,
                        'email' => $booking->email,
                        'phone' => $booking->phone,
                        'time' => $booking->event_hours . ' horas',
                        'location' => $booking->city . ', ' . $booking->address,
                        'city' => $booking->city,
                        'address' => $booking->address,
                        'price' => $booking->price,
                        'status' => ucfirst($booking->status),
                        'description' => $booking->event_name ?? null
                    ];
                });
            
            // Keep only one booking per day
            $bookings = $this->groupBookingsByDate($bookings);

            return response()->json([
                'success' => true,
                'message' => 'Contrataciones filtradas exitosamente',
                'data' => $bookings,
                'count' => $bookings->count()
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al obtener las contrataciones',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Update booking status
     * 
     * @param int $bookingId
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function updateBookingStatus($bookingId, Request $request)
    {
        try {
            $request->validate([
                'status' => 'required|in:pendiente,confirmada'
            ]);

            $booking = Quotations::find($bookingId);

            if (!$booking) {
                return response()->json([
                    'success' => false,
                    'message' => 'Contratación no encontrada'
                ], 404);
            }

            $booking->update([
                'status' => $request->status
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Contratación actualizada exitosamente',
                'data' => $booking
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al actualizar la contratación',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Group bookings by date and keep only one per day
     * Returns the first booking for each date
     * 
     * @param \Illuminate\Support\Collection $bookings
     * @return \Illuminate\Support\Collection
     */
    private function groupBookingsByDate($bookings)
    {
        $grouped = [];
        foreach ($bookings as $booking) {
            if (!isset($grouped[$booking['date']])) {
                $grouped[$booking['date']] = $booking;
            }
        }
        return collect(array_values($grouped));
    }
}
