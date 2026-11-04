<?php

namespace Database\Seeders;

use App\Models\Artist;
use App\Models\Manager;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class ArtistSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        Schema::disableForeignKeyConstraints();
        DB::table('artist_musical_gender')->truncate();
        DB::table('managers')->truncate();
        DB::table('artists')->truncate();
        Schema::enableForeignKeyConstraints();

        //Artista No.1
        $artist = Artist::create([
            'user_id' => 2,
            'name' => 'Grupo Firme',
            'slug' => 'grupo-firme',
            'members' => 8,
            'history' => 'Agrupación mexicana reconocida por su energía en el escenario y por revolucionar el regional mexicano con un estilo moderno, festivo y cercano a su público.',
            'zone' => 'Ciudad de México. Méx.',
            'price_hour' => 10000,
            'extra_kilometre' => '85',
            'coverage_radius' => 50,
        ]);
        $artist->musicalGenders()->sync([
            rand(1, 3),
            rand(4, 5),
            rand(6, 7)
        ]);

        $manager = Manager::create([
            'artist_id' => 1,
            'name' => 'Juan Alberto Guzmán Gómez',
            'phone' => '5542770864',
            'email' => 'juan@gmail.com',
        ]);

        //Artista No.2
        $artist = Artist::create([
            'user_id' => 3,
            'name' => 'Joan Sebastian',
            'slug' => 'joan-sebastian',
            'members' => 1,
            'history' => 'Ícono de la música mexicana y compositor legendario, conocido como “El Poeta del Pueblo” por sus canciones llenas de romanticismo y sentimiento.',
            'zone' => 'Ciudad de México. Méx.',
            'price_hour' => 15000,
            'extra_kilometre' => '45',
            'coverage_radius' => 40,
        ]);
        $artist->musicalGenders()->sync([
            rand(1, 3),
            rand(4, 5),
            rand(6, 7)
        ]);

        $manager = Manager::create([
            'artist_id' => 2,
            'name' => 'Yatziry Guadalupe Gómez Gómez',
            'phone' => '5542770864',
            'email' => 'yatziry@gmail.com',
        ]);

        //Artista No.3
        $artist = Artist::create([
            'user_id' => 4,
            'name' => 'Hombres G',
            'slug' => 'hombres-g',
            'members' => 5,
            'history' => 'Banda española de pop rock que marcó generaciones con temas juveniles, divertidos y románticos que siguen siendo clásicos en habla hispana.',
            'zone' => 'Ciudad de México. Méx.',
            'price_hour' => 12000,
            'extra_kilometre' => '60',
            'coverage_radius' => 45,
        ]);
        $artist->musicalGenders()->sync([
            rand(1, 3),
            rand(4, 5)
        ]);

        $manager = Manager::create([
            'artist_id' => 3,
            'name' => 'Karla Morales Gonzales',
            'phone' => '5542770864',
            'email' => 'karla@gmail.com',
        ]);

        //Artista No.4
        $artist = Artist::create([
            'user_id' => 5,
            'name' => 'Voz de Mando',
            'slug' => 'voz-de-mando',
            'members' => 4,
            'history' => 'Grupo de música norteña reconocido por sus corridos y canciones que reflejan la cultura y las historias del regional mexicano.',
            'zone' => 'Ciudad de México. Méx.',
            'price_hour' => 8000,
            'extra_kilometre' => '50',
            'coverage_radius' => 35,
        ]);
        $artist->musicalGenders()->sync([
            rand(1, 3),
            rand(4, 5),
            rand(6, 7)
        ]);

        $manager = Manager::create([
            'artist_id' => 4,
            'name' => 'Harol Antonio Hidalgo Gutierrez',
            'phone' => '5542770864',
            'email' => 'harlo@gmail.com',
        ]);

        //Artista No.5
        $artist = Artist::create([
            'user_id' => 6,
            'name' => 'Pancho Uresti',
            'slug' => 'pancho-uresti',
            'members' => 1,
            'history' => 'Cantante de música norteña y regional mexicana destacado por su potente voz y trayectoria dentro de agrupaciones reconocidas del género.',
            'zone' => 'Ciudad de México. Méx.',
            'price_hour' => 6000,
            'extra_kilometre' => '30',
            'coverage_radius' => 30,
        ]);
        $artist->musicalGenders()->sync([
            rand(1, 3),
            rand(4, 5)
        ]);

        $manager = Manager::create([
            'artist_id' => 5,
            'name' => 'Danna Herrera Peña',
            'phone' => '5542770864',
            'email' => 'danna@gmail.com',
        ]);

        //Artista No.6
        $artist = Artist::create([
            'user_id' => 7,
            'name' => 'Adriel Favela',
            'slug' => 'adriel-favela',
            'members' => 1,
            'history' => 'Artista que mezcla corridos y regional mexicano con sonidos contemporáneos, destacando por su estilo auténtico y letras emotivas.',
            'zone' => 'Ciudad de México. Méx.',
            'price_hour' => 9000,
            'extra_kilometre' => '35',
            'coverage_radius' => 25,
        ]);
        $artist->musicalGenders()->sync([
            rand(1, 3),
            rand(4, 5)
        ]);

        $manager = Manager::create([
            'artist_id' => 6,
            'name' => 'Angelica Morales Hernández',
            'phone' => '5542770864',
            'email' => 'angelica@gmail.com',
        ]);

        //Artista No.7
        $artist = Artist::create([
            'user_id' => 8,
            'name' => 'Luis R Conriquez',
            'slug' => 'luis-r-conriquez',
            'members' => 4,
            'history' => 'Uno de los exponentes más populares de los corridos actuales, reconocido por su estilo moderno y gran impacto en la música regional mexicana.',
            'zone' => 'Ciudad de México. Méx.',
            'price_hour' => 10000,
            'extra_kilometre' => '55',
            'coverage_radius' => 55,
        ]);
        $artist->musicalGenders()->sync([
            rand(1, 3),
            rand(4, 5)
        ]);

        $manager = Manager::create([
            'artist_id' => 7,
            'name' => 'Issac Villalobos Molina',
            'phone' => '5542770864',
            'email' => 'issac@gmail.com',
        ]);

        //Artista No.8
        $artist = Artist::create([
            'user_id' => 9,
            'name' => 'Los 2 de la S',
            'slug' => 'los-2-de-la-s',
            'members' => 4,
            'history' => 'Dúo sinaloense que ha ganado popularidad gracias a sus corridos y canciones con un estilo fresco y representativo del regional mexicano.',
            'zone' => 'Ciudad de México. Méx.',
            'price_hour' => 13000,
            'extra_kilometre' => '50',
            'coverage_radius' => 40,
        ]);
        $artist->musicalGenders()->sync([
            rand(1, 3),
            rand(4, 5)
        ]);

        $manager = Manager::create([
            'artist_id' => 8,
            'name' => 'Fatima Leon García',
            'phone' => '5542770864',
            'email' => 'fatima@gmail.com',
        ]);

        //Artista No.9
        $artist = Artist::create([
            'user_id' => 10,
            'name' => 'Remmy Valenzuela',
            'slug' => 'remmy-valenzuela',
            'members' => 1,
            'history' => 'Cantautor y accordionista mexicano admirado por su talento musical y por fusionar el norteño con toques románticos y modernos.',
            'zone' => 'Ciudad de México. Méx.',
            'price_hour' => 15000,
            'extra_kilometre' => '40',
            'coverage_radius' => 60,
        ]);
        $artist->musicalGenders()->sync([
            rand(1, 3),
            rand(4, 5)
        ]);

        $manager = Manager::create([
            'artist_id' => 9,
            'name' => 'Monserath López Alarcón',
            'phone' => '5542770864',
            'email' => 'monse@gmail.com',
        ]);

        //Artista No.10
        $artist = Artist::create([
            'user_id' => 11,
            'name' => 'Junior H',
            'slug' => 'junior-h',
            'members' => 1,
            'history' => 'Referente de los corridos tumbados y la música urbana regional, conocido por sus letras melancólicas y sonido innovador.',
            'zone' => 'Ciudad de México. Méx.',
            'price_hour' => 25000,
            'extra_kilometre' => '65',
            'coverage_radius' => 70,
        ]);
        $artist->musicalGenders()->sync([
            rand(1, 3),
            rand(4, 5)
        ]);

        $manager = Manager::create([
            'artist_id' => 10,
            'name' => 'Luis Gómez Gómez',
            'phone' => '5542770864',
            'email' => 'luis@gmail.com',
        ]);

        //Artista No.11
        $artist = Artist::create([
            'user_id' => 12,
            'name' => 'Inspector',
            'slug' => 'inspector',
            'members' => 6,
            'history' => 'Banda mexicana de ska reconocida por su energía, ritmos contagiosos y canciones que combinan fiesta, amor y crítica social.',
            'zone' => 'Ciudad de México. Méx.',
            'price_hour' => 35000,
            'extra_kilometre' => '80',
            'coverage_radius' => 80,
        ]);
        $artist->musicalGenders()->sync([
            rand(1, 3),
            rand(4, 5)
        ]);

        $manager = Manager::create([
            'artist_id' => 11,
            'name' => 'Iván Hernández Sanchez',
            'phone' => '5542770864',
            'email' => 'ivan@gmail.com',
        ]);

        //Artista No.12
        $artist = Artist::create([
            'user_id' => 13,
            'name' => 'Los Caligaris',
            'slug' => 'los-caligaris',
            'members' => 6,
            'history' => 'Grupo argentino famoso por sus shows llenos de alegría, mezclando rock, ska y música circense en espectáculos únicos y divertidos.',
            'zone' => 'Ciudad de México. Méx.',
            'price_hour' => 30000,
            'extra_kilometre' => '90',
            'coverage_radius' => 90,
        ]);
        $artist->musicalGenders()->sync([
            rand(1, 3),
            rand(4, 5),
            rand(6, 7)
        ]);

        $manager = Manager::create([
            'artist_id' => 12,
            'name' => 'Guadalupe Enciso Martínez',
            'phone' => '5542770864',
            'email' => 'lupe@gmail.com',
        ]);

        //Artista No.13
        $artist = Artist::create([
            'user_id' => 14,
            'name' => 'Charles Ans',
            'slug' => 'charles-ans',
            'members' => 1,
            'history' => 'Rapero mexicano destacado por sus letras introspectivas, estilo relajado y una propuesta auténtica dentro del hip hop nacional.',
            'zone' => 'Ciudad de México. Méx.',
            'price_hour' => 45000,
            'extra_kilometre' => '35',
            'coverage_radius' => 35,
        ]);
        $artist->musicalGenders()->sync([
            rand(1, 3),
            rand(4, 5),
            rand(6, 7)
        ]);

        $manager = Manager::create([
            'artist_id' => 13,
            'name' => 'Miguel Francisco Gómez',
            'phone' => '5542770864',
            'email' => 'miguel@gmail.com',
        ]);

        //Artista No.14
        $artist = Artist::create([
            'user_id' => 15,
            'name' => 'Grupo Fernandez',
            'slug' => 'grupo-fernandez',
            'members' => 5,
            'history' => 'Agrupación de regional mexicano reconocida por interpretar música con raíces tradicionales y un estilo cercano al público.',
            'zone' => 'Ciudad de México. Méx.',
            'price_hour' => 25000,
            'extra_kilometre' => '50',
            'coverage_radius' => 50,
        ]);
        $artist->musicalGenders()->sync([
            rand(1, 3),
            rand(4, 5),
            rand(6, 7)
        ]);

        $manager = Manager::create([
            'artist_id' => 14,
            'name' => 'Guillermo Cruz Castillo',
            'phone' => '5542770864',
            'email' => 'memo@gmail.com',
        ]);

        //Artista No.15
        $artist = Artist::create([
            'user_id' => 16,
            'name' => 'Los Plebes del Rancho',
            'slug' => 'los-plebes-del-rancho',
            'members' => 3,
            'history' => 'Grupo popular dentro del movimiento sierreño, conocido por mantener vivo el legado musical romántico y sentimental del regional mexicano.',
            'zone' => 'Ciudad de México. Méx.',
            'price_hour' => 35000,
            'extra_kilometre' => '60',
            'coverage_radius' => 65,
        ]);
        $artist->musicalGenders()->sync([
            rand(1, 3),
            rand(4, 5),
            rand(6, 7)
        ]);

        $manager = Manager::create([
            'artist_id' => 15,
            'name' => 'Cecilia Loera Cid',
            'phone' => '5542770864',
            'email' => 'cecilia@gmail.com',
        ]);
    }
}
