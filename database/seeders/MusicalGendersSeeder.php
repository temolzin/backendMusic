<?php

namespace Database\Seeders;

use App\Models\MusicalGender;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class MusicalGendersSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        Schema::disableForeignKeyConstraints();
        DB::table('musical_genders')->truncate();
        Schema::enableForeignKeyConstraints();

        $gender = MusicalGender::create([
            'name' => 'Mariachi',
            'slug' => 'mariachi',
            'description' => 'Los mariachis surgieron en el siglo XVI, en Cocula, Jalisco fue donde se vio el primer grupo de mariachis que tocaban el violín y guitarra adoptando su propio sonido y estilo.',
            'color'=> 'primary',
        ]);
        $gender = MusicalGender::create([
            'name' => 'Corridos',
            'slug' => 'corridos',
            'description' => 'Los corridos se caracterizan por narrar una historia o hecho sobre lo que acontece a México, este género surgió durante la Revolución Mexicana.',
            'color'=> 'orange',
        ]);
        $gender = MusicalGender::create([
            'name' => 'Ranchera',
            'slug' => 'ranchera',
            'description' => 'Género considerado entre los predilectos de México, se debe a que es el conjunto de la cultura del folclor mexicano.',
            'color'=> 'secondary',
        ]);
        $gender = MusicalGender::create([
            'name' => 'Banda Sinaloense',
            'slug' => 'banda-sinaloense',
            'description' => 'Conocida también cómo “Tambora Sinaloense” es un sonido que surgió en el año de 1920 en el estado de Sinaloa.',
            'color'=> 'red',
        ]);
        $gender = MusicalGender::create([
            'name' => 'Huapango Huasteco',
            'slug' => 'huapango-huasteco',
            'description' => 'El estilo norteño es un género de la cultura popular mexicana y que hizo la introducción del acordeón.',
            'color'=> 'green',
        ]);
        $gender = MusicalGender::create([
            'name' => 'Huapango Norteño',
            'slug' => 'huapango-norteño',
            'description' => 'El estilo norteño es un género de la cultura popular mexicana y que hizo la introducción del acordeón.',
            'color'=> 'yellow',
        ]);
        $gender = MusicalGender::create([
            'name' => 'Chilena',
            'slug' => 'chilena',
            'description' => 'Es un género musical que se hizo muy popular en una zona llamada Costa Chica, localizada entre los estados de Oaxaca y Guerrero, al sur de México.',
            'color'=> 'orange',
        ]);
    }
}
