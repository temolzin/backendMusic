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

        $sales = [
            ['artist_id' => 1,  'customer_id' => 17, 'amount' => 9000,  'openpay_transaction_id' => 'trx_test_001'],
            ['artist_id' => 1,  'customer_id' => 18, 'amount' => 6000,  'openpay_transaction_id' => 'trx_test_002'],
            ['artist_id' => 2,  'customer_id' => 1,  'amount' => 12000, 'openpay_transaction_id' => 'trx_test_003'],
            ['artist_id' => 2,  'customer_id' => 2,  'amount' => 5000,  'openpay_transaction_id' => 'trx_test_004'],
            ['artist_id' => 3,  'customer_id' => 3,  'amount' => 15000, 'openpay_transaction_id' => 'trx_test_005'],
            ['artist_id' => 3,  'customer_id' => 4,  'amount' => 9000,  'openpay_transaction_id' => 'trx_test_006'],
            ['artist_id' => 4,  'customer_id' => 5,  'amount' => 6000,  'openpay_transaction_id' => 'trx_test_007'],
            ['artist_id' => 4,  'customer_id' => 6,  'amount' => 12000, 'openpay_transaction_id' => 'trx_test_008'],
            ['artist_id' => 5,  'customer_id' => 7,  'amount' => 9000,  'openpay_transaction_id' => 'trx_test_009'],
            ['artist_id' => 5,  'customer_id' => 8,  'amount' => 6000,  'openpay_transaction_id' => 'trx_test_010'],
            ['artist_id' => 6,  'customer_id' => 9,  'amount' => 15000, 'openpay_transaction_id' => 'trx_test_011'],
            ['artist_id' => 6,  'customer_id' => 10, 'amount' => 9000,  'openpay_transaction_id' => 'trx_test_012'],
            ['artist_id' => 7,  'customer_id' => 1,  'amount' => 6000,  'openpay_transaction_id' => 'trx_test_013'],
            ['artist_id' => 7,  'customer_id' => 2,  'amount' => 12000, 'openpay_transaction_id' => 'trx_test_014'],
            ['artist_id' => 8,  'customer_id' => 3,  'amount' => 9000,  'openpay_transaction_id' => 'trx_test_015'],
            ['artist_id' => 8,  'customer_id' => 4,  'amount' => 6000,  'openpay_transaction_id' => 'trx_test_016'],
            ['artist_id' => 9,  'customer_id' => 5,  'amount' => 15000, 'openpay_transaction_id' => 'trx_test_017'],
            ['artist_id' => 9,  'customer_id' => 6,  'amount' => 9000,  'openpay_transaction_id' => 'trx_test_018'],
            ['artist_id' => 10, 'customer_id' => 7,  'amount' => 6000,  'openpay_transaction_id' => 'trx_test_019'],
            ['artist_id' => 10, 'customer_id' => 8,  'amount' => 12000, 'openpay_transaction_id' => 'trx_test_020'],
            ['artist_id' => 11, 'customer_id' => 9,  'amount' => 9000,  'openpay_transaction_id' => 'trx_test_021'],
            ['artist_id' => 11, 'customer_id' => 10, 'amount' => 6000,  'openpay_transaction_id' => 'trx_test_022'],
            ['artist_id' => 12, 'customer_id' => 17, 'amount' => 15000, 'openpay_transaction_id' => 'trx_test_023'],
            ['artist_id' => 12, 'customer_id' => 18, 'amount' => 9000,  'openpay_transaction_id' => 'trx_test_024'],
            ['artist_id' => 13, 'customer_id' => 1,  'amount' => 6000,  'openpay_transaction_id' => 'trx_test_025'],
            ['artist_id' => 13, 'customer_id' => 2,  'amount' => 12000, 'openpay_transaction_id' => 'trx_test_026'],
            ['artist_id' => 14, 'customer_id' => 3,  'amount' => 9000,  'openpay_transaction_id' => 'trx_test_027'],
            ['artist_id' => 14, 'customer_id' => 4,  'amount' => 6000,  'openpay_transaction_id' => 'trx_test_028'],
            ['artist_id' => 15, 'customer_id' => 5,  'amount' => 15000, 'openpay_transaction_id' => 'trx_test_029'],
            ['artist_id' => 15, 'customer_id' => 6,  'amount' => 9000,  'openpay_transaction_id' => 'trx_test_030'],
        ];

        foreach ($sales as $sale) {
            ArtistSale::create($sale);
        }
    }
}
