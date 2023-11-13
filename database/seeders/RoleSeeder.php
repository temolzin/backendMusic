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
        Role::create(["name" => "Administrador", "slug" => "administrador", "description" => "Tiene permisos totales"]);
        Role::create(["name" => "Artista", "slug" => "artista", "description" => "Solo tiene permiso de artista"]);
        Role::create(["name" => "Cliente", "slug" => "cliente", "description" => "Tiene permisos totales"]);

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

        //Ruta del muscal genders
        //Crea el permiso y se lo asigna al rol 1 'admin'
        Permission::create(['name' => 'Ver generos musicales', 'slug'  => 'view-musicals-genders', 'description' => 'Ver todos los generos musicales'])->roles()->sync([1, 2]);
        Permission::create(['name' => 'Crear genero musical', 'slug'   => 'create-musical-gender', 'description' => 'Crear un nuevo genero musical'])->roles()->sync([1]);
        Permission::create(['name' => 'Editar genero musical', 'slug'  => 'edit-musical-gender', 'description' => 'Editar un genero musical'])->roles()->sync([1]);
        Permission::create(['name' => 'Eliminar genero musical', 'slug' => 'delete-musical-gender', 'description' => 'Eliminar un genero musical'])->roles()->sync([1]);

        //Ruta de role
        //Crea el permiso y se lo asigna al rol 1 'admin'
        Permission::create(['name' => 'Ver roles', 'slug' => 'view-roles', 'description' => 'Ver todos los roles'])->roles()->sync([1]);
        Permission::create(['name' => 'Crear roles', 'slug' => 'create-roles', 'description' => 'Crear un nuevo rol'])->roles()->sync([1]);
        Permission::create(['name' => 'Editar roles', 'slug' => 'edit-roles', 'description' => 'Editar los permisos de un rol'])->roles()->sync([1]);
        Permission::create(['name' => 'Eliminar roles', 'slug' => 'delete-roles', 'description' => 'Eliminar rol'])->roles()->sync([1]);



        //Ruta de Artista
        //Crea el permiso y se lo asigna al rol 2 'Artista'
        Permission::create(['name' => 'Ver su perfil de artista', 'slug' => 'view-profile-artist', 'description' => 'Ver su perfil de artista'])->roles()->sync([2]);
        Permission::create(['name' => 'Crear perfil de artista', 'slug' => 'create-profile-artist', 'description' => 'Crear perfil de artista'])->roles()->sync([2]);
        Permission::create(['name' => 'Editar su perfil de artista', 'slug' => 'edit-profile-artist', 'description' => 'Editar su perfil de artista'])->roles()->sync([2]);
        //Permission::create(['name' => 'Eliminar su perfil de artista', 'slug' => 'delete-profile-artist', 'description' => 'Eliminar su perfil de artista'])->roles()->sync([2]);

        //Ruta de Cliente
        //Ruta de Card
        Permission::create(['name' => 'Ver sus tarjetas', 'slug' => 'view-card', 'description' => 'Ver todas sus tarjetas'])->roles()->sync([3]);
        Permission::create(['name' => 'Crear sus tarjetas', 'slug' => 'create-card', 'description' => 'Crear nueva tarjeta'])->roles()->sync([3]);
        Permission::create(['name' => 'Editar sus tarjetas', 'slug' => 'edit-card', 'description' => 'Editar sus tarjetas'])->roles()->sync([3]);
        Permission::create(['name' => 'Eliminar sus tarjetas', 'slug' => 'delete-card', 'description' => 'Eliminar sus tarjetas'])->roles()->sync([3]);

        //Ruta de generos musicales
        Permission::create(['name' => 'Ver todos los generos musicales', 'slug' => 'view-all-musicals-genders', 'description' => 'Ver todos los géneros musicales existentes'])->roles()->sync([3]);
        Permission::create(['name' => 'Ver grupos por generos', 'slug' => 'view-groups-by-genders', 'description' => 'Ver grupos dependiendo del género que pertenecen'])->roles()->sync([3]);

        //Ruta del carrito de compras
        Permission::create(['name' => 'Ver carrito de compras', 'slug' => 'view-shopping-cart', 'description' => 'Ver su carrito de compras'])->roles()->sync([3]);
        Permission::create(['name' => 'Crear carrito de compras', 'slug' => 'create-shopping-cart', 'description' => 'Crear su carrito de compras'])->roles()->sync([3]);
        Permission::create(['name' => 'Ver detalles de compras', 'slug' => 'view-my-order-details', 'description' => 'Ver historial de compras'])->roles()->sync([3]);
        Permission::create(['name' => 'Edita carrito de compras', 'slug' => 'edit-shopping-cart', 'description' => 'Editar su carrito de compras'])->roles()->sync([3]);
        Permission::create(['name' => 'Eliminar carrito de compras', 'slug' => 'delete-shopping-cart', 'description' => 'Eliminar su carrito de compras'])->roles()->sync([3]);
       
        //Ruta de artistas favoritos
        Permission::create(['name' => 'Ver artistas favoritos', 'slug' => 'view-favourite-artist', 'description' => 'Ver sus grupos favoritos'])->roles()->sync([3]);
        Permission::create(['name' => 'Agregar artista favorito', 'slug' => 'create-favourite-artist', 'description' => 'Agregar un nuevo grupo a favoritos'])->roles()->sync([3]);
        Permission::create(['name' => 'Eliminar artista favorito', 'slug' => 'delete-favourite-artist', 'description' => 'Eliminar grupo de favoritos'])->roles()->sync([3]);

        // $developerRole = Role::where('slug', 'administrador')->firstOrFail();
        // $developerPermissions = Permission::whereIn('slug', ['view-dashboard'])->get()->pluck('id')->toArray();
        // $role1->permissions()->sync([1, 2]);
        // $role2->permissions()->sync($developerPermissions);
        // $role3->permissions()->sync($developerPermissions);
        // $role4->permissions()->sync($developerPermissions);
    }
}
