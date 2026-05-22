<?php

namespace Database\Seeders;

use App\Models\Artist;
use App\Models\Manager;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ArtistSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::statement('TRUNCATE TABLE artist_musical_gender, managers, artists RESTART IDENTITY CASCADE;');

        //Artista No.1
        Artist::create([
            'user_id' => 2,
            'name' => 'Grupo Firme',
            'slug' => 'grupo-firme',
            'members' => 8,
            'history' => 'Agrupación mexicana reconocida por su energía en el escenario y por revolucionar el regional mexicano con un estilo moderno, festivo y cercano a su público.',
            'zone' => 'Ciudad de México. Méx.',
            'price_hour' => 10000,
            'extra_kilometre' => '1000',
            'image' => 'https://i.pinimg.com/1200x/cc/df/36/ccdf3618be3546b80b281f1c5a4804a9.jpg',
        ])->musicalGenders()->sync([
            rand(1, 3),
            rand(4, 5),
            rand(6, 7)
        ]);
        Manager::create([
            'artist_id' => 1,
            'name' => 'Juan Alberto Guzmán Gómez',
            'phone' => '5542770864',
            'email' => 'juan@gmail.com',
            'image' => 'https://cdn.pixabay.com/photo/2020/04/15/14/45/microphone-5046876_960_720.jpg',
        ]);

        //Artista No.2
        Artist::create([
            'user_id' => 3,
            'name' => 'Joan Sebastian',
            'slug' => 'joan-sebastian',
            'members' => 1,
            'history' => 'Ícono de la música mexicana y compositor legendario, conocido como “El Poeta del Pueblo” por sus canciones llenas de romanticismo y sentimiento.',
            'zone' => 'Ciudad de México. Méx.',
            'price_hour' => 15000,
            'extra_kilometre' => '1500',
            'image' => 'https://i.pinimg.com/736x/0a/d7/00/0ad700ef77db65b7724f029d8327ab70.jpg',
        ])->musicalGenders()->sync([
            rand(1, 3),
            rand(4, 5),
            rand(6, 7)
        ]);
        Manager::create([
            'artist_id' => 2,
            'name' => 'Yatziry Guadalupe Gómez Gómez',
            'phone' => '5542770864',
            'email' => 'yatziry@gmail.com',
            'image' => 'https://cdn.pixabay.com/photo/2016/09/10/11/11/musician-1658887_960_720.jpg',
        ]);

        //Artista No.3
        Artist::create([
            'user_id' => 4,
            'name' => 'Hombres G',
            'slug' => 'hombres-g',
            'members' => 5,
            'history' => 'Banda española de pop rock que marcó generaciones con temas juveniles, divertidos y románticos que siguen siendo clásicos en habla hispana.',
            'zone' => 'Ciudad de México. Méx.',
            'price_hour' => 12000,
            'extra_kilometre' => '1200',
            'image' => 'https://i.pinimg.com/736x/23/ea/2f/23ea2f44d0c87703f57c2b3bea5d78ac.jpg',
        ])->musicalGenders()->sync([
            rand(1, 3),
            rand(4, 5)
        ]);
        Manager::create([
            'artist_id' => 3,
            'name' => 'Karla Morales Gonzales',
            'phone' => '5542770864',
            'email' => 'karla@gmail.com',
            'image' => 'https://cdn.pixabay.com/photo/2015/04/15/09/47/men-723557_960_720.jpg',
        ]);

        //Artista No.4
        Artist::create([
            'user_id' => 5,
            'name' => 'Voz de Mando',
            'slug' => 'voz-de-mando',
            'members' => 4,
            'history' => 'Grupo de música norteña reconocido por sus corridos y canciones que reflejan la cultura y las historias del regional mexicano.',
            'zone' => 'Ciudad de México. Méx.',
            'price_hour' => 8000,
            'extra_kilometre' => '200',
            'image' => 'https://i.pinimg.com/736x/e9/50/13/e95013b2e8b7d7f53838c2f781da9dcb.jpg',
        ])->musicalGenders()->sync([
            rand(1, 3),
            rand(4, 5),
            rand(6, 7)
        ]);
        Manager::create([
            'artist_id' => 4,
            'name' => 'Harol Antonio Hidalgo Gutierrez',
            'phone' => '5542770864',
            'email' => 'harlo@gmail.com',
            'image' => 'https://cdn.pixabay.com/photo/2015/04/15/09/47/men-723557_960_720.jpg',
        ]);

        //Artista No.5
        Artist::create([
            'user_id' => 6,
            'name' => 'Pancho Uresti',
            'slug' => 'pancho-uresti',
            'members' => 1,
            'history' => 'Cantante de música norteña y regional mexicana destacado por su potente voz y trayectoria dentro de agrupaciones reconocidas del género.',
            'zone' => 'Ciudad de México. Méx.',
            'price_hour' => 6000,
            'extra_kilometre' => '200',
            'image' => 'https://i.pinimg.com/736x/96/6c/b4/966cb48cb7837789152ddb0e2e3f2ade.jpg',
        ])->musicalGenders()->sync([
            rand(1, 3),
            rand(4, 5)
        ]);
        Manager::create([
            'artist_id' => 5,
            'name' => 'Danna Herrera Peña',
            'phone' => '5542770864',
            'email' => 'danna@gmail.com',
            'image' => 'https://cdn.pixabay.com/photo/2015/09/17/14/24/woman-944262_960_720.jpg',
        ]);

        //Artista No.6
        Artist::create([
            'user_id' => 7,
            'name' => 'Adriel Favela',
            'slug' => 'adriel-favela',
            'members' => 1,
            'history' => 'Artista que mezcla corridos y regional mexicano con sonidos contemporáneos, destacando por su estilo auténtico y letras emotivas.',
            'zone' => 'Ciudad de México. Méx.',
            'price_hour' => 9000,
            'extra_kilometre' => '250',
            'image' => 'https://i.pinimg.com/736x/03/e1/67/03e167ed063f427feba2b426dd4e174b.jpg',
        ])->musicalGenders()->sync([
            rand(1, 3),
            rand(4, 5)
        ]);
        Manager::create([
            'artist_id' => 6,
            'name' => 'Angelica Morales Hernández',
            'phone' => '5542770864',
            'email' => 'angelica@gmail.com',
            'image' => 'https://cdn.pixabay.com/photo/2019/10/04/05/42/workshop-4524838_960_720.jpg',
        ]);

        //Artista No.7
        Artist::create([
            'user_id' => 8,
            'name' => 'Luis R Conriquez',
            'slug' => 'luis-r-conriquez',
            'members' => 4,
            'history' => 'Uno de los exponentes más populares de los corridos actuales, reconocido por su estilo moderno y gran impacto en la música regional mexicana.',
            'zone' => 'Ciudad de México. Méx.',
            'price_hour' => 10000,
            'extra_kilometre' => '1000',
            'image' => 'https://i.pinimg.com/736x/d1/c6/48/d1c648c1e63daca81628d58dff214249.jpg',
        ])->musicalGenders()->sync([
            rand(1, 3),
            rand(4, 5)
        ]);
        Manager::create([
            'artist_id' => 7,
            'name' => 'Issac Villalobos Molina',
            'phone' => '5542770864',
            'email' => 'issac@gmail.com',
            'image' => 'https://cdn.pixabay.com/photo/2019/10/04/05/42/workshop-4524838_960_720.jpg',
        ]);

        //Artista No.8
        Artist::create([
            'user_id' => 9,
            'name' => 'Los 2 de la S',
            'slug' => 'los-2-de-la-s',
            'members' => 4,
            'history' => 'Dúo sinaloense que ha ganado popularidad gracias a sus corridos y canciones con un estilo fresco y representativo del regional mexicano.',
            'zone' => 'Ciudad de México. Méx.',
            'price_hour' => 13000,
            'extra_kilometre' => '1300',
            'image' => 'https://i.pinimg.com/1200x/91/06/60/910660eee38a08159a5b35aa79c0c163.jpg',
        ])->musicalGenders()->sync([
            rand(1, 3),
            rand(4, 5)
        ]);
        Manager::create([
            'artist_id' => 8,
            'name' => 'Fatima Leon García',
            'phone' => '5542770864',
            'email' => 'fatima@gmail.com',
            'image' => 'https://cdn.pixabay.com/photo/2019/12/18/04/11/dj-4702977_960_720.jpg',
        ]);

        //Artista No.9
        Artist::create([
            'user_id' => 10,
            'name' => 'Remmy Valenzuela',
            'slug' => 'remmy-valenzuela',
            'members' => 1,
            'history' => 'Cantautor y acordeonista mexicano admirado por su talento musical y por fusionar el norteño con toques románticos y modernos.',
            'zone' => 'Ciudad de México. Méx.',
            'price_hour' => 15000,
            'extra_kilometre' => '1500',
            'image' => 'https://i.pinimg.com/1200x/50/1b/14/501b144d5376547e6686e36ad4bde81b.jpg',
        ])->musicalGenders()->sync([
            rand(1, 3),
            rand(4, 5)
        ]);
        Manager::create([
            'artist_id' => 9,
            'name' => 'Monserath López Alarcón',
            'phone' => '5542770864',
            'email' => 'monse@gmail.com',
            'image' => 'https://cdn.pixabay.com/photo/2016/03/27/21/44/musician-1284394_960_720.jpg',
        ]);

        //Artista No.10
        Artist::create([
            'user_id' => 11,
            'name' => 'Junior H',
            'slug' => 'junior-h',
            'members' => 1,
            'history' => 'Referente de los corridos tumbados y la música urbana regional, conocido por sus letras melancólicas y sonido innovador.',
            'zone' => 'Ciudad de México. Méx.',
            'price_hour' => 25000,
            'extra_kilometre' => '2500',
            'image' => 'https://i.pinimg.com/1200x/71/1b/e2/711be2b654a23146aa9ae0ad31e7cb31.jpg',
        ])->musicalGenders()->sync([
            rand(1, 3),
            rand(4, 5)
        ]);
        Manager::create([
            'artist_id' => 10,
            'name' => 'Luis Gómez Gómez',
            'phone' => '5542770864',
            'email' => 'luis@gmail.com',
            'image' => 'https://cdn.pixabay.com/photo/2014/05/21/15/18/musician-349790_960_720.jpg',
        ]);

        //Artista No.11
        Artist::create([
            'user_id' => 12,
            'name' => 'Inspector',
            'slug' => 'inspector',
            'members' => 6,
            'history' => 'Banda mexicana de ska reconocida por su energía, ritmos contagiosos y canciones que combinan fiesta, amor y crítica social.',
            'zone' => 'Ciudad de México. Méx.',
            'price_hour' => 35000,
            'extra_kilometre' => '3500',
            'image' => 'https://i.pinimg.com/1200x/23/b9/82/23b98262e658f93a72822a667918e2fa.jpg',
        ])->musicalGenders()->sync([
            rand(1, 3),
            rand(4, 5)
        ]);
        Manager::create([
            'artist_id' => 11,
            'name' => 'Iván Hernández Sanchez',
            'phone' => '5542770864',
            'email' => 'ivan@gmail.com',
            'image' => 'https://cdn.pixabay.com/photo/2015/05/07/11/02/guitar-756326_960_720.jpg',
        ]);

        //Artista No.12
        Artist::create([
            'user_id' => 13,
            'name' => 'Los Caligaris',
            'slug' => 'los-caligaris',
            'members' => 6,
            'history' => 'Grupo argentino famoso por sus shows llenos de alegría, mezclando rock, ska y música circense en espectáculos únicos y divertidos.',
            'zone' => 'Ciudad de México. Méx.',
            'price_hour' => 30000,
            'extra_kilometre' => '3000',
            'image' => 'https://i.pinimg.com/1200x/1d/76/a6/1d76a6acedc7acee80295d2f8ddc36a4.jpg',
        ])->musicalGenders()->sync([
            rand(1, 3),
            rand(4, 5),
            rand(6, 7)
        ]);
        Manager::create([
            'artist_id' => 12,
            'name' => 'Guadalupe Enciso Martínez',
            'phone' => '5542770864',
            'email' => 'lupe@gmail.com',
            'image' => 'https://cdn.pixabay.com/photo/2016/01/14/06/09/woman-1139397_960_720.jpg',
        ]);

        //Artista No.13
        Artist::create([
            'user_id' => 14,
            'name' => 'Charles Ans',
            'slug' => 'charles-ans',
            'members' => 1,
            'history' => 'Rapero mexicano destacado por sus letras introspectivas, estilo relajado y una propuesta auténtica dentro del hip hop nacional.',
            'zone' => 'Ciudad de México. Méx.',
            'price_hour' => 45000,
            'extra_kilometre' => '4500',
            'image' => 'https://i.pinimg.com/736x/04/1d/3d/041d3da152a762abb95262f430ff3ab6.jpg',
        ])->musicalGenders()->sync([
            rand(1, 3),
            rand(4, 5),
            rand(6, 7)
        ]);
        Manager::create([
            'artist_id' => 13,
            'name' => 'Miguel Francisco Gómez',
            'phone' => '5542770864',
            'email' => 'miguel@gmail.com',
            'image' => 'https://cdn.pixabay.com/photo/2018/01/16/16/48/adult-3086307_960_720.jpg',
        ]);

        //Artista No.14
        Artist::create([
            'user_id' => 15,
            'name' => 'Grupo Fernandez',
            'slug' => 'grupo-fernandez',
            'members' => 5,
            'history' => 'Agrupación de regional mexicano reconocida por interpretar música con raíces tradicionales y un estilo cercano al público.',
            'zone' => 'Ciudad de México. Méx.',
            'price_hour' => 25000,
            'extra_kilometre' => '2500',
            'image' => 'https://i.pinimg.com/1200x/a4/65/92/a46592f025e0e84faec139809716fda6.jpg',
        ])->musicalGenders()->sync([
            rand(1, 3),
            rand(4, 5),
            rand(6, 7)
        ]);
        Manager::create([
            'artist_id' => 14,
            'name' => 'Guillermo Cruz Castillo',
            'phone' => '5542770864',
            'email' => 'memo@gmail.com',
            'image' => 'https://cdn.pixabay.com/photo/2020/04/15/14/45/microphone-5046876_960_720.jpg',
        ]);

        //Artista No.15
        Artist::create([
            'user_id' => 16,
            'name' => 'Los Plebes del Rancho',
            'slug' => 'los-plebes-del-rancho',
            'members' => 3,
            'history' => 'Grupo popular dentro del movimiento sierreño, conocido por mantener vivo el legado musical romántico y sentimental del regional mexicano.',
            'zone' => 'Ciudad de México. Méx.',
            'price_hour' => 35000,
            'extra_kilometre' => '3500',
            'image' => 'https://i.pinimg.com/736x/38/15/e7/3815e71510f30c0cf528c997d6b6af76.jpg',
        ])->musicalGenders()->sync([
            rand(1, 3),
            rand(4, 5),
            rand(6, 7)
        ]);
        Manager::create([
            'artist_id' => 15,
            'name' => 'Cecilia Loera Cid',
            'phone' => '5542770864',
            'email' => 'cecilia@gmail.com',
            'image' => 'https://cdn.pixabay.com/photo/2018/08/27/10/11/radio-cassette-3634616_960_720.png',
        ]);
    }
}
