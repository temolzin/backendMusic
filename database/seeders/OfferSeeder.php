<?php

namespace Database\Seeders;

use App\Models\Offer;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class OfferSeeder extends Seeder
{
    public function run()
    {
        DB::statement('TRUNCATE TABLE offers RESTART IDENTITY CASCADE;');

        $artistIds = DB::table('artists')->orderBy('id')->limit(7)->pluck('id')->all();

        if (empty($artistIds)) {
            throw new \RuntimeException('No hay artistas sembrados para generar ofertas.');
        }

        $descriptions = [
            '10% de descuento este mes',
            '20% este junio!',
            'Descuento de temporada',
            'Precio especial junio',
            'Oferta de bienvenida',
            'Promo verano',
            'Descuento especial julio',
        ];

        $discounts = [10, 20, 10, 12, 10.33, 15, 8];

        $descriptions2 = [
            'Oferta especial fin de semana',
            'Promoción aniversario',
            'Oferta relámpago',
            'Descuento para eventos corporativos',
            'Descuento para quincenas',
            'Oferta para bodas',
            'Oferta exclusiva clientes frecuentes',
        ];

        $now = Carbon::now();

        foreach ($artistIds as $index => $artistId) {
            Offer::create([
                'artist_id'           => $artistId,
                'description'         => $descriptions[$index],
                'discount_percentage' => $discounts[$index],
                'start_date'          => $now->copy()->startOfMonth(),
                'end_date'            => $now->copy()->endOfMonth(),
                'is_active'           => true,
            ]);

            Offer::create([
                'artist_id'           => $artistId,
                'description'         => $descriptions2[$index],
                'discount_percentage' => $discounts[$index] + 5,
                'start_date'          => $now->copy()->addDays($index + 3),
                'end_date'            => $now->copy()->addDays($index + 18),
                'is_active'           => false,
            ]);
        }
    }
}
