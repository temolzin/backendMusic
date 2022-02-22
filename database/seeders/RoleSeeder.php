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
        Role::create(["name" => "Artista", "slug" => "artista","description" => "Solo tiene permiso de artista"]);
        Role::create(["name" => "Cliente", "slug" => "cliente","description" => "Tiene permisos totales"]);
        //Role::create(["name" => "Sonidero", "slug" => "sonidero","description" => "Solo tiene permiso de sonidero"]);

        //Permisos
        //Ruta del dashboard
        //Crea el permiso y se lo asigna al rol 1,2,3
        Permission::create(['name' => 'Ver el Dashboard', 'slug' => 'view-dashboard', 'description' => 'Ver el Dashboard'])->roles()->sync([1, 2, 3]);
        Permission::create(['name' => 'Editar su perfil', 'slug' => 'edit-profile', 'description' => 'Editar su perfil'])->roles()->sync([1, 2, 3]);

        //Ruta del user
        //Crea el permiso y se lo asigna al rol 1 'admin'
        Permission::create(['name' => 'Ver usuarios', 'slug' => 'view-users', 'description' => 'Ver todos los usuarios'])->roles()->sync([1]);
        Permission::create(['name' => 'Crear usuarios', 'slug' => 'create-users', 'description' => 'Crear un nuevo  usuario'])->roles()->sync([1]);
        Permission::create(['name' => 'Editar usuarios', 'slug' => 'edit-users', 'description' => 'Editar su perfil de un usuario'])->roles()->sync([1]);
        Permission::create(['name' => 'Eliminar usuarios', 'slug' => 'delete-users', 'description' => 'Eliminar el perfil de un usuario'])->roles()->sync([1]);

        //Ruta de role
        //Crea el permiso y se lo asigna al rol 1 'admin'
        Permission::create(['name' => 'Ver roles', 'slug' => 'view-roles', 'description' => 'Ver todos los roles'])->roles()->sync([1]);
        Permission::create(['name' => 'Crear roles', 'slug' => 'create-roles', 'description' => 'Crear un nuevo rol'])->roles()->sync([1]);
        Permission::create(['name' => 'Editar roles', 'slug' => 'edit-roles', 'description' => 'Editar los permisos de un rol'])->roles()->sync([1]);
        Permission::create(['name' => 'Eliminar roles', 'slug' => 'delete-roles', 'description' => 'Eliminar rol'])->roles()->sync([1]);

         //Ruta de Artista
         //Crea el permiso y se lo asigna al rol 2 'Artista '
         Permission::create(['name' => 'Ver sus tarjetas', 'slug' => 'view-card', 'description' => 'Ver todas sus tarjetas'])->roles()->sync([3]);
         Permission::create(['name' => 'Crear sus tarjetas', 'slug' => 'create-card', 'description' => 'Crear nueva tarjeta'])->roles()->sync([3]);
         Permission::create(['name' => 'Editar sus tarjetas', 'slug' => 'edit-card', 'description' => 'Editar sus tarjetas'])->roles()->sync([3]);
         Permission::create(['name' => 'Eliminar sus tarjetas', 'slug' => 'delete-card', 'description' => 'Eliminar sus tarjetas'])->roles()->sync([3]);

         //Ruta de Card
         //Crea el permiso y se lo asigna al rol 3 'cliente'
         Permission::create(['name' => 'Ver su perfil de artista', 'slug' => 'view-profile-artist', 'description' => 'Ver su perfil de artista'])->roles()->sync([2]);
         Permission::create(['name' => 'Crear perfil de artista', 'slug' => 'create-profile-artist', 'description' => 'Crear perfil de artista'])->roles()->sync([2]);
         Permission::create(['name' => 'Editar su perfil de artista', 'slug' => 'edit-profile-artist', 'description' => 'Editar su perfil de artista'])->roles()->sync([2]);
         Permission::create(['name' => 'Eliminar su perfil de artista', 'slug' => 'delete-profile-artist', 'description' => 'Eliminar su perfil de artista'])->roles()->sync([2]);

        // $developerRole = Role::where('slug', 'administrador')->firstOrFail();
        // $developerPermissions = Permission::whereIn('slug', ['view-dashboard'])->get()->pluck('id')->toArray();
        // $role1->permissions()->sync([1, 2]);
        // $role2->permissions()->sync($developerPermissions);
        // $role3->permissions()->sync($developerPermissions);
        // $role4->permissions()->sync($developerPermissions);
    }
}
