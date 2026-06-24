<?php

namespace Database\Seeders;

use App\Models\ArtistSale;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ArtistSalesSeeder extends Seeder
{
    public function run()
    {
        DB::statement('TRUNCATE TABLE artist_sales RESTART IDENTITY CASCADE;');

        $artistIds   = DB::table('artists')->orderBy('id')->pluck('id')->all();
        $customerIds = [17, 18];
        $customers   = User::whereIn('id', $customerIds)->get()->keyBy('id');
        $eventHours  = ['08:00', '10:00', '14:00', '16:00', '18:00', '20:00'];
        $amounts     = [6000, 9000, 12000];

        $half = (int) ceil(count($artistIds) / 2);
        $artistsPerCustomer = [
            17 => array_slice($artistIds, 0, $half),
            18 => array_slice($artistIds, $half),
        ];

        $patternsPerCustomer = [
            17 => [
                ['event_date' => '2026-03-10', 'created_at' => '2026-01-15 10:00:00', 'payment_method' => 'card',  'status' => 'completed', 'event_status' => 'completed'],
                ['event_date' => '2026-08-20', 'created_at' => '2026-06-01 10:00:00', 'payment_method' => 'cash',  'status' => 'pending',   'event_status' => 'pending'],
                ['event_date' => '2026-10-05', 'created_at' => '2026-06-10 10:00:00', 'payment_method' => 'card',  'status' => 'completed', 'event_status' => 'pending'],
            ],
            18 => [
                ['event_date' => '2026-04-15', 'created_at' => '2026-02-20 10:00:00', 'payment_method' => 'cash',  'status' => 'pending',   'event_status' => 'completed'],
                ['event_date' => '2026-09-10', 'created_at' => '2026-05-05 10:00:00', 'payment_method' => 'card',  'status' => 'completed', 'event_status' => 'pending'],
                ['event_date' => '2026-11-20', 'created_at' => '2026-06-20 10:00:00', 'payment_method' => 'cash',  'status' => 'pending',   'event_status' => 'pending'],
            ],
        ];

        foreach ($customerIds as $customerId) {
            $customer = $customers[$customerId];
            $artists  = $artistsPerCustomer[$customerId];
            $patterns = $patternsPerCustomer[$customerId];

            foreach ($artists as $index => $artistId) {
                $pattern = $patterns[$index % count($patterns)];

                ArtistSale::create([
                    'artist_id'              => $artistId,
                    'customer_id'            => $customerId,
                    'amount'                 => $amounts[$index % count($amounts)],
                    'openpay_transaction_id' => 'trx_test_' . $artistId . '_' . $customerId,
                    'customer_first_name'    => explode(' ', $customer->name)[0],
                    'customer_last_name'     => explode(' ', $customer->name)[1] ?? 'Usuario',
                    'customer_email'         => $customer->email,
                    'customer_phone'         => '5512345678',
                    'customer_address'       => 'Calle Principal 123',
                    'customer_city'          => 'Ciudad de México',
                    'customer_state'         => 'CDMX',
                    'customer_zip_code'      => '28001',
                    'event_date'             => $pattern['event_date'],
                    'event_hour'             => $eventHours[$index % count($eventHours)],
                    'event_hours'            => rand(2, 5),
                    'payment_method'         => $pattern['payment_method'],
                    'event_status'           => $pattern['event_status'],
                    'status'                 => $pattern['status'],
                    'created_at'             => $pattern['created_at'],
                    'updated_at'             => $pattern['created_at'],
                ]);
            }
        }
    }
}
