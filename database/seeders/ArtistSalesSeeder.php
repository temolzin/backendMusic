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
        $amounts     = [6000, 9000, 12000];
        
        $customers = User::whereIn('id', $customerIds)->get()->keyBy('id');

        if (empty($artistIds)) {
            throw new \RuntimeException('No hay artistas sembrados para generar artist_sales.');
        }

        $eventHours = ['08:00', '10:00', '14:00', '16:00', '18:00', '20:00'];

        foreach ($artistIds as $index => $artistId) {
            for ($i = 0; $i < 1; $i++) {
                $amount     = $amounts[($index + $i) % count($amounts)];
                $customerId = $customerIds[($index + $i) % count($customerIds)];
                $customer   = $customers[$customerId];
            
                ArtistSale::create([
                    'artist_id'              => $artistId,
                    'customer_id'            => $customerId,
                    'amount'                 => $amount,
                    'openpay_transaction_id' => 'trx_test_' . $artistId . '_1',
                    'customer_first_name'    => explode(' ', $customer->name)[0],
                    'customer_last_name'     => explode(' ', $customer->name)[1] ?? 'Usuario',
                    'customer_email'         => $customer->email,
                    'customer_phone'         => '5512345678',
                    'customer_address'       => 'Calle Principal 123',
                    'customer_city'          => 'Ciudad de México',
                    'customer_state'         => 'CDMX',
                    'customer_zip_code'      => '28001',
                    'event_date'             => now()->addDays($index + 1)->format('Y-m-d'),
                    'event_hour'             => $eventHours[$index % count($eventHours)],
                    'event_hours'            => rand(2, 5),
                    'event_status'           => 'pending',
                ]);
            }
        }
    }
}
