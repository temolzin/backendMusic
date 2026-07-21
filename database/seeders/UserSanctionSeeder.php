<?php

namespace Database\Seeders;

use App\Models\ArtistSale;
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
        $candidates = User::has('roles')
            ->where('id', '!=', $adminUser->id)
            ->inRandomOrder()
            ->get();
        $users = collect();
        for ($i = 0; $i < 3; $i++) {
            if (isset($candidates[$i])) {
                $users->push($candidates[$i]);
            }
        }
        if ($users->isEmpty() || !$sale || !$adminUser) {
            return;
        }

        $sanctionsPattern = [
            [
                'type' => 'restricted',
                'days' => 7,
                'reason' => 'Uso de lenguaje inapropiado en el chat del evento.',
                'source' => 'ticket',
                'created_by' => 'admin',
            ],
            [
                'type' => 'restricted',
                'days' => null,
                'reason' => 'Intento de engaño al cliente fuera de la plataforma.',
                'source' => 'ticket',
                'created_by' => 'admin',
            ],
            [
                'type' => 'restricted',
                'days' => 30,
                'reason' => 'SISTEMA: Restricción automática por acumulación de faltas.',
                'source' => 'expired_approval',
                'created_by' => 'system',
            ]
        ];

        foreach ($users as $index => $user) {
            $pattern = $sanctionsPattern[$index % count($sanctionsPattern)];
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
            if ($pattern['source'] === 'expired_approval') {
                $sanctionableType = ArtistSale::class;
                $sanctionableId = $sale->id;
            }
            $endsAt = $pattern['days'] ? Carbon::now()->addDays($pattern['days']) : null;
            $creator = $pattern['created_by'] === 'system' ? 'system' : (string) $adminUser->id;

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
        }
    }
}
