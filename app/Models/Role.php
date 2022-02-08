<?php

namespace App\Models;

use App\Models\Traits\HasPermissions;
use Illuminate\Database\Eloquent\Model;

class Role extends Model
{
    use HasPermissions;

    public function hasPermissionTo(...$permissions)
    {
        // $role->hasPermissionTo('edit-user', 'edit-issue');
        return $this->permissions()->whereIn('slug', $permissions)->count();
    }

    public function permissions()
    {
        return $this->belongsToMany(Permission::class, 'roles_permissions');
    }
}
