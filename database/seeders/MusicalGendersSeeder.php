<?php

namespace Database\Seeders;

use App\Models\MusicalGender;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class MusicalGendersSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::statement('TRUNCATE TABLE musical_genders RESTART IDENTITY CASCADE;');

        MusicalGender::create([
            'name' => 'Mariachi',
            'slug' => 'mariachi',
            'description' => 'Los mariachis surgieron en el siglo XVI, en Cocula, Jalisco fue donde se vio el primer grupo de mariachis que tocaban el violín y guitarra adoptando su propio sonido y estilo.',
            'color'=> 'primary',
            'image' => 'https://images.unsplash.com/photo-1511671782779-c97d3d27a1d4?w=300&h=300&fit=crop'
        ]);
        MusicalGender::create([
            'name' => 'Corridos',
            'slug' => 'corridos',
            'description' => 'Los corridos se caracterizan por narrar una historia o hecho sobre lo que acontece a México, este género surgió durante la Revolución Mexicana.',
            'color'=> 'orange',
            'image' => 'https://images.unsplash.com/photo-1493225457124-a3eb161ffa5f?w=300&h=300&fit=crop'
        ]);
        MusicalGender::create([
            'name' => 'Ranchera',
            'slug' => 'ranchera',
            'description' => 'Género considerado entre los predilectos de México, se debe a que es el conjunto de la cultura del folclor mexicano.',
            'color'=> 'secondary',
            'image' => 'https://images.unsplash.com/photo-1514320291840-2e0a9bf2a9ae?w=300&h=300&fit=crop'
        ]);
        MusicalGender::create([
            'name' => 'Banda Sinaloense',
            'slug' => 'banda-sinaloense',
            'description' => 'Conocida también cómo “Tambora Sinaloense” es un sonido que surgió en el año de 1920 en el estado de Sinaloa.',
            'color'=> 'red',
            'image' => 'https://images.unsplash.com/photo-1508700115892-45ecd05ae2ad?w=300&h=300&fit=crop'
        ]);
        MusicalGender::create([
            'name' => 'Huapango Huasteco',
            'slug' => 'huapango-huasteco',
            'description' => 'El estilo norteño es un género de la cultura popular mexicana y que hizo la introducción del acordeón.',
            'color'=> 'green',
            'image' => 'https://images.unsplash.com/photo-1461784121038-f088ca1e7714?w=300&h=300&fit=crop'
        ]);
        MusicalGender::create([
            'name' => 'Huapango Norteño',
            'slug' => 'huapango-norteño',
            'description' => 'El estilo norteño es un género de la cultura popular mexicana y que hizo la introducción del acordeón.',
            'color'=> 'yellow',
            'image' => 'https://images.unsplash.com/photo-1558981403-c5f9899a28bc?w=300&h=300&fit=crop'
        ]);
        MusicalGender::create([
            'name' => 'Chilena',
            'slug' => 'chilena',
            'description' => 'Es un género musical que se hizo muy popular en una zona llamada Costa Chica, localizada entre los estados de Oaxaca y Guerrero, al sur de México.',
            'color'=> 'orange',
            'image' => 'https://images.unsplash.com/photo-1478737270239-2f02b77fc618?w=300&h=300&fit=crop'
        ]);

    }
}
