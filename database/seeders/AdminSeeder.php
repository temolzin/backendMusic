<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class AdminSeeder extends Seeder
{
    /**
     * Datos mínimos de producción: roles/permisos + 1 usuario administrador.
     * (Clientes y artistas se crean con normalidad en la app, no se seedearn).
     *
     * @return void
     */
    public function run()
    {
        $this->call(RoleSeeder::class);

        DB::statement('TRUNCATE TABLE users RESTART IDENTITY CASCADE;');

        $admin = User::create([
            'name' => 'Administrador Vibeer',
            'email' => 'admin@vibeer.com',
            'password' => bcrypt('password'),
        ]);
        $admin->roles()->sync(1);
        $admin->save();
    }
}