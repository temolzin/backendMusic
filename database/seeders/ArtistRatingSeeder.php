<?php

namespace Database\Seeders;

use App\Models\ArtistRating;
use App\Models\ArtistSale;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ArtistRatingSeeder extends Seeder
{
    public function run()
    {
        DB::statement('TRUNCATE TABLE artist_ratings RESTART IDENTITY CASCADE;');
        $sales = ArtistSale::all();

        if ($sales->isEmpty()) {
            throw new \RuntimeException('No hay eventos (artist_sales) sembrados para generar calificaciones.');
        }

        $ratingValues = [4, 5, 3, 5, 4, 5];

        foreach ($sales as $index => $sale) {
            $rating = $ratingValues[$index % count($ratingValues)];

            ArtistRating::create([
                'artist_sale_id' => $sale->id,
                'artist_id'      => $sale->artist_id,
                'rating'         => $rating,
            ]);
        }
    }
}
