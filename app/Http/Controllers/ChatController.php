<?php

namespace App\Http\Controllers;

use App\Models\Message;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\ArtistSale;
use Carbon\Carbon;

class ChatController extends Controller
{
    public function getMessages($artistSaleId)
    {
        Message::where('artist_sale_id', $artistSaleId)
            ->where('created_by', '!=', Auth::id())
            ->where('is_read', false)
            ->update(['is_read' => true]);

        $messages = Message::with('sender')
            ->where('artist_sale_id', $artistSaleId)
            ->orderBy('created_at', 'asc')
            ->get();

        return response()->json([
            'success' => true,
            'messages' => $messages,
            'is_chat_active' => $this->isChatActive($artistSaleId),
        ], 200);
    }

    public function sendMessage(Request $request)
    {
        $request->validate([
            'artist_sale_id' => 'required|exists:artist_sales,id',
            'message' => 'required|string|max:1000'
        ]);

        if (!$this->isChatActive($request->artist_sale_id)) {
            return response()->json([
                'success' => false,
                'message' => 'El chat ha sido deshabilitado debido a que el evento ha concluido. Gracias por usar nuestra plataforma, esperamos verte pronto en un nuevo evento.'
            ], 403);
        }

        $message = Message::create([
            'artist_sale_id' => $request->artist_sale_id,
            'created_by' => Auth::id(), 
            'message' => $request->message,
            'is_read' => false
        ]);

        $message->load('sender');
        
        return response()->json([
            'success' => true,
            'message' => $message
        ], 201);
    }

    private function isChatActive($artistSaleId)
    {
        $sale = ArtistSale::find($artistSaleId);
        
        if (!$sale || !$sale->event_date) {
            return false;
        }

        $eventDateTime = Carbon::parse($sale->event_date);
        if (isset($sale->event_hour)) {
            $eventDateTime = Carbon::parse($sale->event_date . ' ' . $sale->event_hour);
        }

        $expirationTime = $eventDateTime->addMinutes(1);

        return Carbon::now()->isBefore($expirationTime);
    }
}
