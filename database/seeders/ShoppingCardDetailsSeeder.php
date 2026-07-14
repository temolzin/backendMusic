<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\ShoppingCard;
use App\Models\ShoppingCardDetail;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class ShoppingCardDetailsSeeder extends Seeder
{
    public function run()
    {
        $client = User::whereHas('roles', function ($q) {
            $q->where('slug', 'cliente');
        })->first();

        if (!$client) {
            return;
        }

        $userId = $client->id;

        $shoppingCard = ShoppingCard::create([
            'user_id'           => $userId,
            'status'            => 1,
            'order_date_start'  => now(),
            'order_date_finish' => now()->addDays(7),
            'total'             => 0,
        ]);

        $artistIds = DB::table('artists')->orderBy('id')->pluck('id')->values();

        if ($artistIds->count() < 2) {
            return;
        }

        $shoppingCardDetails = [
            ['artist_id' => $artistIds[0], 'hours' => 2, 'price' => 6000],
            ['artist_id' => $artistIds[1], 'hours' => 2, 'price' => 15000],
        ];

        $total = 0;
        foreach ($shoppingCardDetails as $detail) {
            $item = ShoppingCardDetail::create([
                'shopping_card_id' => $shoppingCard->id,
                'artist_id' => $detail['artist_id'],
                'hours' => $detail['hours'],
                'price' => $detail['price'],
            ]);
            $total += $item->hours * $item->price;
        }

        $shoppingCard->update(['total' => $total]);
    }
}
