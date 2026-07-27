<?php

namespace App\Console\Commands;

use App\Models\ArtistSale;
use App\Mail\EventReminderNotification;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Carbon\Carbon;

class SendEventReminders extends Command
{
    protected $signature = 'events:send-reminders';
    protected $description = 'Envía recordatorio al artista y cliente 24h antes del evento';

    public function handle()
    {
        $tomorrow = Carbon::tomorrow()->toDateString();

        $events = ArtistSale::whereDate('event_date', $tomorrow)
            ->where('event_status', ArtistSale::EVENT_STATUS_PENDING)
            ->where('approval_status', ArtistSale::APPROVAL_STATUS_ACCEPTED)
            ->where('status', ArtistSale::PAYMENT_STATUS_COMPLETED)
            ->whereNull('reminder_sent_at')
            ->get();

        $count = 0;

        foreach ($events as $sale) {
            try {
                $artist = $sale->artist;
                if (!$artist) {
                    Log::warning('Recordatorio: artista no encontrado', ['sale_id' => $sale->id]);
                    continue;
                }

                $artistUser = $artist->user;
                if (!$artistUser || !$artistUser->email) {
                    Log::warning('Recordatorio: email de artista no encontrado', ['sale_id' => $sale->id, 'artist_id' => $artist->id]);
                    continue;
                }

                Mail::to($artistUser->email)->send(new EventReminderNotification($sale, 'artist'));
                Log::info('Recordatorio enviado al artista', [
                    'sale_id' => $sale->id,
                    'artist' => $artistUser->email,
                    'event_date' => $sale->event_date,
                ]);

                $customer = $sale->customer;
                if (!$customer || !$customer->email) {
                    Log::warning('Recordatorio: email de cliente no encontrado', ['sale_id' => $sale->id]);
                }
                if ($customer && $customer->email) {
                    Mail::to($customer->email)->send(new EventReminderNotification($sale, 'customer'));
                    Log::info('Recordatorio enviado al cliente', [
                        'sale_id' => $sale->id,
                        'customer' => $customer->email,
                        'event_date' => $sale->event_date,
                    ]);
                }
                $sale->reminder_sent_at = now();
                $sale->save();
                $count++;
            } catch (\Exception $e) {
                Log::error('Error enviando recordatorio', [
                    'sale_id' => $sale->id,
                    'error' => $e->getMessage(),
                ]);
            }
        }

        $this->info("Recordatorios enviados: {$count}");
    }
}
