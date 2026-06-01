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
        $quantities  = [2, 3, 5];

        foreach ($customerIds as $index => $customerId) {
            $limit = $quantities[$index % count($quantities)];
            $iterations = range(1, $limit);

            foreach ($iterations as $i => $iteration) {
                FavouriteArtists::create([
                    'user_id'   => $customerId,
                    'artist_id' => $artistIds[($index + $i) % count($artistIds)],
                ]);
            }
        }
    }
}
