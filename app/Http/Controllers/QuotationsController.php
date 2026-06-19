<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Quotations;
use App\Models\Artist;
use App\Models\Offer;
use App\Services\DistanceMatrixService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use App\Mail\QuotationCreated;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;

class QuotationsController extends Controller
{
    public function addQuotation(Request $request)
    {
        $request->validate([
            'artist_id' => 'required|exists:artists,id',
            'event_hours' => 'required|integer',
            'event_date' => 'required|date',
            'city' => 'required|string',
            'address' => 'required|string',
            'phone' => 'required|string',
            'email' => 'required|email',
            'full_name' => 'required|string',
        ]);

        $artistId = $request->input('artist_id');
        $artist = Artist::find($artistId);

        if (!$artist) {
            return response()->json([
                'success' => false,
                'message' => 'Artista no encontrado',
            ], 404);
        }

        $originalPriceHour = $artist->price_hour;
        $originalBase = $originalPriceHour * $request->input('event_hours');

        $now = Carbon::now('America/Mexico_City')->format('Y-m-d H:i:s');
        $activeOffer = Offer::where('artist_id', $artist->id)
            ->where('is_active', true)
            ->where('start_date', '<=', $now)
            ->where('end_date', '>=', $now)
            ->orderBy('discount_percentage', 'desc')
            ->first();

        $discountPercent = $activeOffer ? (float) $activeOffer->discount_percentage : 0;
        $priceHour = $activeOffer
            ? $artist->price_hour * (1 - $activeOffer->discount_percentage / 100)
            : $artist->price_hour;

        $baseAfterDiscount = $priceHour * $request->input('event_hours');
        $discountAmount = $originalBase - $baseAfterDiscount;

        $extraKmDistance = null;
        $extraKmCost = 0;
        $latitude = $request->input('latitude');
        $longitude = $request->input('longitude');

        if ($latitude && $longitude && $artist->coverage_radius > 0 && $artist->extra_kilometre > 0) {
            $distanceService = new DistanceMatrixService();
            $distance = $distanceService->getDrivingDistanceInKm($artist->zone, (float) $latitude, (float) $longitude);
            if ($distance !== null && $distance > $artist->coverage_radius) {
                $extraKmDistance = $distance;
                $extraKmCost = ($distance - $artist->coverage_radius) * (float) $artist->extra_kilometre;
            }
        }

        $price = $baseAfterDiscount + $extraKmCost;

        try {
            DB::beginTransaction();
            $quotationCreatedAt = Carbon::now();
            $quotation = new Quotations();
            $quotation->artist_id = $artistId;
            $quotation->event_hours = $request->input('event_hours');
            $quotation->event_date = $request->input('event_date');
            $quotation->city = $request->input('city');
            $quotation->address = $request->input('address');
            $quotation->phone = $request->input('phone');
            $quotation->email = $request->input('email');
            $quotation->full_name = $request->input('full_name');
            $quotation->price = $price;
            $quotation->base_price = $originalBase;
            $quotation->discount_percentage = $discountPercent > 0 ? $discountPercent : null;
            $quotation->discount_amount = $discountAmount > 0 ? $discountAmount : null;
            $quotation->latitude = $latitude;
            $quotation->longitude = $longitude;
            $quotation->google_place_id = $request->input('google_place_id');
            $quotation->extra_km_distance = $extraKmDistance;
            $quotation->extra_km_cost = $extraKmCost > 0 ? $extraKmCost : null;
            $quotation->created_at = $quotationCreatedAt;
            $quotation->save();

            DB::commit();

            Mail::to($request->input('email'))->send(new QuotationCreated($quotation));

            return response()->json([
                'success' => true,
                'message' => 'Cotización creada exitosamente',
                'id' => $quotation->id,
                'data' => [
                    'label' => $artist->name,
                    'value' => $artistId,
                ],
            ], 200);
        } catch (\Exception $e) {
            DB::rollback();
            return response()->json([
                'success' => false,
                'real message' => $e->getMessage(),
                'message' => 'Error al crear la cotización. Por favor, inténtalo de nuevo más tarde.',
                'line' => $e->getLine(),
            ], 500);
        }
    }
}
