<?php

namespace App\Http\Controllers\Client\ShoppingCart;

use App\Http\Controllers\Controller;
use App\Models\ShoppingCard;
use App\Models\ShoppingCardDetail;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;

class ShoppingCardController extends Controller
{
    public function create_order(Request $request)
    {
        try {
            //array de los sevicios seleccionados
            $service_id = $request->input("service_id");
            $name = $request->input("name");
            $price = $request->input("price");
            $service_id = intval($service_id);


            //Existe un carrito de compras ya con estatus 1 de creado
            $exists_shopping_card = ShoppingCard::where('status', 1)->where('user_id', Auth::user()->id)
                ->first();

            if ($exists_shopping_card) {

                DB::beginTransaction();
                //  $shoping_card_update->order_date_start = $request->input('order_date_start');
                //  $shoping_card_update->order_date_finish = $request->input('order_date_finish');


                $update_item = ShoppingCardDetail::where('artist_id', $service_id)
                    ->where('shopping_card_id', $exists_shopping_card->id)->first();

                if ($update_item) {
                    $update_item->hours = $update_item->hours  + 1;
                    $update_item->save();
                } else {
                    ShoppingCardDetail::create([
                        'shopping_card_id' => $exists_shopping_card->id,
                        'artist_id' => $service_id,
                        'hours' => 1,
                        'price' =>  $price,
                    ]);
                }
                $list_items = ShoppingCardDetail::where('shopping_card_id', $exists_shopping_card->id)->get();
                $total = 0;
                foreach ($list_items as $data) {
                    $total = $total + floatval($data->hours) * floatval($data->price);
                }
                $shoping_card_update = ShoppingCard::find($exists_shopping_card->id);
                $shoping_card_update->total = $total;
                $shoping_card_update->save();



                DB::commit();
                return response()->json([
                    'success' => true,
                    'message' => 'Carrito encontrado y actualizado',
                ], 200);
            } else {
                $shopping_card = ShoppingCard::create([
                    'user_id' => Auth::user()->id,
                    'status' => 1, // 1 es creado 
                    'order_date_start' => $request->input("order_date_start"),
                    'order_date_finish' =>   null,
                    'total' =>  $price,
                ]);

                ShoppingCardDetail::create([
                    'shopping_card_id' => $shopping_card->id,
                    'artist_id' => $service_id,
                    'hours' => 1,
                    'price' =>  $price,
                ]);
            }
            return response()->json([
                'success' => true,
                'message' => 'Carrito agregado',
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 401);
        }
    }

    public function list_shopping_card_details()
    {
        try {
            $list_shoping_card_details = ShoppingCard::with('shoppingCardDetail', 'shoppingCardDetail.artist', 'shoppingCardDetail.artist.manager')->where('status', 1)->where('user_id', Auth::user()->id)->get();
            return response()->json([
                'success' => true,
                'list_shoping_card_details' => $list_shoping_card_details,
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 401);
        }
    }
    public function count_list_shopping_card_details()
    {
        try {
            $count_list_shoping_card_details = ShoppingCard::with('shoppingCardDetail')->where('status', 1)->where('user_id', Auth::user()->id)->get();

            return response()->json([
                'success' => true,
                'count_list_shoping_card_details' => $count_list_shoping_card_details,
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 401);
        }
    }
    public function delete_item_shopping_card_details(Request $request)
    {
        try {
            $artist_id = $request->id;

            $shopping_id = ShoppingCard::where('user_id', Auth::user()->id)->where('status', 1)->first();

            DB::beginTransaction();

            $item_shopping_card = ShoppingCardDetail::where('artist_id', $artist_id)
                ->where('shopping_card_id', $shopping_id->id)
                ->first();
            $item_shopping_card->delete();

            $price_item_total = floatval($item_shopping_card->price) * floatval($item_shopping_card->hours);
            $new_price = floatval($shopping_id->total) - $price_item_total;
            $shoppingcard = ShoppingCard::find($shopping_id->id);
            $shoppingcard->total = $new_price;
            $shoppingcard->save();

            DB::commit();
            return response()->json([
                'success' => true,
            ], 200);
        } catch (\Exception $e) {
            DB::rollBack();
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
            $hours_artist = $request->hours_artist;


            $shopping_id = ShoppingCard::where('user_id', Auth::user()->id)->where('status', 1)->first();

            DB::beginTransaction();

            $item_shopping_card = ShoppingCardDetail::where('artist_id', $artist_id)
                ->where('shopping_card_id', $shopping_id->id)
                ->first();
            $item_shopping_card->hours = $hours_artist;
            $item_shopping_card->save();

            $list_items = ShoppingCardDetail::where('shopping_card_id', $shopping_id->id)->get();

            $total = 0;
            foreach ($list_items as $data) {
                $total = $total + floatval($data->hours) * floatval($data->price);
            }

            $shoppingcard = ShoppingCard::find($shopping_id->id);
            $shoppingcard->total = $total;
            $shoppingcard->save();

            DB::commit();
            return response()->json([
                'success' => true,
            ], 200);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 401);
        }
    } 
}
