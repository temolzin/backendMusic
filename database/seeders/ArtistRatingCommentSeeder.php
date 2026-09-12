<?php

namespace Database\Seeders;

use App\Models\Artist;
use App\Models\ArtistRating;
use App\Models\ArtistSale;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ArtistRatingCommentSeeder extends Seeder
{
    private const COMMENTS_PER_ARTIST = 4;

    public function run()
    {
        DB::statement('TRUNCATE TABLE artist_ratings RESTART IDENTITY CASCADE;');

        $artists = Artist::all();

        if ($artists->isEmpty()) {
            throw new \RuntimeException('No hay artistas para sembrar comentarios.');
        }

        $goodSamples = [
            'Excelente grupo, cumplieron todo a tiempo y el ambiente estuvo increíble.',
            'Muy profesionales y el show fue espectacular, todos quedaron encantados.',
            'Gran experiencia, muy buen repertorio y atención al público impecable.',
        ];
        $badSamples = [
            'Llegaron tarde al evento y el set fue más corto de lo contratado.',
            'La calidad del sonido no fue la esperada y la comunicación dejó mucho que desear.',
            'No cumplieron con lo acordado en el contrato, se notó poco compromiso.',
        ];

        foreach ($artists as $artist) {
            $sales = $this->ensureSales($artist->id);

            ArtistRating::updateOrCreate(
                ['artist_sale_id' => $sales[0]->id, 'artist_id' => $artist->id],
                ['rating' => 5, 'comment' => $goodSamples[0]]
            );
            ArtistRating::updateOrCreate(
                ['artist_sale_id' => $sales[1]->id, 'artist_id' => $artist->id],
                ['rating' => 4, 'comment' => $goodSamples[1]]
            );
            ArtistRating::updateOrCreate(
                ['artist_sale_id' => $sales[2]->id, 'artist_id' => $artist->id],
                ['rating' => 2, 'comment' => $badSamples[0]]
            );
            ArtistRating::updateOrCreate(
                ['artist_sale_id' => $sales[3]->id, 'artist_id' => $artist->id],
                ['rating' => 1, 'comment' => $badSamples[1]]
            );
        }
    }

    private function ensureSales($artistId)
    {
        $sales = ArtistSale::where('artist_id', $artistId)->get();

        while ($sales->count() < self::COMMENTS_PER_ARTIST) {
            $template = $sales->first();

            if (!$template) {
                throw new \RuntimeException("El artista {$artistId} no tiene ventas para usar como plantilla.");
            }

            $new = $template->replicate();
            $new->openpay_transaction_id = 'seed_' . $artistId . '_' . uniqid();
            $new->event_status = ArtistSale::EVENT_STATUS_COMPLETED;
            $new->status = ArtistSale::PAYMENT_STATUS_COMPLETED;
            $new->save();

            $sales->push($new);
        }

        return $sales->take(self::COMMENTS_PER_ARTIST)->values();
    }
}
