<?php

namespace App\Http\Controllers\Client\ShoppingCart;

use App\Http\Controllers\Controller;
use App\Models\ShoppingCard;
use App\Models\ShoppingCardDetail;
use App\Models\ArtistSale;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;

class ShoppingCardController extends Controller
{
    private function getActiveShoppingCardForUser($userId, $lock = false)
    {
        $query = ShoppingCard::where('status', ShoppingCard::STATUS_ACTIVE)
            ->where('user_id', $userId)
            ->orderBy('id');

        if ($lock) {
            $query->lockForUpdate();
        }

        $activeCarts = $query->get();

        if ($activeCarts->isEmpty()) {
            return null;
        }

        $mainCart = $activeCarts->first();
        $duplicateCarts = $activeCarts->slice(1);

        foreach ($duplicateCarts as $duplicateCart) {
            $duplicateItems = ShoppingCardDetail::where('shopping_card_id', $duplicateCart->id)->get();

            foreach ($duplicateItems as $duplicateItem) {
                $mainItem = ShoppingCardDetail::where('shopping_card_id', $mainCart->id)
                    ->where('artist_id', $duplicateItem->artist_id)
                    ->first();

                $mainItem
                    ? $this->mergeDuplicateItem($mainItem, $duplicateItem)
                    : $this->moveDuplicateItem($duplicateItem, $mainCart);
            }

            $duplicateCart->status = ShoppingCard::STATUS_INACTIVE;
            $duplicateCart->total = 0;
            $duplicateCart->save();
        }

        $this->consolidateDuplicateItems($mainCart);
        $this->recalculateShoppingCardTotal($mainCart);

        return $mainCart->fresh();
    }

    private function mergeDuplicateItem(ShoppingCardDetail $mainItem, ShoppingCardDetail $duplicateItem)
    {
        $mainItem->hours = (int) $mainItem->hours + (int) $duplicateItem->hours;
        $mainItem->price = $duplicateItem->price;
        $mainItem->save();
        $duplicateItem->delete();
    }

    private function moveDuplicateItem(ShoppingCardDetail $duplicateItem, ShoppingCard $mainCart)
    {
        $duplicateItem->shopping_card_id = $mainCart->id;
        $duplicateItem->save();
    }

    private function consolidateDuplicateItems(ShoppingCard $shoppingCard)
    {
        $itemsByArtist = ShoppingCardDetail::where('shopping_card_id', $shoppingCard->id)
            ->orderBy('id')
            ->get()
            ->groupBy('artist_id');

        foreach ($itemsByArtist as $items) {
            if ($items->count() < 2) {
                continue;
            }

            $mainItem = $items->first();
            $duplicates = $items->slice(1);

            foreach ($duplicates as $duplicateItem) {
                $mainItem->hours = (int) $mainItem->hours + (int) $duplicateItem->hours;
                $mainItem->price = $duplicateItem->price;
                $duplicateItem->delete();
            }

            $mainItem->save();
        }
    }

    private function recalculateShoppingCardTotal(ShoppingCard $shoppingCard)
    {
        $total = ShoppingCardDetail::where('shopping_card_id', $shoppingCard->id)
            ->get()
            ->sum(function ($item) {
                return floatval($item->hours) * floatval($item->price);
            });

        $shoppingCard->total = $total;
        $shoppingCard->save();

        return $total;
    }

