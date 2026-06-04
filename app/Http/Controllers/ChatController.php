<?php

namespace App\Http\Controllers;

use App\Models\Message;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

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
            'messages' => $messages
        ], 200);
    }

    public function sendMessage(Request $request)
    {
        $request->validate([
            'artist_sale_id' => 'required|exists:artist_sales,id',
            'message' => 'required|string|max:1000'
        ]);

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
}
