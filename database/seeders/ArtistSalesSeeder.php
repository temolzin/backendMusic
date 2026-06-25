<?php

namespace Database\Seeders;

use App\Models\ArtistSale;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ArtistSalesSeeder extends Seeder
{
    public function run()
    {
        DB::statement('TRUNCATE TABLE artist_sales RESTART IDENTITY CASCADE;');

        $artistIds = DB::table('artists')->orderBy('id')->pluck('id')->all();
        $customers = User::whereHas('roles', function ($q) {
            $q->where('slug', User::ROLE_CLIENT);
        })->get()->values();

        $eventHours     = ['08:00', '10:00', '14:00', '16:00', '18:00', '20:00'];
        $amounts        = [6000, 9000, 12000];
        $eventDateOffsets = [-90, -60, -30, -15, 15, 30, 60, 90, 120, 150];
        $createdAtOffsets = [-150, -120, -90, -75, -60, -30, -20, -10];
        $paymentMethods   = ['card', 'cash'];

        $salesPattern = [
            ['status' => 'completed', 'event_status' => 'completed'],
            ['status' => 'pending',   'event_status' => 'pending'],
            ['status' => 'pending',   'event_status' => 'pending'],
        ];

        for ($i = 0; $i < count($artistIds); $i++) {
            $artistId = $artistIds[$i];

            for ($j = 0; $j < count($salesPattern); $j++) {
                $customer      = $customers[array_rand($customers->all())];
                $statusPattern = $salesPattern[$j];
                
                $paymentMethod = $paymentMethods[array_rand($paymentMethods)];
                
                $amount = $amounts[array_rand($amounts)];
                $openpayFee = ($paymentMethod === 'card') 
                    ? round(($amount * 0.029) * 1.16, 2) 
                    : 0.00;

                $eventDate = Carbon::now()->addDays($eventDateOffsets[array_rand($eventDateOffsets)])->format('Y-m-d');
                $createdAt = Carbon::now()->addDays($createdAtOffsets[array_rand($createdAtOffsets)])->format('Y-m-d H:i:s');

                ArtistSale::create([
                    'artist_id'              => $artistId,
                    'customer_id'            => $customer->id,
                    'amount'                 => $amount,
                    'openpay_fee'            => $openpayFee,
                    'openpay_transaction_id' => 'trx_test_' . $artistId . '_' . $customer->id . '_' . $j,
                    'customer_first_name'    => explode(' ', $customer->name)[0],
                    'customer_last_name'     => explode(' ', $customer->name)[1] ?? 'Usuario',
                    'customer_email'         => $customer->email,
                    'customer_phone'         => '5512345678',
                    'customer_address'       => 'Calle Principal 123',
                    'customer_city'          => 'Ciudad de México',
                    'customer_state'         => 'CDMX',
                    'customer_zip_code'      => '28001',
                    'event_date'             => $eventDate,
                    'event_hour'             => $eventHours[array_rand($eventHours)],
                    'event_hours'            => rand(2, 5),
                    'payment_method'         => $paymentMethod,
                    'event_status'           => $statusPattern['event_status'],
                    'status'                 => $statusPattern['status'],
                    'created_at'             => $createdAt,
                    'updated_at'             => $createdAt,
                ]);
            }
        }
    }
}
