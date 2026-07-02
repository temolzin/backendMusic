<?php
namespace App\Http\Controllers;

use App\Models\SupportTicket;
use App\Models\ArtistSale;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;
use App\Models\TicketLog;

class SupportTicketController extends Controller
{
    public function store(Request $request)
    {
        $request->validate([
            'artist_sale_id' => 'required|exists:artist_sales,id',
            'category'       => 'required|in:no_show,delay,bad_service,cancellation,other',
            'description'    => 'required|string|min:10',
        ]);

        $userId = Auth::id();
        $sale = ArtistSale::with('artist')->findOrFail($request->artist_sale_id);

        $isCustomer = $sale->customer_id === $userId;
        $isArtist   = $sale->artist->user_id === $userId;

        if (!$isCustomer && !$isArtist) {
            return response()->json(['message' => 'No autorizado'], 403);
        }

        if (now()->lt(Carbon::parse($sale->event_date))) {
            return response()->json([
                'message' => 'Solo puedes reportar un incidente después de la fecha del evento.'
            ], 422);
        }

        $ticket = SupportTicket::create([
            'artist_sale_id'   => $sale->id,
            'reporter_user_id' => $userId,
            'category'         => $request->category,
            'description'      => $request->description,
            'status'           => 'open',
        ]);

        TicketLog::create([
            'support_ticket_id'  => $ticket->id,
            'changed_by_user_id' => $userId,
            'status' => 'open',
            'notes' => 'Ticket creado.',
        ]);

        return response()->json(['data' => $ticket->load('media')], 201);
    }

    public function uploadEvidence(Request $request, SupportTicket $ticket)
    {
        $request->validate([
            'files'   => 'required|array|min:1',
            'files.*' => 'file|mimes:jpg,jpeg,png,mp4,mov|max:20480',
        ]);

        $userId = Auth::id();
        if ($ticket->reporter_user_id !== $userId) {
            return response()->json(['message' => 'No autorizado'], 403);
        }

        foreach ($request->file('files') as $file) {
            $ticket->addMedia($file)->toMediaCollection('ticket_evidences');
        }

        return response()->json(['message' => 'Evidencias guardadas'], 201);
    }

    public function myTickets()
    {
        $tickets = SupportTicket::where('reporter_user_id', Auth::id())
            ->with(['artistSale.artist', 'media'])
            ->latest()
            ->get();

        return response()->json(['data' => $tickets]);
    }

    public function index(Request $request)
    {
        $query = SupportTicket::with(['artistSale.artist', 'artistSale.customer', 'reporter', 'media'])
            ->latest();

        if ($request->status) {
            $query->where('status', $request->status);
        }
        if ($request->category) {
            $query->where('category', $request->category);
        }

        return response()->json(['data' => $query->paginate(15)]);
    }

    public function show(SupportTicket $ticket)
    {
        return response()->json([
            'data' => $ticket->load(['artistSale.artist', 'artistSale.customer', 'reporter', 'media'])
        ]);
    }

    public function updateStatus(Request $request, SupportTicket $ticket)
    {
        $request->validate([
            'status' => 'required|in:open,under_review,resolved,rejected',
            'resolution_type' => 'nullable|in:full_refund,partial_refund,no_action',
            'notes'           => 'nullable|string|max:500',
        ]);

        $ticket->update([
            'status' => $request->status,
            'resolution_type' => $request->resolution_type,
        ]);

        TicketLog::create([
            'support_ticket_id'  => $ticket->id,
            'changed_by_user_id' => Auth::id(),
            'status'             => $request->status,
            'resolution_type'    => $request->resolution_type,
            'notes'              => $request->notes,
        ]);

        return response()->json([
            'data' => $ticket->load(['artistSale.artist', 'artistSale.customer', 'reporter', 'media'])
        ]);
    }

    public function logs(SupportTicket $ticket)
    {
        return response()->json([
            'data' => $ticket->logs()->get()
        ]);
    }

    public function myTicketLogs(SupportTicket $ticket)
    {
        if ($ticket->reporter_user_id !== Auth::id()) {
            return response()->json(['message' => 'No autorizado'], 403);
        }

        return response()->json([
            'data' => $ticket->logs()->get()
        ]);
    }
}
