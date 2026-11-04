<?php

namespace Database\Seeders;

use App\Models\Artist;
use App\Models\ArtistPayoutMethod;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class ArtistPayoutMethodSeeder extends Seeder
{
    public function run()
    {
        Schema::disableForeignKeyConstraints();
        DB::table('artist_payout_methods')->truncate();
        Schema::enableForeignKeyConstraints();

        $banks = [
            'BBVA México',
            'Banorte',
            'Santander',
            'HSBC México',
            'Banco Azteca',
            'Scotiabank',
            'Banco Inbursa',
        ];

        $artists = Artist::orderBy('id')->get();

        foreach ($artists as $artist) {
            $clabe = '002' . str_pad((string) (100000000000 + $artist->id), 15, '0', STR_PAD_LEFT);

            ArtistPayoutMethod::create([
                'artist_id'      => $artist->id,
                'bank_name'      => $banks[array_rand($banks)],
                'account_holder' => $artist->name,
                'clabe'          => $clabe,
                'rfc'            => 'XAXX010101' . str_pad((string) $artist->id, 3, '0', STR_PAD_LEFT),
            ]);
        }
    }
}
