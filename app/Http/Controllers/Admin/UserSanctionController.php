<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\UserSanction;
use App\Models\SupportTicket;
use App\Models\EventCancellation;
use App\Models\ArtistSale;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class UserSanctionController extends Controller
{
    const WEIGHT_RESTRICTED = 5000;
    const WEIGHT_ACTIVE = 0;
    const WEIGHT_FAULT = 100;
    const WEIGHT_TICKET = 10;

    private function checkAndLiftExpiredSanction(User $user)
    {
        if ($user->account_status != 'active') {
            $lastSanc = UserSanction::where('user_id', $user->id)->orderBy('id', 'desc')->first();

            if (!empty($lastSanc)) {
                if (!empty($lastSanc->ends_at)) {
                    $endsAtDate = Carbon::parse($lastSanc->ends_at);
                    if (Carbon::now()->gte($endsAtDate)) {
                        $user->account_status = 'active';
                        $user->save();
                    }
                }
            }
        }
    }

    /**
     * Display a listing of the resource.
     *
     * @param  Request  $request
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        try {
            $search = $request->input("search");
            $role = $request->input("role");
            $dateFrom = $request->input("date_from");
            $dateTo = $request->input("date_to");
            $accountStatus = $request->input("account_status");
            $perPageInput = $request->input("per_page");

            $penalizedUsers = User::where('account_status', 'restricted')->get();

            foreach ($penalizedUsers as $pUser) {
                $this->checkAndLiftExpiredSanction($pUser);
            }

            $query = User::withCount('sanctions')->with('roles');

            if (!empty($search)) {
                $searchTerm = trim($search);
                $query->where(function ($q) use ($searchTerm) {
                    $q->where('name', 'ilike', '%' . $searchTerm . '%')
                    ->orWhere('email', 'ilike', '%' . $searchTerm . '%')
                    ->orWhere('account_status', 'ilike', '%' . $searchTerm . '%')
                    ->orWhereHas('roles', function ($roleQuery) use ($searchTerm) {
                        $roleQuery->where('name', 'ilike', '%' . $searchTerm . '%');
                    });
                });
            }

            if (!empty($role)) {
                $query->role($role); 
            }

            if (!empty($dateFrom)) {
                if (!empty($dateTo)) {
                    $query->whereBetween('created_at', [$dateFrom, $dateTo . ' 23:59:59']);
                }
            }

            if (!empty($accountStatus)) {
                $query->where('account_status', $accountStatus);
            }
            
            $perPage = 100;
            if (!empty($perPageInput)) {
                $perPage = $perPageInput;
            }

            $users = $query->paginate($perPage);

            $users->getCollection()->transform(function ($user) {
                $user->tickets_against_count = SupportTicket::where('status', SupportTicket::STATUS_RESOLVED)
                    ->whereDoesntHave('sanction') 
                    ->where('reporter_user_id', '!=', $user->id) 
                    ->whereHas('artistSale', function ($q) use ($user) {
                        $q->where(function ($query) use ($user) {
                            $query->where('customer_id', $user->id)
                                ->whereHas('artist', function ($artQ) {
                                    $artQ->whereColumn('artists.user_id', 'support_tickets.reporter_user_id');
                            });
                        })->orWhere(function ($query) use ($user) {
                            $query->whereHas('artist', function ($artQ) use ($user) {
                                $artQ->where('user_id', $user->id);
                            });
                        });
                    })->count();
                
                $thirtyDaysAgo = Carbon::now()->subDays(30);

                $lastSanction = UserSanction::where('user_id', $user->id)
                    ->orderBy('id', 'desc')
                    ->first();

                $cancellationsQuery = EventCancellation::where('user_id', $user->id)
                    ->where('created_at', '>=', $thirtyDaysAgo) 
                    ->with('artistSale');
                
                if (!empty($lastSanction)) {
                    $cancellationsQuery->where('created_at', '>', $lastSanction->created_at);
                }

                $cancellations = $cancellationsQuery->get();
                $faults = 0;
                
                foreach($cancellations as $cancel) {
                    if (!empty($cancel->artistSale)) {
                        $eDate = Carbon::parse($cancel->artistSale->event_date)->startOfDay();
                        $cDate = Carbon::parse($cancel->created_at)->startOfDay();
                        $diff = $cDate->diffInDays($eDate, false);
                        
                        if ($diff >= 3) {
                            if ($diff < 7) {
                                $faults++;
                            }
                        }
                    }
                }

                $user->faults_count = $faults; 
                
                $statusWeight = 0;
                if ($user->account_status == 'restricted') {
                    $statusWeight = self::WEIGHT_RESTRICTED;
                }
                if ($user->account_status == 'active') {
                    $statusWeight = self::WEIGHT_ACTIVE;
                }
                
                $penaltyPoints = ($user->faults_count * self::WEIGHT_FAULT) + ($user->tickets_against_count * self::WEIGHT_TICKET);
                
                $user->status_weight = $statusWeight + $penaltyPoints;
                
                $user->role_name = 'Sin Rol';
                $firstRole = $user->roles->first();
                if (!empty($firstRole)) {
                    $user->role_name = $firstRole->name;
                }
                
                return $user;
            });

            return response()->json($users, 200);
        } catch (\Exception $e) {
            DB::rollback();
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 401);
        }
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        try {
            $user = User::with(['sanctions.sanctionable'])->find($id);

            if (empty($user)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Usuario no encontrado',
                ], 401);
            }

            $this->checkAndLiftExpiredSanction($user);

            $tickets = SupportTicket::with(['reporter', 'artistSale'])
                ->where('status', SupportTicket::STATUS_RESOLVED) 
                ->where('reporter_user_id', '!=', $user->id)
                ->whereHas('artistSale', function ($q) use ($user) {
                    $q->where(function ($query) use ($user) {
                        $query->where('customer_id', $user->id)
                            ->whereHas('artist', function ($artQ) {
                                $artQ->whereColumn('artists.user_id', 'support_tickets.reporter_user_id');
                            });
                    })->orWhere(function ($query) use ($user) {
                        $query->whereHas('artist', function ($artQ) use ($user) {
                            $artQ->where('user_id', $user->id);
                        });
                    });
                })
                ->orderBy('id', 'desc') 
                ->get();

            $cancellations = EventCancellation::where('user_id', $user->id)
                ->with('artistSale')
                ->orderBy('id', 'desc')
                ->get();

            return response()->json([
                'success' => true,
                'user' => $user,
                'sanctions' => $user->sanctions,
                'tickets' => $tickets,
                'cancellations' => $cancellations
            ], 200);

        } catch (\Exception $e) {
            DB::rollback();
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 401);
        }
    }

    /**
     * Get user tickets.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function getUserTickets($id)
    {
        try {
            $user = User::find($id);

            if (empty($user)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Usuario no encontrado',
                ], 401);
            }

            $tickets = SupportTicket::with(['reporter', 'artistSale'])
                ->where('status', SupportTicket::STATUS_RESOLVED) 
                ->whereDoesntHave('sanction') 
                ->where('reporter_user_id', '!=', $user->id)
                ->whereHas('artistSale', function ($q) use ($user) {
                    $q->where(function ($query) use ($user) {
                        $query->where('customer_id', $user->id)
                            ->whereHas('artist', function ($artQ) {
                                $artQ->whereColumn('artists.user_id', 'support_tickets.reporter_user_id');
                            });
                    })->orWhere(function ($query) use ($user) {
                        $query->whereHas('artist', function ($artQ) use ($user) {
                            $artQ->where('user_id', $user->id);
                        });
                    });
                })
                ->orderBy('id', 'desc') 
                ->get();

            return response()->json([
                'success' => true,
                'tickets' => $tickets
            ], 200);

        } catch (\Exception $e) {
            DB::rollback();
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 401);
        }
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        try {
            $userId = $request->input("user_id");
            $ticketId = $request->input("ticket_id");
            $type = $request->input("type");
            $reason = $request->input("reason");
            $startsAtInput = $request->input("starts_at");
            $endsAtInput = $request->input("ends_at");

            if (empty($userId) || empty($ticketId) || empty($type) || empty($reason)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Error por campos vacíos',
                ], 422);
            }

            $ticket = SupportTicket::find($ticketId);

            if (empty($ticket)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Ticket no encontrado',
                ], 401);
            }

            if ($ticket->status != SupportTicket::STATUS_RESOLVED) {
                return response()->json([
                    'success' => false,
                    'message' => 'Solo se pueden aplicar sanciones basadas en tickets que ya estén resueltos.'
                ], 401);
            }

            if ($ticket->sanction()->exists()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Este ticket ya fue utilizado para emitir una sanción anteriormente.'
                ], 401);
            }

            DB::beginTransaction();

            $user = User::find($userId);

            if (empty($user)) {
                DB::rollback();
                return response()->json([
                    'success' => false,
                    'message' => 'Usuario no encontrado',
                ], 401);
            }
            
            $startsAt = Carbon::now();
            if (!empty($startsAtInput)) {
                $startsAt = Carbon::parse($startsAtInput);
            }

            $endsAt = null;
            if (!empty($endsAtInput)) {
                $endsAt = Carbon::parse($endsAtInput);
            }

            $sanction = new UserSanction();
            $sanction->sanctionable_id = $ticket->id;
            $sanction->sanctionable_type = SupportTicket::class;
            $sanction->user_id = $user->id;
            $sanction->type = $type;
            $sanction->reason = $reason;
            $sanction->starts_at = $startsAt;
            $sanction->ends_at = $endsAt; 
            $sanction->created_by = 'admin';
            $sanction->save();

            $user->account_status = $type;
            $user->save();

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Sanción aplicada correctamente.',
                'sanction' => $sanction,
                'user_status' => $user->account_status
            ], 200);

        } catch (\Exception $e) {
            DB::rollback();
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 401);
        }
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function revoke($id)
    {
        try {
            DB::beginTransaction();

            $user = User::find($id);

            if (empty($user)) {
                DB::rollback();
                return response()->json([
                    'success' => false,
                    'message' => 'Usuario no encontrado',
                ], 401);
            }

            $activeSanction = UserSanction::where('user_id', $user->id)
                ->where(function($query) {
                    $query->where('ends_at', '>', Carbon::now())
                        ->orWhereNull('ends_at');
                })
                ->orderBy('id', 'desc')
                ->first();

            if (!empty($activeSanction)) {
                $activeSanction->ends_at = Carbon::now();
                $activeSanction->reason = $activeSanction->reason . ' [Sanción levantada anticipadamente por el Administrador].';
                $activeSanction->save();
            }

            $user->account_status = 'active';
            $user->save();

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'La cuenta del usuario ha sido restaurada a estado activo.',
                'user_status' => $user->account_status
            ], 200);

        } catch (\Exception $e) {
            DB::rollback();
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 401);
        }
    }
}
