<?php

namespace Database\Seeders;

use App\Models\Artist;
use App\Models\Quotations;
use Illuminate\Database\Seeder;
use Faker\Factory as Faker;

class ArtistBookingsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $faker = Faker::create('es_ES');
        $statuses = ['pendiente', 'confirmada'];        
        $artists = Artist::all();

        if ($artists->isEmpty()) {
            return;
        }

        foreach ($artists as $artist) {
            $bookingsCount = rand(5, 15);

            for ($i = 0; $i < $bookingsCount; $i++) {
                $daysOffset = rand(-60, 90);
                $eventDate = now()->addDays($daysOffset)->format('Y-m-d');

                $status = rand(1, 2) === 1 ? 'pendiente' : 'confirmada';

                $eventHours = rand(2, 8);
                $pricePerHour = $artist->price_hour ?? 50;
                $price = $pricePerHour * $eventHours;

                Quotations::create([
                    'artist_id' => $artist->id,
                    'event_name' => $this->generateEventName(),
                    'event_date' => $eventDate,
                    'event_hours' => $eventHours,
                    'city' => $faker->city(),
                    'address' => $faker->address(),
                    'phone' => $this->generatePhoneNumber(),
                    'email' => $faker->unique()->safeEmail(),
                    'full_name' => $faker->name(),
                    'price' => $price,
                    'status' => $status,
                ]);
            }
        }
    }

    private function generateEventName()
    {
        $eventTypes = [
            'Boda',
            'Cumpleaños',
            'Evento Corporativo',
            'Fiesta Privada',
            'Festival',
            'Concierto',
            'Gala Benéfica',
            'Inauguración',
            'Aniversario',
            'Retiro Empresarial',
            'Cena de Gala',
            'Fiesta de Quinceañera',
            'Matrimonio',
            'Celebración',
            'Evento Social'
        ];

        return $eventTypes[array_rand($eventTypes)] . ' - ' . rand(2024, 2026);
    }

    private function generatePhoneNumber()
    {
        $prefixes = ['6', '7', '9'];
        $prefix = $prefixes[array_rand($prefixes)];
        return $prefix . rand(10000000, 99999999);
    }
}
