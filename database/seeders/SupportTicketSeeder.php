<?php

namespace Database\Seeders;

use App\Models\Artist;
use App\Models\ArtistSale;
use App\Models\SupportTicket;
use App\Models\TicketLog;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class SupportTicketSeeder extends Seeder
{
    public function run()
    {
        DB::statement('TRUNCATE TABLE ticket_logs, support_tickets RESTART IDENTITY CASCADE;');

        $clientCategories = ['no_show', 'delay', 'bad_service', 'cancellation', 'other'];
        $artistCategories = ['cancellation', 'other'];
        $statuses = [
            SupportTicket::STATUS_OPEN,
            SupportTicket::STATUS_UNDER_REVIEW,
            SupportTicket::STATUS_RESOLVED,
            SupportTicket::STATUS_REJECTED,
        ];
        $resolutionTypes = ['full_refund', 'partial_refund', 'no_action'];

        $artists = Artist::query()->orderBy('id')->get(['id', 'user_id', 'name']);
        $clients = User::whereHas('roles', function ($query) {
            $query->where('slug', User::ROLE_CLIENT);
        })->orderBy('id')->get(['id', 'name']);

        $artistLimit = max(1, intdiv($artists->count(), 2));
        $clientLimit = max(1, intdiv($clients->count(), 2));

        $this->createArtistTickets($artists->take($artistLimit), $artistCategories, $statuses, $resolutionTypes);
        $this->createClientTickets($clients->take($clientLimit), $clientCategories, $statuses, $resolutionTypes);
    }

    private function createArtistTickets($artists, array $categories, array $statuses, array $resolutionTypes)
    {
        foreach ($artists->values() as $index => $artist) {
            $sale = ArtistSale::query()
                ->where('artist_id', $artist->id)
                ->whereDate('event_date', '<=', Carbon::today())
                ->orderBy('event_date')
                ->orderBy('id')
                ->first();

            if (!$sale) {
                $sale = ArtistSale::query()
                    ->where('artist_id', $artist->id)
                    ->orderBy('event_date')
                    ->orderBy('id')
                    ->first();
            }

            if (!$sale) {
                continue;
            }

            $this->createTicket(
                $sale,
                $artist->user_id,
                $categories[$index % count($categories)],
                $statuses[$index % count($statuses)],
                $resolutionTypes[$index % count($resolutionTypes)]
            );
        }
    }

    private function createClientTickets($clients, array $categories, array $statuses, array $resolutionTypes)
    {
        foreach ($clients->values() as $index => $client) {
            $sale = ArtistSale::query()
                ->where('customer_id', $client->id)
                ->whereDate('event_date', '<=', Carbon::today())
                ->orderBy('event_date')
                ->orderBy('id')
                ->first();

            if (!$sale) {
                $sale = ArtistSale::query()
                    ->where('customer_id', $client->id)
                    ->orderBy('event_date')
                    ->orderBy('id')
                    ->first();
            }

            if (!$sale) {
                continue;
            }

            $this->createTicket(
                $sale,
                $client->id,
                $categories[$index % count($categories)],
                $statuses[$index % count($statuses)],
                $resolutionTypes[$index % count($resolutionTypes)]
            );
        }
    }

    private function createTicket(ArtistSale $sale, int $reporterUserId, string $category, string $status, string $resolutionType)
    {
        if (in_array($status, [SupportTicket::STATUS_OPEN, SupportTicket::STATUS_UNDER_REVIEW], true)) {
            $resolutionType = null;
        }

        $ticket = SupportTicket::create([
            'artist_sale_id'   => $sale->id,
            'reporter_user_id' => $reporterUserId,
            'category'         => $category,
            'description'      => 'Ticket de soporte generado por seeder para la venta #' . $sale->id . '.',
            'status'           => $status,
            'resolution_type'  => $resolutionType,
        ]);

        TicketLog::create([
            'support_ticket_id'  => $ticket->id,
            'changed_by_user_id' => $reporterUserId,
            'status'             => $status,
            'resolution_type'    => $resolutionType,
            'notes'              => 'Ticket creado por seeder.',
        ]);
    }
}
