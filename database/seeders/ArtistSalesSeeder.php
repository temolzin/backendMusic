<?php

namespace Database\Seeders;

use App\Models\ArtistSale;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ArtistSalesSeeder extends Seeder
{
    public function run()
    {
        DB::statement('TRUNCATE TABLE artist_sales RESTART IDENTITY CASCADE;');

        $artistIds   = range(2, 16);
        $customerIds = [17, 18];
        $amounts     = [6000, 9000, 12000];

        foreach ($artistIds as $artistId) {
            foreach ($amounts as $index => $amount) {
                ArtistSale::create([
                    'artist_id'              => $artistId,
                    'customer_id'            => $customerIds[$index % count($customerIds)],
                    'amount'                 => $amount,
                    'openpay_transaction_id' => 'trx_test_' . $artistId . '_' . ($index + 1),
                ]);
            }
        }
    }
}
