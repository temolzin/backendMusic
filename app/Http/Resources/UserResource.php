<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class UserResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array|\Illuminate\Contracts\Support\Arrayable|\JsonSerializable
     */
    public function toArray($request)
    {
        $userRoles = $this->roles()->with('permissions')->get();
        $roles = $userRoles->pluck('slug');
        $rolesPermissions = $userRoles->pluck('permissions')->flatten(1)->pluck('slug');
        $userPermissions = $rolesPermissions->merge($this->permissions->pluck('slug'));

        return [
            //'id' => $this->id,
            'name'        => $this->name,
            'email'       => $this->email,
            'created_at'  => $this->created_at->format('d-m-Y'),
            'role'        => $roles,
            "permissions" => $userPermissions,
            "image"       => $this->image_profile,
        ];
    }
}
