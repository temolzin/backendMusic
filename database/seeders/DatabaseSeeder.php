<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Storage;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     *
     * @return void
     */
    public function run()
    {
        // \App\Models\User::factory(10)->create();

        // $this->call(PermissionSeeder::class);
        Storage::deleteDirectory('public/user_profile');
        Storage::makeDirectory('public/user_profile');

        Storage::deleteDirectory('public/artist');
        Storage::makeDirectory('public/artist');

        Storage::deleteDirectory('public/galery-artist');
        Storage::makeDirectory('public/galery-artist');

        Storage::deleteDirectory('public/manager');
        Storage::makeDirectory('public/manager');

        $this->call(RoleSeeder::class);
        $this->call(UserSeeder::class);
        $this->call(MusicalGendersSeeder::class);
    }
}
