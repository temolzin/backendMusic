<?php

namespace Database\Seeders;

use App\Models\ArtistRating;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ArtistRatingSeeder extends Seeder
{
    public function run()
    {
        DB::statement('TRUNCATE TABLE artist_ratings RESTART IDENTITY CASCADE;');

        $ratings = [
            ['user_id' => 17, 'artist_id' => 1,  'rating' => 5],
            ['user_id' => 17, 'artist_id' => 2,  'rating' => 4],
            ['user_id' => 17, 'artist_id' => 3,  'rating' => 5],
            ['user_id' => 17, 'artist_id' => 4,  'rating' => 3],
            ['user_id' => 17, 'artist_id' => 5,  'rating' => 4],
            ['user_id' => 17, 'artist_id' => 6,  'rating' => 5],
            ['user_id' => 17, 'artist_id' => 7,  'rating' => 4],
            ['user_id' => 17, 'artist_id' => 8,  'rating' => 3],
            ['user_id' => 17, 'artist_id' => 9,  'rating' => 5],
            ['user_id' => 17, 'artist_id' => 10, 'rating' => 4],
            ['user_id' => 17, 'artist_id' => 11, 'rating' => 5],
            ['user_id' => 17, 'artist_id' => 12, 'rating' => 3],
            ['user_id' => 17, 'artist_id' => 13, 'rating' => 4],
            ['user_id' => 17, 'artist_id' => 14, 'rating' => 5],
            ['user_id' => 17, 'artist_id' => 15, 'rating' => 4],

            ['user_id' => 18, 'artist_id' => 1,  'rating' => 4],
            ['user_id' => 18, 'artist_id' => 2,  'rating' => 5],
            ['user_id' => 18, 'artist_id' => 3,  'rating' => 3],
            ['user_id' => 18, 'artist_id' => 4,  'rating' => 4],
            ['user_id' => 18, 'artist_id' => 5,  'rating' => 5],
            ['user_id' => 18, 'artist_id' => 6,  'rating' => 4],
            ['user_id' => 18, 'artist_id' => 7,  'rating' => 5],
            ['user_id' => 18, 'artist_id' => 8,  'rating' => 4],
            ['user_id' => 18, 'artist_id' => 9,  'rating' => 3],
            ['user_id' => 18, 'artist_id' => 10, 'rating' => 5],
            ['user_id' => 18, 'artist_id' => 11, 'rating' => 4],
            ['user_id' => 18, 'artist_id' => 12, 'rating' => 5],
            ['user_id' => 18, 'artist_id' => 13, 'rating' => 3],
            ['user_id' => 18, 'artist_id' => 14, 'rating' => 4],
            ['user_id' => 18, 'artist_id' => 15, 'rating' => 5],

            ['user_id' => 1, 'artist_id' => 1,  'rating' => 5],
            ['user_id' => 1, 'artist_id' => 3,  'rating' => 4],
            ['user_id' => 1, 'artist_id' => 6,  'rating' => 5],
            ['user_id' => 1, 'artist_id' => 10, 'rating' => 3],
            ['user_id' => 1, 'artist_id' => 13, 'rating' => 4],

            ['user_id' => 2,  'artist_id' => 2,  'rating' => 4],
            ['user_id' => 2,  'artist_id' => 5,  'rating' => 3],
            ['user_id' => 2,  'artist_id' => 9,  'rating' => 5],

            ['user_id' => 3,  'artist_id' => 1,  'rating' => 3],
            ['user_id' => 3,  'artist_id' => 6,  'rating' => 4],
            ['user_id' => 3,  'artist_id' => 11, 'rating' => 5],

            ['user_id' => 4,  'artist_id' => 1,  'rating' => 4],
            ['user_id' => 4,  'artist_id' => 7,  'rating' => 5],
            ['user_id' => 4,  'artist_id' => 12, 'rating' => 3],

            ['user_id' => 5,  'artist_id' => 2,  'rating' => 5],
            ['user_id' => 5,  'artist_id' => 8,  'rating' => 4],
            ['user_id' => 5,  'artist_id' => 13, 'rating' => 3],

            ['user_id' => 6,  'artist_id' => 3,  'rating' => 4],
            ['user_id' => 6,  'artist_id' => 9,  'rating' => 5],
            ['user_id' => 6,  'artist_id' => 14, 'rating' => 4],

            ['user_id' => 7,  'artist_id' => 4,  'rating' => 3],
            ['user_id' => 7,  'artist_id' => 10, 'rating' => 5],
            ['user_id' => 7,  'artist_id' => 15, 'rating' => 4],

            ['user_id' => 8,  'artist_id' => 1,  'rating' => 5],
            ['user_id' => 8,  'artist_id' => 5,  'rating' => 4],
            ['user_id' => 8,  'artist_id' => 11, 'rating' => 3],

            ['user_id' => 9,  'artist_id' => 2,  'rating' => 4],
            ['user_id' => 9,  'artist_id' => 6,  'rating' => 5],
            ['user_id' => 9,  'artist_id' => 12, 'rating' => 4],

            ['user_id' => 10, 'artist_id' => 3,  'rating' => 3],
            ['user_id' => 10, 'artist_id' => 7,  'rating' => 5],
            ['user_id' => 10, 'artist_id' => 13, 'rating' => 4],
        ];

        foreach ($ratings as $rating) {
            ArtistRating::create($rating);
        }
    }
}
