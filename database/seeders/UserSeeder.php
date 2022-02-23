<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        User::create([
            'name' => 'Juan Alberto Guzmán Gómez',
            'email' => 'juanalbertoguzman87@gmail.com',
            'password' => bcrypt('password'),
            'image_profile' => 'https://secure.gravatar.com/avatar/' . md5(strtolower(trim('juanalbertoguzman87@gmail.com'))) . '?s=800&d=retro',
        ])->roles()->sync(2);

        User::create([
            'name' => 'Miguel Ánguel Parra Moreno',
            'email' => 'angel@gmail.com',
            'password' => bcrypt('password'),
            'image_profile' => 'https://secure.gravatar.com/avatar/' . md5(strtolower(trim('angel@gmail.com'))) . '?s=800&d=retro',
        ])->roles()->sync(1);

        User::create([
            'name' => 'Yatziry Guadalupe Gómez Gómez',
            'email' => 'yatziry@gmail.com',
            'password' => bcrypt('password'),
            'image_profile' => 'https://secure.gravatar.com/avatar/' . md5(strtolower(trim('yatziry@gmail.com'))) . '?s=800&d=retro',
        ])->roles()->sync(3);

        User::create([
            'name' => 'Karla Morales Gonzales',
            'email' => 'karla@gmail.com',
            'password' => bcrypt('password'),
            'image_profile' => 'https://secure.gravatar.com/avatar/' . md5(strtolower(trim('karla@gmail.com'))) . '?s=800&d=retro',
        ])->roles()->sync(2);

        User::create([
            'name' => 'Harol Antonio Hidalgo Gutierrez',
            'email' => 'antonio@gmail.com',
            'password' => bcrypt('password'),
            'image_profile' => 'https://secure.gravatar.com/avatar/' . md5(strtolower(trim('antonio@gmail.com'))) . '?s=800&d=retro',
        ])->roles()->sync(2);

        User::create([
            'name' => 'Danna Herrera Peña',
            'email' => 'danna@gmail.com',
            'password' => bcrypt('password'),
            'image_profile' => 'https://secure.gravatar.com/avatar/' . md5(strtolower(trim('danna@gmail.com'))) . '?s=800&d=retro',
        ])->roles()->sync(3);

        User::create([
            'name' => 'Alexis Hernandez López',
            'email' => 'alexis@gmail.com',
            'password' => bcrypt('password'),
            'image_profile' => 'https://secure.gravatar.com/avatar/' . md5(strtolower(trim('alexis@gmail.com'))) . '?s=800&d=retro',
        ])->roles()->sync(3);
    }
}