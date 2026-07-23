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

        $eventHours     = ['08:00', '10:00', '14:00', '16:00', '18:00', '20:00'];
        $amounts        = [6000, 9000, 12000];
        $eventDateOffsets = [-90, -60, -30, -15, 15, 30, 60, 90, 120, 150];
        $createdAtOffsets = [-150, -120, -90, -75, -60, -30, -20, -10];
        $paymentMethods   = ['card', 'cash'];
        $salesPattern = [
            ['status' => ArtistSale::PAYMENT_STATUS_COMPLETED, 'event_status' => ArtistSale::EVENT_STATUS_COMPLETED, 'approval_status' => ArtistSale::APPROVAL_STATUS_ACCEPTED],
            ['status' => ArtistSale::PAYMENT_STATUS_COMPLETED, 'event_status' => ArtistSale::EVENT_STATUS_PENDING,   'approval_status' => ArtistSale::APPROVAL_STATUS_ACCEPTED],
            ['status' => ArtistSale::PAYMENT_STATUS_PENDING,   'event_status' => ArtistSale::EVENT_STATUS_PENDING,  'approval_status' => ArtistSale::APPROVAL_STATUS_PENDING],
        ];

        foreach ($customers as $customerIndex => $customer) {
            for ($j = 0; $j < count($salesPattern); $j++) {
                $artistId = $artistIds[($customerIndex + $j) % count($artistIds)];
                $statusPattern = $salesPattern[$j];

                $paymentMethod = $paymentMethods[($customerIndex + $j) % count($paymentMethods)];

                $amount = $amounts[($customerIndex + $j) % count($amounts)];
                $openpayFee = ($paymentMethod === 'card')
                    ? round(($amount * 0.029) * 1.16, 2)
                    : 0.00;

                $eventDate = Carbon::now()->addDays($eventDateOffsets[($customerIndex + $j) % count($eventDateOffsets)])->format('Y-m-d');
                $createdAt = Carbon::now()->addDays($createdAtOffsets[($customerIndex + $j) % count($createdAtOffsets)]);

                $approvalStatus = $statusPattern['approval_status'];
                $approvalDeadline = null;
                $approvalRespondedAt = null;

                if ($approvalStatus === ArtistSale::APPROVAL_STATUS_PENDING) {
                    $approvalDeadline = Carbon::now()->addHours(24);
                } elseif ($approvalStatus === ArtistSale::APPROVAL_STATUS_ACCEPTED) {
                    $approvalDeadline = (clone $createdAt)->addHours(24);
                    $approvalRespondedAt = (clone $createdAt)->addHours(2);
                } elseif (in_array($approvalStatus, [ArtistSale::APPROVAL_STATUS_REJECTED, ArtistSale::APPROVAL_STATUS_EXPIRED])) {
                    $approvalDeadline = (clone $createdAt)->addHours(24);
                    $approvalRespondedAt = $approvalStatus === ArtistSale::APPROVAL_STATUS_REJECTED
                        ? (clone $createdAt)->addHours(4) : null;
                }

                $sale = ArtistSale::create([
                    'artist_id'              => $artistId,
                    'customer_id'            => $customer->id,
                    'amount'                 => $amount,
                    'openpay_fee'            => $openpayFee,
                    'openpay_transaction_id' => $paymentMethod === 'cash' && $statusPattern['status'] === ArtistSale::PAYMENT_STATUS_PENDING ? null : 'trx_test_' . $artistId . '_' . $customer->id,
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
                    'approval_status'        => $approvalStatus,
                    'approval_deadline'      => $approvalDeadline,
                    'approval_responded_at'  => $approvalRespondedAt,
                    'created_at'             => $createdAt,
                    'updated_at'             => $createdAt,
                ]);

                if ($paymentMethod === 'cash') {
                    $isPending = $statusPattern['status'] === ArtistSale::PAYMENT_STATUS_PENDING;

                    $sale->cashReference()->create([
                        'cash_reference'    => 'REF-' . strtoupper(uniqid()),
                        'cash_barcode_url'  => 'https://sandbox-dashboard.openpay.mx/barcode/test_' . $sale->id . '.png',
                        'cash_due_date'     => $isPending
                            ? Carbon::now()->addDays(3)
                            : Carbon::parse($createdAt)->addDays(3),
                    ]);
                }
            }
        }
    }
}
