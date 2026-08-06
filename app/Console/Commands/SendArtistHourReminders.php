<?php

namespace App\Console\Commands;

use App\Models\ArtistSale;
use App\Mail\ArtistHourReminderNotification;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Carbon\Carbon;

class SendArtistHourReminders extends Command
{
    protected $signature = 'events:send-artist-hour-reminders';
    protected $description = 'Envía al artista un recordatorio 30 minutos antes de su hora de inicio en el evento';

    private const REMINDER_MINUTES_BEFORE = 30;

    public function handle()
    {
        $now = Carbon::now();
        $today = $now->toDateString();

        $sales = ArtistSale::whereDate('event_date', $today)
            ->whereNotNull('event_hour')
            ->where('event_status', ArtistSale::EVENT_STATUS_PENDING)
            ->where('approval_status', ArtistSale::APPROVAL_STATUS_ACCEPTED)
            ->where('status', ArtistSale::PAYMENT_STATUS_COMPLETED)
            ->whereNull('hour_reminder_sent_at')
            ->get();

        $count = 0;

        foreach ($sales as $sale) {
            try {
                $eventDateStr = $sale->event_date instanceof Carbon
                    ? $sale->event_date->toDateString()
                    : $sale->event_date;
                $eventHourStr = $sale->event_hour instanceof Carbon
                    ? $sale->event_hour->format('H:i:s')
                    : $sale->event_hour;

                $startsAt = Carbon::parse("{$eventDateStr} {$eventHourStr}");
                $minutesUntilStart = $now->diffInMinutes($startsAt, false);

                if ($minutesUntilStart < 0 || $minutesUntilStart > self::REMINDER_MINUTES_BEFORE) {
                    continue;
                }

                $artist = $sale->artist;
                if (!$artist) {
                    Log::warning('Recordatorio de hora: artista no encontrado', ['sale_id' => $sale->id]);
                    continue;
                }

                $artistUser = $artist->user;
                if (!$artistUser || !$artistUser->email) {
                    Log::warning('Recordatorio de hora: email de artista no encontrado', [
                        'sale_id' => $sale->id,
                        'artist_id' => $artist->id,
                    ]);
                    continue;
                }

                Mail::to($artistUser->email)->send(new ArtistHourReminderNotification($sale));

                $sale->hour_reminder_sent_at = now();
                $sale->save();

                Log::info('Recordatorio de hora enviado al artista', [
                    'sale_id' => $sale->id,
                    'artist' => $artistUser->email,
                    'event_date' => $eventDateStr,
                    'event_hour' => $eventHourStr,
                ]);

                $count++;
            } catch (\Exception $e) {
                Log::error('Error enviando recordatorio de hora', [
                    'sale_id' => $sale->id,
                    'error' => $e->getMessage(),
                ]);
            }
        }

        $this->info("Recordatorios de hora enviados: {$count}");
    }
}
