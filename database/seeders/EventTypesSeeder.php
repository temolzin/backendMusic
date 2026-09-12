<?php

namespace Database\Seeders;

use App\Models\EventType;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class EventTypesSeeder extends Seeder
{
    public function run()
    {
        DB::table('event_types')->delete();

        $types = [
            'Boda',
            'XV Años',
            'Cumpleaños',
            'Baby Shower',
            'Bautizo',
            'Graduación',
            'Corporativo',
            'Otros',
        ];

        foreach ($types as $type) {
            EventType::create([
                'name' => $type,
                'slug' => Str::slug($type),
            ]);
        }
    }
}
