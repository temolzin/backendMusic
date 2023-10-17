<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Quotations;
use App\Models\Artist;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use App\Mail\QuotationCreated;
use Carbon\Carbon;

class QuotationsController extends Controller
{
    public function addQuotation(Request $request)
    {
        $request->validate([
            'artist_id.value' => 'required|exists:artists,id',
            'event_hours' => 'required|integer',
            'event_date' => 'required|date',
            'city' => 'required|string',
            'address' => 'required|string',
            'phone' => 'required|string',
            'email' => 'required|email',
            'full_name' => 'required|string',
        ]);

        $artistId = $request->input('artist_id.value');
        $artist = Artist::find($artistId);

        if (!$artist) {
            return response()->json([
                'success' => false,
                'message' => 'Artista no encontrado',
            ], 404);
        }

        $price = $artist->price_hour * $request->input('event_hours');

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
            $quotation->created_at = $quotationCreatedAt;
            $quotation->save();
            
            DB::commit();

            Mail::to($request->input('email'))->send(new QuotationCreated($quotation));

            return response()->json([
                'success' => true,
                'message' => 'Cotización creada exitosamente',
                'data' => [
                    'label' => $artist->name,
                    'value' => $artistId,
                ],
            ], 200);
        } catch (\Exception $e) {
            DB::rollback();
            return response()->json([
                'success' => false,
                'message' => 'Error al crear la cotización. Por favor, inténtalo de nuevo más tarde.',
            ], 500);
        }
    }
}
