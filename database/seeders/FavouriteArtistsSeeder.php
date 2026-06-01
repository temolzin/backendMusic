<?php

namespace Database\Seeders;

use App\Models\FavouriteArtists;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class FavouriteArtistsSeeder extends Seeder
{
    public function run()
    {
        DB::statement('TRUNCATE TABLE favourite_artists RESTART IDENTITY CASCADE;');

        $customerIds = [17, 18, 19, 20, 21];
        $artistIds   = range(1, 15);
        $cantidades  = [2, 3, 5];

        foreach ($customerIds as $index => $customerId) {
            $limite = $cantidades[$index % count($cantidades)];
            $vueltas = range(1, $limite);

            foreach ($vueltas as $i => $vuelta) {
                FavouriteArtists::create([
                    'user_id'   => $customerId,
                    'artist_id' => $artistIds[($index + $i) % count($artistIds)],
                ]);
            }
        }
    }
}
