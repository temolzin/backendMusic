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

        $patterns = [
            ['event_date' => '2026-04-20', 'payment_method' => 'cash',  'status' => 'pending'],
            ['event_date' => '2026-05-10', 'payment_method' => 'card',  'status' => 'completed'],
            ['event_date' => '2026-08-20', 'payment_method' => 'cash',  'status' => 'pending'],
            ['event_date' => '2026-09-15', 'payment_method' => 'card',  'status' => 'completed'],
        ];

        foreach ($customerIds as $customerIndex => $customerId) {
            $customer = $customers[$customerId];

            foreach ($artistIds as $index => $artistId) {
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
                    'event_status'           => 'pending',
                    'status'                 => $pattern['status'],
                ]);
            }
        }
    }
}
