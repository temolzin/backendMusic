<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class FavouriteArtistsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::statement('TRUNCATE TABLE favourite_artists RESTART IDENTITY CASCADE;');

        $favourites = [
            ['user_id' => 17, 'artist_id' => 1],
            ['user_id' => 17, 'artist_id' => 5],
            ['user_id' => 17, 'artist_id' => 9],
            ['user_id' => 17, 'artist_id' => 12],
            ['user_id' => 17, 'artist_id' => 15],

            ['user_id' => 18, 'artist_id' => 2],
            ['user_id' => 18, 'artist_id' => 6],
            ['user_id' => 18, 'artist_id' => 10],

            ['user_id' => 1, 'artist_id' => 3],
            ['user_id' => 1, 'artist_id' => 7],

            ['user_id' => 2, 'artist_id' => 4],
            ['user_id' => 2, 'artist_id' => 8],
            ['user_id' => 2, 'artist_id' => 11],
            ['user_id' => 2, 'artist_id' => 13],
            ['user_id' => 2, 'artist_id' => 14],

        ];

        foreach ($favourites as $favourite) {
            DB::table('favourite_artists')->insert([
                'user_id'    => $favourite['user_id'],
                'artist_id'  => $favourite['artist_id'],
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }
}
