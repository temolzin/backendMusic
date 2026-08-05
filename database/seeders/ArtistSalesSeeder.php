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
        $this->call(ArtistPayoutMethodSeeder::class);

        $artistIds = DB::table('artists')->orderBy('id')->pluck('id')->all();
        $customers = User::whereHas('roles', function ($q) {
            $q->where('slug', User::ROLE_CLIENT);
        })->get()->values();

        if (empty($artistIds) || $customers->isEmpty()) {
            return;
        }

        $eventHours = ['08:00', '10:00', '14:00', '16:00', '18:00', '20:00'];
        $amounts = [6000, 9000, 12000];
        $createdAtOffsets = [-150, -120, -90, -75, -60, -30, -20, -10];
        $paymentMethods = ['card', 'cash'];
        $artistEventCounts = [];
        $eventStatusCycle = [
            ArtistSale::EVENT_STATUS_COMPLETED,
            ArtistSale::EVENT_STATUS_PENDING,
            ArtistSale::EVENT_STATUS_EXPIRED,
        ];

        foreach ($artistIds as $artistIndex => $artistId) {
            for ($j = 0; $j < 1; $j++) {
                $customer = $customers[($artistIndex + $j) % count($customers)];
                $statusIndex = ($artistIndex + $j) % count($eventStatusCycle);
                $eventStatus = $eventStatusCycle[$statusIndex];
                $artistSaleIndex = $artistEventCounts[$artistId] ?? 0;
                $artistEventCounts[$artistId] = $artistSaleIndex + 1;

                $paymentMethod = $paymentMethods[($artistIndex + $j) % count($paymentMethods)];

                $amount = $amounts[($artistIndex + $j) % count($amounts)];
                $openpayFee = ($paymentMethod === 'card')
                    ? round(($amount * 0.029) * 1.16, 2)
                    : 0.00;

                $eventDate = $this->buildEventDate($eventStatus, $artistSaleIndex);
                $createdAt = Carbon::now()->addDays($createdAtOffsets[($artistIndex + $j) % count($createdAtOffsets)]);

                $approvalStatus = $eventStatus === ArtistSale::EVENT_STATUS_PENDING
                    ? ArtistSale::APPROVAL_STATUS_PENDING
                    : ArtistSale::APPROVAL_STATUS_ACCEPTED;

                $respondHours = [
                    ArtistSale::APPROVAL_STATUS_ACCEPTED => 2,
                    ArtistSale::APPROVAL_STATUS_REJECTED => 4,
                ];

                $approvalDeadline = $approvalStatus === ArtistSale::APPROVAL_STATUS_PENDING
                    ? Carbon::now()->addHours(24)
                    : (clone $createdAt)->addHours(24);

                $approvalRespondedAt = isset($respondHours[$approvalStatus])
                    ? (clone $createdAt)->addHours($respondHours[$approvalStatus])
                    : null;

                $sale = ArtistSale::create([
                    'artist_id' => $artistId,
                    'customer_id' => $customer->id,
                    'amount' => $amount,
                    'openpay_fee' => $openpayFee,
                    'openpay_transaction_id' => $paymentMethod === 'cash' && $eventStatus === ArtistSale::EVENT_STATUS_PENDING ? null : 'trx_test_' . $artistId . '_' . $customer->id,
                    'customer_first_name'    => explode(' ', $customer->name)[0],
                    'customer_last_name'     => explode(' ', $customer->name)[1] ?? 'Usuario',
                    'customer_email' => $customer->email,
                    'customer_phone' => '5512345678',
                    'customer_address' => 'Calle Principal 123',
                    'customer_city' => 'Ciudad de México',
                    'customer_state' => 'CDMX',
                    'customer_zip_code' => '28001',
                    'event_date' => $eventDate->format('Y-m-d'),
                    'event_hour' => $eventHours[array_rand($eventHours)],
                    'event_hours' => rand(2, 5),
                    'payment_method' => $paymentMethod,
                    'event_status' => $this->resolveEventStatus($eventDate),
                    'status' => $eventStatus === ArtistSale::EVENT_STATUS_PENDING
                        ? ArtistSale::PAYMENT_STATUS_PENDING
                        : ArtistSale::PAYMENT_STATUS_COMPLETED,
                    'approval_status' => $approvalStatus,
                    'approval_deadline' => $approvalDeadline,
                    'approval_responded_at' => $approvalRespondedAt,
                    'created_at' => $createdAt,
                    'updated_at' => $createdAt,
                ]);

                if ($paymentMethod === 'cash') {
                    $isPending = $eventStatus === ArtistSale::EVENT_STATUS_PENDING;

                    $sale->cashReference()->create([
                        'cash_reference' => 'REF-' . strtoupper(uniqid()),
                        'cash_barcode_url' => 'https://sandbox-dashboard.openpay.mx/barcode/test_' . $sale->id . '.png',
                        'cash_due_date' => $isPending
                            ? Carbon::now()->addDays(3)
                            : Carbon::parse($createdAt)->addDays(3),
                    ]);
                }
            }
        }
    }

    private function buildEventDate(string $eventStatus, int $artistSaleIndex): Carbon
    {
        $baseDate = Carbon::now()->startOfDay();

        if ($eventStatus === ArtistSale::EVENT_STATUS_COMPLETED) {
            return $baseDate->copy()->subDays(7 + ($artistSaleIndex * 3));
        }

        if ($eventStatus === ArtistSale::EVENT_STATUS_PENDING) {
            return $baseDate->copy()->addDays(7 + ($artistSaleIndex * 3));
        }

        return $baseDate->copy()->subDays(45 + ($artistSaleIndex * 3));
    }

    private function resolveEventStatus(Carbon $eventDate): string
    {
        $today = Carbon::now()->startOfDay();

        if ($eventDate->greaterThan($today)) {
            return ArtistSale::EVENT_STATUS_PENDING;
        }

        if ($eventDate->lessThan($today->copy()->subDays(30))) {
            return ArtistSale::EVENT_STATUS_EXPIRED;
        }

        return ArtistSale::EVENT_STATUS_COMPLETED;
    }
}
