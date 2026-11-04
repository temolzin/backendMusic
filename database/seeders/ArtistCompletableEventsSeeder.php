<?php

namespace Database\Seeders;

use App\Models\Artist;
use App\Models\ArtistSale;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ArtistCompletableEventsSeeder extends Seeder
{
    public function run()
    {
        $artistIds = DB::table('artists')->orderBy('id')->pluck('id')->all();

        $customers = User::whereHas('roles', function ($q) {
            $q->where('slug', User::ROLE_CLIENT);
        })->orderBy('id')->get()->values();

        if (empty($artistIds) || $customers->isEmpty()) {
            return;
        }

        $paymentMethods = [ArtistSale::PAYMENT_METHOD_CARD, ArtistSale::PAYMENT_METHOD_CASH];
        $customerCursor = 0;

        foreach ($artistIds as $artistIndex => $artistId) {
            $artist = Artist::find($artistId);
            if (!$artist) {
                continue;
            }

            for ($eventIndex = 0; $eventIndex < 2; $eventIndex++) {
                $customer = $customers[$customerCursor % $customers->count()];
                $customerCursor++;

                $paymentMethod = $paymentMethods[($artistIndex + $eventIndex) % count($paymentMethods)];
                $eventHours = 1;
                $hoursAgoEnded = $eventIndex === 0 ? 3 : 8;
                $eventEnd = Carbon::now()->subHours($hoursAgoEnded);
                $eventStart = (clone $eventEnd)->subHours($eventHours);

                $eventDate = $eventStart->format('Y-m-d');
                $eventHour = $eventStart->format('H:i:s');

                $amount = (float) $artist->price_hour * $eventHours;
                $openpayFee = $paymentMethod === ArtistSale::PAYMENT_METHOD_CARD
                    ? round(($amount * 0.029) * 1.16, 2)
                    : 0.00;

                $createdAt = Carbon::now()->subDays(2);

                $sale = ArtistSale::create([
                    'artist_id' => $artist->id,
                    'customer_id' => $customer->id,
                    'amount' => $amount,
                    'openpay_fee' => $openpayFee,
                    'openpay_transaction_id' => 'trx_completable_' . $artist->id . '_' . $customer->id . '_' . $eventIndex,
                    'customer_first_name' => explode(' ', $customer->name)[0],
                    'customer_last_name' => explode(' ', $customer->name)[1] ?? 'Usuario',
                    'customer_email' => $customer->email,
                    'customer_phone' => '5512345678',
                    'customer_address' => 'Calle Principal 123',
                    'customer_city' => 'Ciudad de México',
                    'customer_state' => 'CDMX',
                    'customer_zip_code' => '28001',
                    'event_date' => $eventDate,
                    'event_hour' => $eventHour,
                    'event_hours' => $eventHours,
                    'payment_method' => $paymentMethod,
                    'event_status' => ArtistSale::EVENT_STATUS_PENDING,
                    'status' => ArtistSale::PAYMENT_STATUS_COMPLETED,
                    'approval_status' => ArtistSale::APPROVAL_STATUS_ACCEPTED,
                    'approval_deadline' => (clone $createdAt)->addHours(24),
                    'approval_responded_at' => (clone $createdAt)->addHours(2),
                    'created_at' => $createdAt,
                    'updated_at' => $createdAt,
                ]);

                if ($paymentMethod === ArtistSale::PAYMENT_METHOD_CASH) {
                    $sale->cashReference()->create([
                        'cash_reference' => 'REF-' . strtoupper(uniqid()),
                        'cash_barcode_url' => 'https://sandbox-dashboard.openpay.mx/barcode/test_' . $sale->id . '.png',
                        'cash_due_date' => Carbon::parse($createdAt)->addDays(3),
                    ]);
                }
            }
        }
    }
}
