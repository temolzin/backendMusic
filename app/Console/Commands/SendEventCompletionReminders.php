<?php

namespace App\Console\Commands;

use App\Models\ArtistSale;
use App\Models\EventReminder;
use App\Mail\EventCompletionReminderNotification;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Carbon\Carbon;

class SendEventCompletionReminders extends Command
{
    protected $signature = 'events:send-completion-reminders';
    protected $description = 'Envía al artista un recordatorio para marcar como completado el evento terminado';

    private const COMPLETION_LOOKBACK_HOURS = 24;

    public function handle()
    {
        $now = Carbon::now();
        $cutoff = $now->copy()->subHours(self::COMPLETION_LOOKBACK_HOURS);

        $sales = ArtistSale::where('event_status', ArtistSale::EVENT_STATUS_PENDING)
            ->where('approval_status', ArtistSale::APPROVAL_STATUS_ACCEPTED)
            ->where('status', ArtistSale::PAYMENT_STATUS_COMPLETED)
            ->whereNotNull('event_date')
            ->whereNotNull('event_hour')
            ->whereDoesntHave('reminders', function ($q) {
                $q->where('lapse', EventReminder::LAPSE_COMPLETION);
            })
            ->get()
            ->filter(function ($sale) use ($now, $cutoff) {
                $eventDateStr = $sale->event_date instanceof Carbon ? $sale->event_date->format('Y-m-d') : $sale->event_date;
                $eventHourStr = $sale->event_hour instanceof Carbon ? $sale->event_hour->format('H:i:s') : $sale->event_hour;
                $eventEnd = Carbon::parse($eventDateStr . ' ' . $eventHourStr);
                $hours = $sale->event_hours ?? 0;
                $eventEnd->addHours($hours);

                return $eventEnd->lte($now) && $eventEnd->gte($cutoff);
            });

        $count = 0;

        foreach ($sales as $sale) {
            try {
                $artist = $sale->artist;
                if (!$artist) {
                    Log::warning('Recordatorio de completado: artista no encontrado', ['sale_id' => $sale->id]);
                    continue;
                }

                $artistUser = $artist->user;
                if (!$artistUser || !$artistUser->email) {
                    Log::warning('Recordatorio de completado: email de artista no encontrado', [
                        'sale_id' => $sale->id,
                        'artist_id' => $artist->id,
                    ]);
                    continue;
                }

                Mail::to($artistUser->email)->send(new EventCompletionReminderNotification($sale));

                $sale->reminders()->create([
                    'lapse' => EventReminder::LAPSE_COMPLETION,
                    'sent_at' => now(),
                ]);

                Log::info('Recordatorio de completado enviado al artista', [
                    'sale_id' => $sale->id,
                    'artist' => $artistUser->email,
                ]);

                $count++;
            } catch (\Exception $e) {
                Log::error('Error enviando recordatorio de completado', [
                    'sale_id' => $sale->id,
                    'error' => $e->getMessage(),
                ]);
            }
        }

        $this->info("Recordatorios de completado enviados: {$count}");
    }
}