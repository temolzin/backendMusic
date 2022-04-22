<?php

namespace Database\Seeders;

use App\Models\Artist;
use App\Models\Manager;
use Illuminate\Database\Seeder;

class ArtistSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        //Artista No.1
        Artist::create([
            'user_id' => 2,
            'name' => 'Grupo Firme',
            'slug' => 'grupo-firme',
            'members' => 8,
            'history' => "Lorem Ipsum is simply dummy text of the printing and typesetting industry. Lorem Ipsum has been the industry's standard dummy text ever since the 1500s, when an unknown printer took a galley of type and scrambled it to make a type specimen book.",
            'zone' => 'Ciudad de México. Méx.',
            'price_hour' => 10000,
            'extra_kilometre' => '1000',
            'image' => 'https://www.cryptoarena.com/assets/img/GrupoFirme_Press_1130x665-12c1f5e16e.jpg',
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
            'history' => "Lorem Ipsum is simply dummy text of the printing and typesetting industry. Lorem Ipsum has been the industry's standard dummy text ever since the 1500s, when an unknown printer took a galley of type and scrambled it to make a type specimen book.",
            'zone' => 'Ciudad de México. Méx.',
            'price_hour' => 15000,
            'extra_kilometre' => '1500',
            'image' => 'https://www.soygrupero.com.mx/wp-content/uploads/2019/07/las-mejores-canciones-de-joan-sebastian.jpg',
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
            'history' => "Lorem Ipsum is simply dummy text of the printing and typesetting industry. Lorem Ipsum has been the industry's standard dummy text ever since the 1500s, when an unknown printer took a galley of type and scrambled it to make a type specimen book.",
            'zone' => 'Ciudad de México. Méx.',
            'price_hour' => 12000,
            'extra_kilometre' => '1200',
            'image' => 'https://portal.andina.pe/EDPfotografia3/Thumbnail/2021/06/16/000782404W.jpg',
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
            'history' => "Lorem Ipsum is simply dummy text of the printing and typesetting industry. Lorem Ipsum has been the industry's standard dummy text ever since the 1500s, when an unknown printer took a galley of type and scrambled it to make a type specimen book.",
            'zone' => 'Ciudad de México. Méx.',
            'price_hour' => 8000,
            'extra_kilometre' => '200',
            'image' => 'https://lasvegasnespanol.com/wp-content/uploads/2017/07/voz-de-mando-en-las-vegas.jpg',
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
            'history' => "Lorem Ipsum is simply dummy text of the printing and typesetting industry. Lorem Ipsum has been the industry's standard dummy text ever since the 1500s, when an unknown printer took a galley of type and scrambled it to make a type specimen book.",
            'zone' => 'Ciudad de México. Méx.',
            'price_hour' => 6000,
            'extra_kilometre' => '200',
            'image' => 'https://hora724.com/wp-content/uploads/2020/08/WhatsApp-Image-2020-08-20-at-6.13.44-PM-e1598286461660.jpeg',
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
            'history' => "Lorem Ipsum is simply dummy text of the printing and typesetting industry. Lorem Ipsum has been the industry's standard dummy text ever since the 1500s, when an unknown printer took a galley of type and scrambled it to make a type specimen book.",
            'zone' => 'Ciudad de México. Méx.',
            'price_hour' => 9000,
            'extra_kilometre' => '250',
            'image' => 'https://pbs.twimg.com/profile_images/1448767265256656903/fYUhMdtx_400x400.jpg',
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
            'history' => "Lorem Ipsum is simply dummy text of the printing and typesetting industry. Lorem Ipsum has been the industry's standard dummy text ever since the 1500s, when an unknown printer took a galley of type and scrambled it to make a type specimen book.",
            'zone' => 'Ciudad de México. Méx.',
            'price_hour' => 10000,
            'extra_kilometre' => '1000',
            'image' => 'https://www.elsoldehermosillo.com.mx/gossip/celebridades/hmh8mq-luis-r.-conriquez/ALTERNATES/LANDSCAPE_960/Luis%20R.%20Conr%C3%ADquez',
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
            'history' => "Lorem Ipsum is simply dummy text of the printing and typesetting industry. Lorem Ipsum has been the industry's standard dummy text ever since the 1500s, when an unknown printer took a galley of type and scrambled it to make a type specimen book.",
            'zone' => 'Ciudad de México. Méx.',
            'price_hour' => 13000,
            'extra_kilometre' => '1300',
            'image' => 'https://i.scdn.co/image/ab6761610000e5ebaae48f703ad4e525539316f9',
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
            'history' => "Lorem Ipsum is simply dummy text of the printing and typesetting industry. Lorem Ipsum has been the industry's standard dummy text ever since the 1500s, when an unknown printer took a galley of type and scrambled it to make a type specimen book.",
            'zone' => 'Ciudad de México. Méx.',
            'price_hour' => 15000,
            'extra_kilometre' => '1500',
            'image' => 'https://www.sanborns.com.mx/imagenes-sanborns-ii/1200/602547853516.jpg',
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
            'name' => 'Junion H',
            'slug' => 'junion-h',
            'members' => 1,
            'history' => "Lorem Ipsum is simply dummy text of the printing and typesetting industry. Lorem Ipsum has been the industry's standard dummy text ever since the 1500s, when an unknown printer took a galley of type and scrambled it to make a type specimen book.",
            'zone' => 'Ciudad de México. Méx.',
            'price_hour' => 25000,
            'extra_kilometre' => '2500',
            'image' => 'https://i1.sndcdn.com/artworks-000659146966-1n2hvc-t500x500.jpg',
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
            'history' => "Lorem Ipsum is simply dummy text of the printing and typesetting industry. Lorem Ipsum has been the industry's standard dummy text ever since the 1500s, when an unknown printer took a galley of type and scrambled it to make a type specimen book.",
            'zone' => 'Ciudad de México. Méx.',
            'price_hour' => 35000,
            'extra_kilometre' => '3500',
            'image' => 'https://www.elsoldemexico.com.mx/incoming/hnjle6-inspector.jpg/ALTERNATES/LANDSCAPE_1140/inspector.jpg',
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
            'history' => "Lorem Ipsum is simply dummy text of the printing and typesetting industry. Lorem Ipsum has been the industry's standard dummy text ever since the 1500s, when an unknown printer took a galley of type and scrambled it to make a type specimen book.",
            'zone' => 'Ciudad de México. Méx.',
            'price_hour' => 30000,
            'extra_kilometre' => '3000',
            'image' => 'https://faroenlascmx.com/wp-content/uploads/2021/06/image_processing20190403-20399-11gyela.jpeg',
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
            'history' => "Lorem Ipsum is simply dummy text of the printing and typesetting industry. Lorem Ipsum has been the industry's standard dummy text ever since the 1500s, when an unknown printer took a galley of type and scrambled it to make a type specimen book.",
            'zone' => 'Ciudad de México. Méx.',
            'price_hour' => 45000,
            'extra_kilometre' => '4500',
            'image' => 'https://www.elsoldetijuana.com.mx/incoming/qba933-charles-ans/alternates/FREE_400/Charles%20Ans',
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
            'history' => "Lorem Ipsum is simply dummy text of the printing and typesetting industry. Lorem Ipsum has been the industry's standard dummy text ever since the 1500s, when an unknown printer took a galley of type and scrambled it to make a type specimen book.",
            'zone' => 'Ciudad de México. Méx.',
            'price_hour' => 25000,
            'extra_kilometre' => '2500',
            'image' => 'https://e.snmc.io/i/600/s/e28ee9b8bda442e1437cfd7959368da0/8199238/grupo-fernandez-la-fuga-del-dorian-Cover-Art.jpg',
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
            'history' => "Lorem Ipsum is simply dummy text of the printing and typesetting industry. Lorem Ipsum has been the industry's standard dummy text ever since the 1500s, when an unknown printer took a galley of type and scrambled it to make a type specimen book.",
            'zone' => 'Ciudad de México. Méx.',
            'price_hour' => 35000,
            'extra_kilometre' => '3500',
            'image' => 'https://i.ytimg.com/vi/4lE2SiM0jnM/maxresdefault.jpg',
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
