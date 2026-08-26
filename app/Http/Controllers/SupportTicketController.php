<?php
namespace App\Http\Controllers;

use App\Models\SupportTicket;
use App\Models\ArtistSale;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;
use App\Models\TicketLog;
use App\Models\User;
use Illuminate\Validation\Rule;

class SupportTicketController extends Controller
{
    public function store(Request $request)
    {
        $request->validate([
            'artist_sale_id' => 'required|integer|exists:artist_sales,id',
            'category'       => 'required|string',
            'description'    => 'required|string|min:10|max:2000',
        ]);

        $userId = Auth::id();
        $sale = ArtistSale::with('artist')->findOrFail($request->artist_sale_id);

        $isCustomer = $sale->customer_id === $userId;
        $isArtist   = $sale->artist->user_id === $userId;

        if (!$isCustomer && !$isArtist) {
            return response()->json(['message' => 'No autorizado'], 403);
        }

        $allowedCategories = $isArtist
            ? SupportTicket::CATEGORIES_ARTIST
            : SupportTicket::CATEGORIES_CUSTOMER;

        $request->validate([
            'category' => ['required', Rule::in($allowedCategories)],
        ]);

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
            'status'           => SupportTicket::STATUS_OPEN,
        ]);

        TicketLog::create([
            'support_ticket_id'  => $ticket->id,
            'changed_by_user_id' => $userId,
            'status' => TicketLog::STATUS_OPEN,
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

    public function getTickets()
    {
        $tickets = SupportTicket::where('reporter_user_id', Auth::id())
            ->with(['artistSale.artist', 'artistSale.customer', 'reporter.roles', 'media'])
            ->latest()
            ->get();
        return response()->json(['data' => $tickets]);
    }

    public function getArtistTickets()
    {
        $userId = Auth::id();
        $tickets = SupportTicket::whereHas('artistSale.artist', function ($q) use ($userId) {
                $q->where('user_id', $userId);
            })
            ->where('reporter_user_id', '!=', $userId)
            ->with(['artistSale.artist', 'artistSale.customer', 'reporter.roles', 'media'])
            ->latest()
            ->get();
        return response()->json(['data' => $tickets]);
    }

    public function getCustomerTickets()
    {
        $userId = Auth::id();
        $tickets = SupportTicket::whereHas('artistSale', function ($q) use ($userId) {
                $q->where('customer_id', $userId);
            })
            ->where('reporter_user_id', '!=', $userId)
            ->with(['artistSale.artist', 'artistSale.customer', 'reporter.roles', 'media'])
            ->latest()
            ->get();
        return response()->json(['data' => $tickets]);
    }

    public function index(Request $request)
    {
        $request->validate([
            'status'   => ['nullable', Rule::in([
                SupportTicket::STATUS_OPEN,
                SupportTicket::STATUS_UNDER_REVIEW,
                SupportTicket::STATUS_RESOLVED,
                SupportTicket::STATUS_REJECTED,
            ])],
            'category' => ['nullable', Rule::in(SupportTicket::CATEGORIES_CUSTOMER)],
        ]);

        $query = SupportTicket::with(['artistSale.artist', 'artistSale.customer', 'reporter.roles', 'media'])
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
            'data' => $ticket->load(['artistSale.artist', 'artistSale.customer', 'reporter.roles', 'media'])
        ]);
    }

    public function updateStatus(Request $request, SupportTicket $ticket)
    {
        $isClosing = in_array($request->status, [SupportTicket::STATUS_RESOLVED, SupportTicket::STATUS_REJECTED], true);

        $request->validate([
            'status'          => ['required', Rule::in([
                SupportTicket::STATUS_OPEN,
                SupportTicket::STATUS_UNDER_REVIEW,
                SupportTicket::STATUS_RESOLVED,
                SupportTicket::STATUS_REJECTED,
            ])],
            'resolution_type' => [$isClosing ? 'required' : 'nullable', Rule::in(SupportTicket::RESOLUTION_TYPES)],
            'notes'           => 'nullable|string|max:500',
        ]);

        $previousStatus = $ticket->status;

        $ticket->update([
            'status' => $request->status,
            'resolution_type' => $isClosing ? $request->resolution_type : null,
        ]);

        TicketLog::create([
            'support_ticket_id'  => $ticket->id,
            'changed_by_user_id' => Auth::id(),
            'status'             => $request->status,
            'resolution_type'    => $ticket->resolution_type,
            'notes'              => $request->notes
                ?? ($previousStatus !== $request->status ? "Estado actualizado de {$previousStatus} a {$request->status}." : null),
        ]);

        return response()->json([
            'data' => $ticket->load(['artistSale.artist', 'artistSale.customer', 'reporter.roles', 'media'])
        ]);
    }

    public function getLogs(SupportTicket $ticket)
    {
        return response()->json([
            'data' => $ticket->logs()->get()
        ]);
    }

    public function getTicketLogs(SupportTicket $ticket)
    {
        $userId = Auth::id();
        $sale = $ticket->artistSale()->with('artist')->first();

        $isAdmin = Auth::user() && Auth::user()->hasRole(User::ROLE_ADMIN);
        $isReporter = $ticket->reporter_user_id === $userId;
        $isArtist = $sale && $sale->artist && $sale->artist->user_id === $userId;
        $isCustomer = $sale && $sale->customer_id === $userId;

        if (!$isAdmin && !$isReporter && !$isArtist && !$isCustomer) {
            return response()->json(['message' => 'No autorizado'], 403);
        }

        return response()->json([
            'data' => $ticket->logs()->get()
        ]);
    }

    public function addComment(Request $request, SupportTicket $ticket)
    {
        $request->validate([
            'message' => 'required|string|max:1000',
        ]);
        if (in_array($ticket->status, [SupportTicket::STATUS_RESOLVED, SupportTicket::STATUS_REJECTED])) {
            return response()->json([
                'message' => 'No es posible agregar comentarios a un ticket resuelto o rechazado.'
            ], 422);
        }
        $userId = Auth::id();
        $sale = $ticket->artistSale()->with('artist')->first();
        $isAdmin = Auth::user() && Auth::user()->hasRole(User::ROLE_ADMIN);
        $isCustomer = $sale && $sale->customer_id === $userId;
        $isArtist = $sale && $sale->artist && $sale->artist->user_id === $userId;
        $isReporter = $ticket->reporter_user_id === $userId;
        if (!$isAdmin && !$isCustomer && !$isArtist && !$isReporter) {
            return response()->json(['message' => 'No autorizado'], 403);
        }
        $log = TicketLog::create([
            'support_ticket_id'  => $ticket->id,
            'changed_by_user_id' => $userId,
            'status' => $ticket->status,
            'message' => $request->message,
        ]);
        $log->load('changedBy');
        return response()->json(['data' => $log], 201);
    }
}
