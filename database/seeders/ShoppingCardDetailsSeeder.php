<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\ShoppingCard;
use App\Models\ShoppingCardDetail;
use App\Models\User;
use App\Models\Offer;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class ShoppingCardDetailsSeeder extends Seeder
{
    public function run()
    {
        $client = User::whereHas('roles', function ($q) {
            $q->where('slug', User::ROLE_CLIENT);
        })->first();

        if (!$client) {
            return;
        }

        $userId = $client->id;

        $shoppingCard = ShoppingCard::create([
            'user_id' => $userId,
            'status' => 1,
            'order_date_start'  => now(),
            'order_date_finish' => now()->addDays(7),
            'total' => 0,
        ]);

        $artistIds = DB::table('artists')->orderBy('id')->pluck('id')->values();

        if ($artistIds->count() < 2) {
            return;
        }

        $shoppingCardDetails = [
            ['artist_id' => $artistIds[0], 'hours' => 2, 'price' => 10000],
            ['artist_id' => $artistIds[1], 'hours' => 2, 'price' => 15000],
        ];

        $total = 0;
        foreach ($shoppingCardDetails as $detail) {
            $finalPrice = $detail['price'];
            $offer = Offer::where('artist_id', $detail['artist_id'])
                ->where('is_active', true)
                ->whereDate('start_date', '<=', Carbon::now())
                ->whereDate('end_date', '>=', Carbon::now())
                ->first();
            if ($offer) {
                $discountAmount = $finalPrice * ($offer->discount_percentage / 100);
                $finalPrice = $finalPrice - $discountAmount;
            }
            $item = ShoppingCardDetail::create([
                'shopping_card_id' => $shoppingCard->id,
                'artist_id' => $detail['artist_id'],
                'hours' => $detail['hours'],
                'price' => $finalPrice,
            ]);
            $total += $item->hours * $item->price;
        }

        $shoppingCard->update(['total' => $total]);
    }
}
