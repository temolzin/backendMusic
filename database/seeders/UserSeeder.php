<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::statement('TRUNCATE TABLE users RESTART IDENTITY CASCADE;');

        $user = User::create([
            'name' => 'Miguel Ánguel Parra Moreno',
            'email' => 'angel@gmail.com',
            'password' => bcrypt('password'),
        ]);
        $user->roles()->sync(1);
        $user->addMediaFromUrl('https://secure.gravatar.com/avatar/' . md5(strtolower(trim('angel@gmail.com'))) . '?s=800&d=retro')->toMediaCollection('profile_images');

        $user = User::create([
            'name' => 'Juan Alberto Guzmán Gómez',
            'email' => 'juan@gmail.com',
            'password' => bcrypt('password'),
        ]);
        $user->roles()->sync(2);
        $user->addMediaFromUrl('https://secure.gravatar.com/avatar/' . md5(strtolower(trim('juanalbertoguzman87@gmail.com'))) . '?s=800&d=retro')->toMediaCollection('profile_images');

        $user = User::create([
            'name' => 'Yatziry Guadalupe Gómez Gómez',
            'email' => 'yatziry@gmail.com',
            'password' => bcrypt('password'),
        ]);
        $user->roles()->sync(2);
        $user->addMediaFromUrl('https://secure.gravatar.com/avatar/' . md5(strtolower(trim('yatziry@gmail.com'))) . '?s=800&d=retro')->toMediaCollection('profile_images');

        $user = User::create([
            'name' => 'Karla Morales Gonzales',
            'email' => 'karla@gmail.com',
            'password' => bcrypt('password'),
        ]);
        $user->roles()->sync(2);
        $user->addMediaFromUrl('https://secure.gravatar.com/avatar/' . md5(strtolower(trim('karla@gmail.com'))) . '?s=800&d=retro')->toMediaCollection('profile_images');

        $user = User::create([
            'name' => 'Harol Antonio Hidalgo Gutierrez',
            'email' => 'antonio@gmail.com',
            'password' => bcrypt('password'),
        ]);
        $user->roles()->sync(2);
        $user->addMediaFromUrl('https://secure.gravatar.com/avatar/' . md5(strtolower(trim('antonio@gmail.com'))) . '?s=800&d=retro')->toMediaCollection('profile_images');

        $user = User::create([
            'name' => 'Danna Herrera Peña',
            'email' => 'danna@gmail.com',
            'password' => bcrypt('password'),
        ]);
        $user->roles()->sync(2);
        $user->addMediaFromUrl('https://secure.gravatar.com/avatar/' . md5(strtolower(trim('danna@gmail.com'))) . '?s=800&d=retro')->toMediaCollection('profile_images');

        $user = User::create([
            'name' => 'Angelica Morales Hernández',
            'email' => 'angelica@gmail.com',
            'password' => bcrypt('password'),
        ]);
        $user->roles()->sync(2);
        $user->addMediaFromUrl('https://secure.gravatar.com/avatar/' . md5(strtolower(trim('angelica@gmail.com'))) . '?s=800&d=retro')->toMediaCollection('profile_images');

        $user = User::create([
            'name' => 'Issac Villalobos Molina',
            'email' => 'issac@gmail.com',
            'password' => bcrypt('password'),
        ]);
        $user->roles()->sync(2);
        $user->addMediaFromUrl('https://secure.gravatar.com/avatar/' . md5(strtolower(trim('issac@gmail.com'))) . '?s=800&d=retro')->toMediaCollection('profile_images');

        $user = User::create([
            'name' => 'Fatima Leon García',
            'email' => 'fatima@gmail.com',
            'password' => bcrypt('password'),
        ]);
        $user->roles()->sync(2);
        $user->addMediaFromUrl('https://secure.gravatar.com/avatar/' . md5(strtolower(trim('fatima@gmail.com'))) . '?s=800&d=retro')->toMediaCollection('profile_images');

        $user = User::create([
            'name' => 'Monserath López Alarcón',
            'email' => 'monse@gmail.com',
            'password' => bcrypt('password'),
        ]);
        $user->roles()->sync(2);
        $user->addMediaFromUrl('https://secure.gravatar.com/avatar/' . md5(strtolower(trim('monse@gmail.com'))) . '?s=800&d=retro')->toMediaCollection('profile_images');

        $user = User::create([
            'name' => 'Luis Gómez Gómez',
            'email' => 'luis@gmail.com',
            'password' => bcrypt('password'),
        ]);
        $user->roles()->sync(2);
        $user->addMediaFromUrl('https://secure.gravatar.com/avatar/' . md5(strtolower(trim('luis@gmail.com'))) . '?s=800&d=retro')->toMediaCollection('profile_images');

        $user = User::create([
            'name' => 'Iván Hernández Sanchez',
            'email' => 'ivan@gmail.com',
            'password' => bcrypt('password'),
        ]);
        $user->roles()->sync(2);
        $user->addMediaFromUrl('https://secure.gravatar.com/avatar/' . md5(strtolower(trim('ivan@gmail.com'))) . '?s=800&d=retro')->toMediaCollection('profile_images');

        $user = User::create([
            'name' => 'Guadalupe Enciso Martínez',
            'email' => 'lupe@gmail.com',
            'password' => bcrypt('password'),
        ]);
        $user->roles()->sync(2);
        $user->addMediaFromUrl('https://secure.gravatar.com/avatar/' . md5(strtolower(trim('lupe@gmail.com'))) . '?s=800&d=retro')->toMediaCollection('profile_images');

        $user = User::create([
            'name' => 'Miguel Francisco Gómez',
            'email' => 'miguel@gmail.com',
            'password' => bcrypt('password'),
        ]);
        $user->roles()->sync(2);
        $user->addMediaFromUrl('https://secure.gravatar.com/avatar/' . md5(strtolower(trim('miguel@gmail.com'))) . '?s=800&d=retro')->toMediaCollection('profile_images');

        $user = User::create([
            'name' => 'Guillermo Cruz Castillo',
            'email' => 'memo@gmail.com',
            'password' => bcrypt('password'),
        ]);
        $user->roles()->sync(2);
        $user->addMediaFromUrl('https://secure.gravatar.com/avatar/' . md5(strtolower(trim('memo@gmail.com'))) . '?s=800&d=retro')->toMediaCollection('profile_images');

        $user = User::create([
            'name' => 'Cecilia Loera Cid',
            'email' => 'cecilia@gmail.com',
            'password' => bcrypt('password'),
        ]);
        $user->roles()->sync(2);
        $user->addMediaFromUrl('https://secure.gravatar.com/avatar/' . md5(strtolower(trim('cecilia@gmail.com'))) . '?s=800&d=retro')->toMediaCollection('profile_images');

        $user = User::create([
            'name' => 'Alexis Hernandez López',
            'email' => 'alexis@gmail.com',
            'password' => bcrypt('password'),
        ]);
        $user->roles()->sync(3);
        $user->addMediaFromUrl('https://secure.gravatar.com/avatar/' . md5(strtolower(trim('alexis@gmail.com'))) . '?s=800&d=retro')->toMediaCollection('profile_images');

        $user = User::create([
            'name' => 'Fernando Mendoza Cruz',
            'email' => 'fernando@gmail.com',
            'password' => bcrypt('password'),
        ]);
        $user->roles()->sync(3);
        $user->addMediaFromUrl('https://secure.gravatar.com/avatar/' . md5(strtolower(trim('fernando@gmail.com'))) . '?s=800&d=retro')->toMediaCollection('profile_images');

        $user = User::create([
            'name' => 'Carlos Ramirez Lopez',
            'email' => 'carlosramirez@gmail.com',
            'password' => bcrypt('password'),
        ]);
        $user->roles()->sync(3);
        $user->addMediaFromUrl('https://secure.gravatar.com/avatar/' . md5(strtolower(trim('carlosramirez@gmail.com'))) . '?s=800&d=retro')->toMediaCollection('profile_images');

        $user = User::create([
            'name' => 'Valeria Torres Silva',
            'email' => 'valeriatorres@gmail.com',
            'password' => bcrypt('password'),
        ]);
        $user->roles()->sync(3);
        $user->addMediaFromUrl('https://secure.gravatar.com/avatar/' . md5(strtolower(trim('valeriatorres@gmail.com'))) . '?s=800&d=retro')->toMediaCollection('profile_images');

        $user = User::create([
            'name' => 'Diego Hernandez Ruiz',
            'email' => 'diegohernandez@gmail.com',
            'password' => bcrypt('password'),
        ]);
        $user->roles()->sync(3);
        $user->addMediaFromUrl('https://secure.gravatar.com/avatar/' . md5(strtolower(trim('diegohernandez@gmail.com'))) . '?s=800&d=retro')->toMediaCollection('profile_images');
    }
}
