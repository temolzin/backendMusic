<?php

namespace App\Http\Controllers\Client;

use App\Http\Controllers\Controller;
use App\Models\Card;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class CardController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        try {
            $cards = Card::orderBy('id', 'Asc')->where('user_id', Auth::user()->id)->get();

            return response()->json([
                'success' => true,
                'cards' => $cards,
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 401);
        }
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
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
            $number = $request->input('number_card');
            $clean = preg_replace('/[\s-]/', '', $number);

            $cardType = match (true) {
                preg_match('/^4/', $clean) => 'Visa',
                preg_match('/^5[1-5]/', $clean) || preg_match('/^2(2[2-9]|[3-6]\d|7[01])/', $clean) => 'Mastercard',
                preg_match('/^3[47]/', $clean) => 'American Express',
                preg_match('/^6(011|5)/', $clean) => 'Discover',
                preg_match('/^3(0[0-5]|[68])/', $clean) => 'Diners Club',
                preg_match('/^35(2[89]|[3-8])/', $clean) => 'JCB',
                preg_match('/^(5018|5020|5038|6304|6759|676[1-3])/', $clean) => 'Maestro',
                preg_match('/^62/', $clean) => 'UnionPay',
                default => 'Desconocida'
            };

            DB::beginTransaction();
            $card = new Card();
            $card->user_id = Auth::user()->id;
            $card->number_card = $number;
            $card->card_type = $cardType;
            $card->name = $request->input('name');
            $card->expiration_date = $request->input('expiration_date');
            $card->save();

            DB::commit();

            return response()->json([
                'success' => true,
                'card' => $card,
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
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        try {
            $card = Card::find($id);

            return response()->json([
                'success' => true,
                'card' => $card,
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 401);
        }
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        try {
            DB::beginTransaction();
            $card = Card::find($id);
            $card->fill($request->all());
            $card->save();

            DB::commit();

            return response()->json([
                'success' => true,
                'card' => $card,
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
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        try {
            DB::beginTransaction();
            $card = Card::where('id', $id)->first();
            $card->delete();

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

    public function getProfile()
    {
        try {
            $user = Auth::user();
            if (!$user) {
                return response()->json([
                    'success' => false,
                    'message' => 'Usuario no autenticado'
                ], 401);
            }

            return response()->json([
                'success' => true,
                'user' => [
                    'id' => $user->id,
                    'name' => $user->name,
                    'email' => $user->email,
                    'address' => $user->address,
                    'city' => $user->city,
                    'state' => $user->state,
                    'zip_code' => $user->zip_code,
                    'country' => $user->country,
                ]
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }
}
