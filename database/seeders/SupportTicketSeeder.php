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

        $adminId = User::whereHas('roles', function ($query) {
            $query->where('slug', User::ROLE_ADMIN);
        })->orderBy('id')->value('id');

        $artists = Artist::query()->orderBy('id')->get(['id', 'user_id']);
        $clients = User::whereHas('roles', function ($query) {
            $query->where('slug', User::ROLE_CLIENT);
        })->orderBy('id')->get(['id']);

        $usedSaleIds  = [];
        $artistSeq    = 0;
        $clientSeq    = 0;

        $artistLimit = max(1, intdiv($artists->count(), 2));
        $clientLimit = max(1, intdiv($clients->count(), 2));

        $artistCategories = SupportTicket::CATEGORIES_ARTIST;
        $clientCategories = SupportTicket::CATEGORIES_CUSTOMER;

        foreach ($artists->take($artistLimit)->values() as $artist) {
            $sale = $this->findPastSale('artist_id', $artist->id, $usedSaleIds);
            $usedSaleIds[] = $sale?->id ?? -1;

            optional($sale, function (ArtistSale $found) use ($artist, $adminId, $artistSeq, $artistCategories) {
                $category = $artistCategories[$artistSeq % count($artistCategories)];
                $this->createTicketWithHistory(
                    $found,
                    $artist->user_id,
                    $category,
                    $this->artistStatusFor($artistSeq),
                    $adminId,
                    $artistSeq,
                    $this->artistDescriptions()[$category],
                    'artist',
                    $artistSeq
                );
            });
            $artistSeq++;
        }

        foreach ($clients->take($clientLimit)->values() as $client) {
            $sale = $this->findPastSale('customer_id', $client->id, $usedSaleIds);
            $usedSaleIds[] = $sale?->id ?? -1;

            optional($sale, function (ArtistSale $found) use ($client, $adminId, $clientSeq, $clientCategories) {
                $category = $clientCategories[$clientSeq % count($clientCategories)];
                $this->createTicketWithHistory(
                    $found,
                    $client->id,
                    $category,
                    $this->clientStatusFor($clientSeq),
                    $adminId,
                    $clientSeq,
                    $this->clientDescriptions()[$category],
                    'client',
                    $clientSeq
                );
            });
            $clientSeq++;
        }

        $this->ensureAgainstTickets($artists->take($artistLimit), $adminId, $usedSaleIds);
    }

    private function ensureAgainstTickets($artists, ?int $adminId, array &$usedSaleIds): void
    {
        $againstCategories = SupportTicket::CATEGORIES_CUSTOMER;

        $artistsNeedingTickets = collect($artists->values())->filter(
            fn (Artist $artist) => ! SupportTicket::whereHas('artistSale.artist', fn ($q) => $q->where('user_id', $artist->user_id))
                ->where('reporter_user_id', '!=', $artist->user_id)
                ->exists()
        )->values();

        $artistsNeedingTickets->reduce(function (int $seq, Artist $artist) use ($adminId, $usedSaleIds, $againstCategories) {
            $sale = ArtistSale::query()
                ->where('artist_id', $artist->id)
                ->whereDate('event_date', '<=', Carbon::today())
                ->orderByDesc('event_date')
                ->first();

            optional($sale, function (ArtistSale $found) use ($artist, $adminId, $seq, $againstCategories) {
                $category = $againstCategories[$seq % count($againstCategories)];
                $this->createTicketWithHistory(
                    $found,
                    $found->customer_id,
                    $category,
                    SupportTicket::STATUS_OPEN,
                    $adminId,
                    $seq,
                    $this->clientDescriptions()[$category],
                    'client',
                    $seq
                );
            });

            return $seq + 1;
        }, 0);
    }

    private function findPastSale(string $column, int $value, array $excludedSaleIds): ?ArtistSale
    {
        return ArtistSale::query()
            ->where($column, $value)
            ->whereDate('event_date', '<=', Carbon::today())
            ->when(!empty($excludedSaleIds), fn ($q) => $q->whereNotIn('id', $excludedSaleIds))
            ->orderByDesc('event_date')
            ->orderBy('id')
            ->first();
    }

    private function artistStatusFor(int $seq): string
    {
        $statuses = [
            SupportTicket::STATUS_OPEN,
            SupportTicket::STATUS_RESOLVED,
            SupportTicket::STATUS_UNDER_REVIEW,
            SupportTicket::STATUS_REJECTED,
        ];
        return $statuses[$seq % count($statuses)];
    }

    private function clientStatusFor(int $seq): string
    {
        $statuses = [
            SupportTicket::STATUS_UNDER_REVIEW,
            SupportTicket::STATUS_OPEN,
            SupportTicket::STATUS_REJECTED,
            SupportTicket::STATUS_RESOLVED,
        ];
        return $statuses[$seq % count($statuses)];
    }

    private function artistDescriptions(): array
    {
        return [
            SupportTicket::CATEGORY_NO_SHOW    => 'El cliente no estuvo presente en el lugar y hora acordados del evento.',
            SupportTicket::CATEGORY_BAD_SERVICE => 'El cliente tuvo un comportamiento agresivo e irrespetuoso con el artista y su equipo.',
            SupportTicket::CATEGORY_CANCELLATION => 'El cliente canceló el evento de último minuto sin aviso previo suficiente.',
            SupportTicket::CATEGORY_OTHER       => 'Situación adicional con el cliente que requiere revisión de soporte.',
        ];
    }

    private function clientDescriptions(): array
    {
        return [
            SupportTicket::CATEGORY_NO_SHOW    => 'El artista nunca se presentó al evento contratado y no respondió a los mensajes previos.',
            SupportTicket::CATEGORY_DELAY      => 'El artista llegó con más de una hora de retraso al evento.',
            SupportTicket::CATEGORY_BAD_SERVICE => 'El comportamiento del artista durante el evento fue inadecuado e irrespetuoso.',
            SupportTicket::CATEGORY_CANCELLATION => 'El artista canceló el evento de último minuto sin aviso previo suficiente.',
            SupportTicket::CATEGORY_OTHER       => 'Situación adicional con el artista que requiere revisión de soporte.',
        ];
    }

    private function createTicketWithHistory(
        ArtistSale $sale,
        int $reporterUserId,
        string $category,
        string $status,
        ?int $adminId,
        int $seq,
        string $description,
        string $direction,
        int $seqForDate
    ): void {
        $resolutionType = match ($status) {
            SupportTicket::STATUS_RESOLVED => SupportTicket::RESOLUTION_TYPES[$seqForDate % count(SupportTicket::RESOLUTION_TYPES)],
            SupportTicket::STATUS_REJECTED => 'no_action',
            default                        => null,
        };

        $openedAt   = $direction === 'artist'
            ? Carbon::now()->subDays(20 - ($seqForDate % 14))->setTime(10, 0)->addMinutes($seqForDate * 13)
            : Carbon::now()->subDays(18 - ($seqForDate % 12))->setTime(14, 30)->addMinutes($seqForDate * 17);
        $reviewerId = $adminId ?: $reporterUserId;

        $ticket = new SupportTicket([
            'artist_sale_id'   => $sale->id,
            'reporter_user_id' => $reporterUserId,
            'category'         => $category,
            'description'      => $description,
            'status'           => $status,
            'resolution_type'  => $resolutionType,
        ]);
        $ticket->created_at = $openedAt;
        $ticket->save();

        $logs = array_values(array_filter([
            $this->creationLog($ticket->id, $reporterUserId, $openedAt),
            $this->reviewLog($ticket->id, $reviewerId, $openedAt, $status),
            $this->closureLog($ticket->id, $reviewerId, $openedAt, $status, $resolutionType),
        ]));

        collect($logs)->each(function (array $data) {
            $createdAt = $data['created_at'];
            unset($data['created_at']);

            $log = new TicketLog($data);
            $log->created_at = $createdAt;
            $log->save();
        });

        $ticket->updated_at = $logs[count($logs) - 1]['created_at'];
        $ticket->save();
    }

    private function creationLog(int $ticketId, int $userId, Carbon $at): array
    {
        return [
            'support_ticket_id'  => $ticketId,
            'changed_by_user_id' => $userId,
            'status'             => SupportTicket::STATUS_OPEN,
            'resolution_type'    => null,
            'notes'              => 'Ticket creado.',
            'created_at'         => $at->copy(),
        ];
    }

    private function reviewLog(int $ticketId, int $userId, Carbon $at, string $status): ?array
    {
        return in_array($status, [
            SupportTicket::STATUS_UNDER_REVIEW,
            SupportTicket::STATUS_RESOLVED,
            SupportTicket::STATUS_REJECTED,
        ], true) ? [
            'support_ticket_id'  => $ticketId,
            'changed_by_user_id' => $userId,
            'status'             => SupportTicket::STATUS_UNDER_REVIEW,
            'resolution_type'    => null,
            'notes'              => 'El equipo de soporte tomó el caso y comenzó la revisión.',
            'created_at'         => $at->copy()->addDay(),
        ] : null;
    }

    private function closureLog(int $ticketId, int $userId, Carbon $at, string $status, ?string $resolutionType): ?array
    {
        $resolutionNotes = [
            'full_refund'    => 'Se aplicó reembolso total al cliente.',
            'partial_refund' => 'Se aplicó reembolso parcial al cliente.',
            'no_action'      => 'Ticket resuelto sin acción adicional sobre la orden.',
        ];

        return in_array($status, [SupportTicket::STATUS_RESOLVED, SupportTicket::STATUS_REJECTED], true) ? [
            'support_ticket_id'  => $ticketId,
            'changed_by_user_id' => $userId,
            'status'             => $status,
            'resolution_type'    => $resolutionType,
            'notes'              => $status === SupportTicket::STATUS_RESOLVED
                ? $resolutionNotes[$resolutionType]
                : 'El reporte fue rechazado tras revisar las evidencias.',
            'created_at'         => $at->copy()->addDays(2),
        ] : null;
    }
}
