<?php

namespace App\Http\Controllers\Client\ShoppingCart;

use App\Http\Controllers\Controller;
use App\Models\ShoppingCard;
use App\Models\ShoppingCardDetail;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

use Illuminate\Support\Facades\DB;

class ShoppingCardController extends Controller
{
    public function create_order(Request $request)
    {
        try {
            //array de los sevicios seleccionados
            $services = $request->input("services");

            //Existe un carrito de compras ya con estatus 1 de creado
            $exists_shopping_card = ShoppingCard::where('status', 1)->where('user_id', Auth::user()->id)
                ->first();

            if ($exists_shopping_card) {
                DB::beginTransaction();
                $shoping_card_update = ShoppingCard::find($exists_shopping_card->id);
                //  $shoping_card_update->order_date_start = $request->input('order_date_start');
                //  $shoping_card_update->order_date_finish = $request->input('order_date_finish');
                $shoping_card_update->total =  $request->input("total");
                $shoping_card_update->save();

                $shopping_card_details = ShoppingCardDetail::where('shopping_card_id', $exists_shopping_card->id)->get();



                foreach ($shopping_card_details as $item) {

                    DB::beginTransaction();
                    $musicalGenders =  ShoppingCardDetail::find($item->id);
                    $musicalGenders->delete();
                    DB::commit();
                }

                foreach (json_decode($services) as $item) {
                    ShoppingCardDetail::create([
                        'shopping_card_id' => $exists_shopping_card->id,
                        'artist_id' => $item->id,
                        'hours' => $item->cant,
                        'price' =>  $item->price_hour,
                    ]);
                }


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
                    'order_date_finish' =>   $request->input("order_date_finish"),
                    'total' =>  $request->input("total"),
                ]);

                foreach (json_decode($services) as $item) {
                    ShoppingCardDetail::create([
                        'shopping_card_id' => $shopping_card->id,
                        'artist_id' => $item->id,
                        'hours' => $item->cant,
                        'price' =>  $item->price_hour,
                    ]);
                }
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

    public function list_shoping_card_details()
    {
        try {
            $list_shoping_card_details = ShoppingCard::with('shoppingCardDetail','shoppingCardDetail.artist')->where('status', 1)->where('user_id', Auth::user()->id)->get();
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
    public function count_list_shoping_card_details()
    {
        try {
            $count_list_shoping_card_details = ShoppingCard::with('shoppingCardDetail',)->where('status', 1)->where('user_id', Auth::user()->id)->count();
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
}
