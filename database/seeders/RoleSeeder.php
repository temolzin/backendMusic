<?php

namespace Database\Seeders;

use App\Models\Permission;
use App\Models\Role;
use Illuminate\Database\Seeder;

class RoleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        Role::create(["name" => "Administrador", "slug" => "administrador","description" => "Tiene permisos totales"]);
        Role::create(["name" => "Artist", "slug" => "artist","description" => "Solo tiene permiso de artista"]);
        Role::create(["name" => "Cliente", "slug" => "cliente","description" => "Tiene permisos totales"]);
        //Role::create(["name" => "Sonidero", "slug" => "sonidero","description" => "Solo tiene permiso de sonidero"]);

        //Permisos
        //Ruta del dashboard
        //Crea el permiso y se lo asigna al rol 1,2,3,4
        Permission::create(['name' => 'View Dashboard', 'slug' => 'view-dashboard', 'description' => 'Ver el Dashboard'])->roles()->sync([1, 2, 3]);
        Permission::create(['name' => 'Edit Profile', 'slug' => 'edit-profile', 'description' => 'Editar su perfil'])->roles()->sync([1, 2, 3]);

        //Ruta del user
        //Crea el permiso y se lo asigna al rol 1
        Permission::create(['name' => 'View users', 'slug' => 'view-users', 'description' => 'Ver todos los usuarios'])->roles()->sync([1]);
        Permission::create(['name' => 'Edit users', 'slug' => 'edit-users', 'description' => 'Editar su perfil de un usuario'])->roles()->sync([1]);

        



        // $developerRole = Role::where('slug', 'administrador')->firstOrFail();
        // $developerPermissions = Permission::whereIn('slug', ['view-dashboard'])->get()->pluck('id')->toArray();
        // $role1->permissions()->sync([1, 2]);
        // $role2->permissions()->sync($developerPermissions);
        // $role3->permissions()->sync($developerPermissions);
        // $role4->permissions()->sync($developerPermissions);
    }
}