    public function create_order(Request $request)
    {
        try {
            //array de los sevicios seleccionados
            $service_id = $request->input("service_id");
            $name = $request->input("name");
            $price = $request->input("price");
            $service_id = intval($service_id);
            $hours = max(1, (int) $request->input('hours', 1));

            DB::beginTransaction();

            $userId = Auth::user()->id;
            DB::table('users')->where('id', $userId)->lockForUpdate()->first();
            $exists_shopping_card = $this->getActiveShoppingCardForUser($userId, true);

            if ($exists_shopping_card) {
                //  $shoping_card_update->order_date_start = $request->input('order_date_start');
                //  $shoping_card_update->order_date_finish = $request->input('order_date_finish');

                $update_item = ShoppingCardDetail::where('artist_id', $service_id)
                    ->where('shopping_card_id', $exists_shopping_card->id)->first();

                if ($update_item) {
                    $update_item->hours = (int) $update_item->hours + $hours;
                    $update_item->save();
                } else {
                    ShoppingCardDetail::create([
                        'shopping_card_id' => $exists_shopping_card->id,
                        'artist_id' => $service_id,
                        'hours' => $hours,
                        'price' =>  $price,
                    ]);
                }
                $this->recalculateShoppingCardTotal($exists_shopping_card);

                DB::commit();
                return response()->json([
                    'success' => true,
                    'message' => 'Carrito encontrado y actualizado',
                ], 200);
            } else {
                $shopping_card = ShoppingCard::create([
                    'user_id' => $userId,
                    'status' => ShoppingCard::STATUS_ACTIVE,
                    'order_date_start' => $request->input("order_date_start"),
                    'order_date_finish' => $request->input("order_date_finish"),
                    'total' =>  floatval($hours) * floatval($price),
                ]);
                ShoppingCardDetail::create([
                    'shopping_card_id' => $shopping_card->id,
                    'artist_id' => $service_id,
                    'hours' => $hours,
                    'price' => $price,
                ]);
            }
            DB::commit();
            return response()->json([
                'success' => true,
                'message' => 'Carrito agregado',
            ], 200);
        } catch (\Exception $e) {
            if (DB::transactionLevel() > 0) {
                DB::rollBack();
            }
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 401);
        }
    }

    public function list_shopping_card_details()
    {
        try {
            DB::beginTransaction();
            $shoppingCard = $this->getActiveShoppingCardForUser(Auth::user()->id, true);
            DB::commit();

            if (!$shoppingCard) {
                return response()->json([
                    'success' => true,
                    'list_shoping_card_details' => [],
                ], 200);
            }

            $list_shoping_card_details = ShoppingCard::with([
                'shoppingCardDetail',
                'shoppingCardDetail.artist' => function ($query) {
                    $query->withAvg('ratings', 'rating');
                },
                'shoppingCardDetail.artist.manager'
            ])
            ->where('status', ShoppingCard::STATUS_ACTIVE)
            ->where('id', $shoppingCard->id)
            ->get();
            return response()->json([
                'success' => true,
                'list_shoping_card_details' => $list_shoping_card_details,
            ], 200);
        } catch (\Exception $e) {
            if (DB::transactionLevel() > 0) {
                DB::rollBack();
            }
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 401);
        }
    }
    public function count_list_shopping_card_details()
    {
        try {
            DB::beginTransaction();
            $shoppingCard = $this->getActiveShoppingCardForUser(Auth::user()->id, true);
            DB::commit();

            if (!$shoppingCard) {
                return response()->json([
                    'success' => true,
                    'count_list_shoping_card_details' => [],
                ], 200);
            }

            $count_list_shoping_card_details = ShoppingCard::with('shoppingCardDetail')
                ->where('id', $shoppingCard->id)
                ->get();

            return response()->json([
                'success' => true,
                'count_list_shoping_card_details' => $count_list_shoping_card_details,
            ], 200);
        } catch (\Exception $e) {
            if (DB::transactionLevel() > 0) {
                DB::rollBack();
            }
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 401);
        }
    }

    public function list_purchase_history()
    {
        try {
            $auth_user = Auth::user();
            if (!$auth_user) {
                return response()->json([
                    'success' => false,
                    'message' => 'Usuario no autenticado',
                    'error' => 'No authenticated user found'
                ], 401);
            }

            $user_id = $auth_user->id;

            $purchases = ArtistSale::where('customer_id', $user_id)
                ->with('artist', 'artist.manager', 'customer', 'cashReference')
                ->orderBy('created_at', 'desc')
                ->get();

            $purchases->each(function (ArtistSale $purchase) {
                if (!$purchase->cashReference) {
                    return;
                }

                $cashReference = $purchase->cashReference->cash_reference;
                if (preg_match('/^LOCAL-(\d+)$/', (string) $cashReference, $matches)) {
                    $cashReference = '1000' . str_pad($matches[1], 12, '0', STR_PAD_LEFT);
                }

                $purchase->setAttribute('cash_reference', $cashReference);
                $purchase->setAttribute('cash_barcode_url', $purchase->cashReference->cash_barcode_url);
                $purchase->setAttribute('cash_due_date', $purchase->cashReference->cash_due_date);
                $purchase->unsetRelation('cashReference');
            });

            return response()->json([
                'success' => true,
                'purchases' => $purchases,
            ], 200);
        } catch (\Exception $e) {
            \Log::error("Error in list_purchase_history: " . $e->getMessage());
            \Log::error($e->getTraceAsString());
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function delete_item_shopping_card_details(Request $request)
    {
        try {
            $artist_id = $request->id;

            DB::beginTransaction();

            $shopping_id = $this->getActiveShoppingCardForUser(Auth::user()->id, true);
            if (!$shopping_id) {
                throw new \Exception('No hay carrito activo');
            }

            $item_shopping_card = ShoppingCardDetail::where('artist_id', $artist_id)
                ->where('shopping_card_id', $shopping_id->id)
                ->first();
            if (!$item_shopping_card) {
                throw new \Exception('El artista no existe en el carrito');
            }
            $item_shopping_card->delete();

            $this->recalculateShoppingCardTotal($shopping_id);

            DB::commit();
            return response()->json([
                'success' => true,
            ], 200);
        } catch (\Exception $e) {
            if (DB::transactionLevel() > 0) {
                DB::rollBack();
            }
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 401);
        }
    }

    public function update_item_shopping_card_details(Request $request)
    {
        try {
            $artist_id = $request->artist_id;
            $hours_artist = max(1, (int) $request->hours_artist);


            DB::beginTransaction();

            $shopping_id = $this->getActiveShoppingCardForUser(Auth::user()->id, true);
            if (!$shopping_id) {
                throw new \Exception('No hay carrito activo');
            }

            $item_shopping_card = ShoppingCardDetail::where('artist_id', $artist_id)
                ->where('shopping_card_id', $shopping_id->id)
                ->first();
            if (!$item_shopping_card) {
                throw new \Exception('El artista no existe en el carrito');
            }
            $item_shopping_card->hours = $hours_artist;
            $item_shopping_card->save();

            $this->recalculateShoppingCardTotal($shopping_id);

            DB::commit();
            return response()->json([
                'success' => true,
            ], 200);
        } catch (\Exception $e) {
            if (DB::transactionLevel() > 0) {
                DB::rollBack();
            }
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 401);
        }
    }

    public function save_address(Request $request)
    {
        try {
            $user = Auth::user();
            if (!$user) {
                return response()->json([
                    'success' => false,
                    'message' => 'Usuario no autenticado'
                ], 401);
            }

            $fullName = trim(($request->input('first_name', '') . ' ' . $request->input('last_name', '')));

            $user->update([
                'name' => $fullName !== '' ? $fullName : $user->name,
                'address' => $request->input('address'),
                'city' => $request->input('city'),
                'state' => $request->input('state'),
                'zip_code' => $request->input('zip_code'),
                'country' => $request->input('country'),
                'latitude' => $request->input('latitude'),
                'longitude' => $request->input('longitude'),
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Dirección guardada correctamente',
                'user' => $user
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }
}
