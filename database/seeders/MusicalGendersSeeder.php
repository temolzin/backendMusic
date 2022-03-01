<?php

namespace Database\Seeders;

use App\Models\MusicalGender;
use Illuminate\Database\Seeder;

class MusicalGendersSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        MusicalGender::create([
            'name' => 'Mariachi',
            'slug' => 'mariachi',
            'description' => 'Los mariachis surgieron en el siglo XVI, en Cocula, Jalisco fue donde se vio el primer grupo de mariachis que tocaban el violín y guitarra adoptando su propio sonido y estilo.',
            'color'=> 'primary'
        ]);
        MusicalGender::create([
            'name' => 'Corridos',
            'slug' => 'corridos',
            'description' => 'Los corridos se caracterizan por narrar una historia o hecho sobre lo que acontece a México, este género surgió durante la Revolución Mexicana.',
            'color'=> 'orange'
        ]);
        MusicalGender::create([
            'name' => 'Ranchera',
            'slug' => 'ranchera',
            'description' => 'Género considerado entre los predilectos de México, se debe a que es el conjunto de la cultura del folclor mexicano.',
            'color'=> 'secondary'
        ]);
        MusicalGender::create([
            'name' => 'Banda Sinaloense',
            'slug' => 'banda-sinaloense',
            'description' => 'Conocida también cómo “Tambora Sinaloense” es un sonido que surgió en el año de 1920 en el estado de Sinaloa.',
            'color'=> 'red'
        ]);
        MusicalGender::create([
            'name' => 'Huapango Huasteco',
            'slug' => 'huapango-huasteco',
            'description' => 'El estilo norteño es un género de la cultura popular mexicana y que hizo la introducción del acordeón.',
            'color'=> 'green'
        ]);
        MusicalGender::create([
            'name' => 'Huapango Norteño',
            'slug' => 'huapango-norteño',
            'description' => 'El estilo norteño es un género de la cultura popular mexicana y que hizo la introducción del acordeón.',
            'color'=> 'yellow'
        ]);
        MusicalGender::create([
            'name' => 'Chilena',
            'slug' => 'chilena',
            'description' => 'Es un género musical que se hizo muy popular en una zona llamada Costa Chica, localizada entre los estados de Oaxaca y Guerrero, al sur de México.',
            'color'=> 'orange'
        ]);

    }
}
