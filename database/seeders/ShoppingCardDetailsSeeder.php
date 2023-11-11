<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\ShoppingCard;
use App\Models\ShoppingCardDetail;

class ShoppingCardDetailsSeeder extends Seeder
{
    public function run()
    {
        // Crear una tarjeta de compras
        $shoppingCard = ShoppingCard::create([
            'user_id' => 18,
            'status' => 1,
            'order_date_start' => now(),
            'order_date_finish' => now()->addDays(7),
            'total' => 0,
        ]);

        // Detalles de la tarjeta de compras
        $shoppingCardDetails = [
            [
                'shopping_card_id' =>1,
                'artist_id' => 6,
                'hours' => 2,
                'price' => 6000,
            ],
            [
                'artist_id' => 3,
                'hours' => 2,
                'price' => 15000,
            ],
            // Puedes agregar más detalles aquí según tus necesidades
        ];

        // Calcular el total de la tarjeta de compras basado en los detalles
        $total = 0;

        foreach ($shoppingCardDetails as $detail) {
            $shoppingCardDetail = ShoppingCardDetail::create([
                'shopping_card_id' => $shoppingCard->id,
                'artist_id' => $detail['artist_id'],
                'hours' => $detail['hours'],
                'price' => $detail['price'],
            ]);

            // Sumar al total
            $total += $shoppingCardDetail->hours * $shoppingCardDetail->price;
        }

        // Actualizar el total en la tarjeta de compras
        $shoppingCard->update(['total' => $total]);

        // Crear una tarjeta de compras
        $shoppingCard = ShoppingCard::create([
            'user_id' => 18,
            'status' => 1,
            'order_date_start' => '2023-11-02 20:59:47',
            'order_date_finish' => '2023-11-07 20:59:47',
            'total' => 0,
        ]);

        // Detalles de la tarjeta de compras
        $shoppingCardDetails = [
            [
                'shopping_card_id' =>2,
                'artist_id' => 6,
                'hours' => 2,
                'price' => 6000,
            ],
            [
                'artist_id' => 3,
                'hours' => 5,
                'price' => 15000,
            ],
            // Puedes agregar más detalles aquí según tus necesidades
        ];

        // Calcular el total de la tarjeta de compras basado en los detalles
        $total = 0;

        foreach ($shoppingCardDetails as $detail) {
            $shoppingCardDetail = ShoppingCardDetail::create([
                'shopping_card_id' => $shoppingCard->id,
                'artist_id' => $detail['artist_id'],
                'hours' => $detail['hours'],
                'price' => $detail['price'],
            ]);

            // Sumar al total
            $total += $shoppingCardDetail->hours * $shoppingCardDetail->price;
        }

        // Actualizar el total en la tarjeta de compras
        $shoppingCard->update(['total' => $total]);
    }
}
