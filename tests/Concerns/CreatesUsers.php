<?php

namespace Tests\Concerns;

use App\Models\Role;
use App\Models\User;

trait CreatesUsers
{
    {
        $role = Role::where('slug', $slug)->first();

        if (! $role) {
            $role = new Role();
            $role->name = ucfirst($slug);
            $role->slug = $slug;
            $role->description = $slug;
            $role->save();
        }

        $user = User::factory()->create();
        $user->roles()->attach($role->id);

        return $user;
    }
}
