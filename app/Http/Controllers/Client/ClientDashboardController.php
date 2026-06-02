<?php

namespace App\Http\Controllers\Client;

use App\Http\Controllers\Controller;
use App\Models\ShoppingCard;
use App\Models\ArtistSale;
use App\Models\FavouriteArtists;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class ClientDashboardController extends Controller
{
    public function index(Request $request)
    {
        try {
            $periodDays = (int) $request->input('period_days', 30);
            $periodDays = $periodDays > 0 ? $periodDays : 30;
            $periodStart = Carbon::now()->subDays($periodDays);

            $userId = Auth::id();

            $cart = ShoppingCard::with(['shoppingCardDetail.artist'])
                ->where('user_id', $userId)
                ->where('status', 1)
                ->first();

            $cartItems = [];
            $cartTotalItems = 0;
            $cartTotalAmount = 0.0;

            if ($cart) {
                foreach ($cart->shoppingCardDetail as $detail) {
                    $subtotal = (float) $detail->hours * (float) $detail->price;
                    $cartItems[] = [
                        'id' => $detail->id,
                        'artist_id' => $detail->artist_id,
                        'artist_name' => $detail->artist ? ($detail->artist->name ?? null) : null,
                        'hours' => $detail->hours,
                        'price' => (float) $detail->price,
                        'subtotal' => $subtotal,
                    ];
                    $cartTotalItems += 1;
                    $cartTotalAmount += $subtotal;
                }
            }

            $purchasesQuery = ArtistSale::where('customer_id', $userId)->orderBy('created_at', 'desc');
            $purchases = $purchasesQuery->get();
            $totalPurchases = $purchases->count();
            $totalSpent = (float) $purchases->sum('amount');
            $lastPurchase = null;
            if ($purchases->isNotEmpty()) {
                $lp = $purchases->first();
                $lastPurchase = [
                    'id' => $lp->id,
                    'artist_id' => $lp->artist_id,
                    'artist_name' => $lp->artist ? ($lp->artist->name ?? null) : null,
                    'amount' => (float) $lp->amount,
                    'date' => $lp->created_at ? $lp->created_at->toIso8601String() : null,
                ];
            }

            $favsQuery = FavouriteArtists::with('artist')->where('user_id', $userId);
            $favs = $favsQuery->get();
            $totalFavs = $favs->count();
            $addedLastPeriod = $favsQuery->where('created_at', '>=', $periodStart)->count();
            $examples = $favs->take(5)->map(function ($f) {
                return [
                    'artist_id' => $f->artist_id,
                    'artist_name' => $f->artist ? ($f->artist->name ?? null) : null,
                    'added_at' => $f->created_at ? $f->created_at->toIso8601String() : null,
                ];
            })->values();

            $payload = [
                'success' => true,
                'data' => [
                    'period_days' => $periodDays,
                    'generated_at' => Carbon::now()->toIso8601String(),
                    'cards' => [
                        [
                            'key' => 'cart',
                            'label' => 'Carrito activo',
                            'total' => $cartTotalItems,
                            'breakdown' => [
                                'total_items' => $cartTotalItems,
                                'total_amount' => round($cartTotalAmount, 2),
                                'status' => $cart ? $cart->status : null,
                            ],
                            'items' => $cartItems,
                        ],
                        [
                            'key' => 'purchases',
                            'label' => 'Compras realizadas',
                            'total' => $totalPurchases,
                            'breakdown' => [
                                'total_purchases' => $totalPurchases,
                                'total_spent' => round($totalSpent, 2),
                                'last_purchase' => $lastPurchase,
                            ],
                        ],
                        [
                            'key' => 'favourites',
                            'label' => 'Artistas favoritos',
                            'total' => $totalFavs,
                            'breakdown' => [
                                'added_last_' . $periodDays . '_days' => $addedLastPeriod,
                            ],
                            'examples' => $examples,
                        ],
                    ],
                ],
            ];

            return response()->json($payload, 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 500);
        }
    }
}
