<?php

namespace Database\Seeders;

use App\Models\ArtistSale;
use App\Models\EventCancellation;
use App\Models\SupportTicket;
use App\Models\User;
use App\Models\UserSanction;
use Carbon\Carbon;
use Illuminate\Database\Seeder;

class UserSanctionSeeder extends Seeder
{
    public function run()
    {
        $sale = ArtistSale::first();
        $adminUser = User::first();

        $artistAccount = User::where('email', 'miguel@gmail.com')->first();
        $clientAccount = User::where('email', 'carlosramirez@gmail.com')->first();

        $targets = [
            'artista' => $artistAccount,
            'cliente' => $clientAccount,
        ];

        $targets = array_filter($targets);

        if (empty($targets) || !$sale || !$adminUser) {
            return;
        }

        $sanctionByRole = [
            'artista' => [
                'type' => 'restricted',
                'days' => 7,
                'reason' => 'Uso de lenguaje inapropiado en el chat del evento.',
                'source' => 'ticket',
                'created_by' => 'admin',
            ],
            'cliente' => [
                'type' => 'restricted',
                'days' => null,
                'reason' => 'Intento de engaño al cliente fuera de la plataforma.',
                'source' => 'ticket',
                'created_by' => 'admin',
            ],
        ];

        foreach ($targets as $role => $user) {
            $pattern = $sanctionByRole[$role];
            $user->update(['account_status' => $pattern['type']]);
            $sanctionableType = null;
            $sanctionableId = null;

            if ($pattern['source'] === 'ticket') {
                $ticket = SupportTicket::create([
                    'artist_sale_id' => $sale->id,
                    'reporter_user_id' => $adminUser->id,
                    'category' => 'bad_service',
                    'description' => 'Reporte autogenerado para justificar sanción: ' . $pattern['reason'],
                    'status' => SupportTicket::STATUS_RESOLVED,
                    'resolution_type' => 'partial_refund',
                ]);
                $sanctionableType = SupportTicket::class;
                $sanctionableId = $ticket->id;
            }
            $endsAt = $pattern['days'] ? Carbon::now()->addDays($pattern['days']) : null;
            $creator = (string) $adminUser->id;

            UserSanction::create([
                'user_id' => $user->id,
                'sanctionable_type' => $sanctionableType,
                'sanctionable_id' => $sanctionableId,
                'type' => $pattern['type'],
                'reason' => $pattern['reason'],
                'starts_at' => Carbon::now(),
                'ends_at' => $endsAt,
                'created_by' => $creator,
            ]);

            $this->createCancellationHistory($user, $role, $pattern['reason']);
        }
    }

    private function createCancellationHistory(User $user, string $role, string $reason)
    {
        $saleQuery = match ($role) {
            'artista' => ArtistSale::whereHas('artist', function ($query) use ($user) {
                $query->where('user_id', $user->id);
            })->orderBy('event_date'),
            'cliente' => ArtistSale::where('customer_id', $user->id)->orderBy('event_date'),
            default => null,
        };

        $sale = $saleQuery?->first();

        if (!$sale) {
            return;
        }

        $penaltyPercentage = 30;
        $sale->event_date = Carbon::now()->addDays(4)->toDateString();
        $sale->event_status = ArtistSale::EVENT_STATUS_CANCELLED;
        $sale->approval_status = ArtistSale::APPROVAL_STATUS_CANCELLED;
        $sale->approval_responded_at = Carbon::now();
        $sale->save();

        $cancellation = new EventCancellation([
            'artist_sale_id' => $sale->id,
            'user_id' => $user->id,
            'cancellation_reason' => 'Cancelación registrada para historial: ' . $reason,
            'penalty_percentage' => $penaltyPercentage,
            'penalty_amount' => round(floatval($sale->amount) * ($penaltyPercentage / 100), 2),
            'refunded_at' => null,
            'penalty_paid' => true,
        ]);
        $cancellation->created_at = Carbon::now()->addMinute();
        $cancellation->save();
    }
}
