<?php

namespace App\Http\Controllers\Client;

use App\Http\Controllers\Controller;
use App\Models\Card;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class CardController extends Controller
{
    private const UPDATABLE_FIELDS = ['number_card', 'name', 'expiration_date'];

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        try {
            $cards = Card::orderBy('id', 'Asc')
                ->where('user_id', Auth::id())
                ->get()
                ->map(fn (Card $card) => $this->cardResponse($card));

            return response()->json([
                'success' => true,
                'cards' => $cards,
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 500);
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

            DB::beginTransaction();
            $card = new Card();
            $card->user_id = Auth::id();
            $card->number_card = $number;
            $card->card_type = $this->detectCardType($number);
            $card->name = $request->input('name');
            $card->expiration_date = $request->input('expiration_date');
            $card->save();

            DB::commit();

            return response()->json([
                'success' => true,
                'card' => $this->cardResponse($card),
            ], 200);
        } catch (\Exception $e) {
            DB::rollback();
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 500);
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
            $card = $this->findOwnedCard($id);

            if (!$card) {
                return $this->cardNotFound();
            }

            return response()->json([
                'success' => true,
                'card' => $this->cardResponse($card),
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 500);
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
            $card = $this->findOwnedCard($id);

            if (!$card) {
                DB::rollback();
                return $this->cardNotFound();
            }

            $data = $request->only(self::UPDATABLE_FIELDS);

            $numberCard = $data['number_card'] ?? null;

            $data = $numberCard === null
                ? array_diff_key($data, ['number_card' => true])
                : array_merge($data, ['card_type' => $this->detectCardType($numberCard)]);

            $card->fill($data);
            $card->save();

            DB::commit();

            return response()->json([
                'success' => true,
                'card' => $this->cardResponse($card),
            ], 200);
        } catch (\Exception $e) {
            DB::rollback();
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 500);
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
            $card = $this->findOwnedCard($id);

            if (!$card) {
                DB::rollback();
                return $this->cardNotFound();
            }

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
            ], 500);
        }
    }

    private function findOwnedCard($id): ?Card
    {
        return Card::where('id', $id)
            ->where('user_id', Auth::id())
            ->first();
    }

    private function cardNotFound()
    {
        return response()->json([
            'success' => false,
            'message' => 'Tarjeta no encontrada',
        ], 404);
    }

    private function cardResponse(Card $card): array
    {
        return array_merge($card->toArray(), [
            'number_card' => $this->maskCardNumber($card->number_card),
        ]);
    }

    private function maskCardNumber(?string $number): ?string
    {
        if ($number === null) {
            return null;
        }

        $digits = preg_replace('/\D/', '', $number);

        if ($digits === '') {
            return $number;
        }

        return str_repeat('*', max(0, strlen($digits) - 4)) . substr($digits, -4);
    }

    private function detectCardType(?string $number): string
    {
        $clean = preg_replace('/[\s-]/', '', (string) $number);

        return match (true) {
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
