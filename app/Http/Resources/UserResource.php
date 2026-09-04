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
        $emailHash = md5(strtolower(trim($this->email)));
        $defaultGravatar = "https://secure.gravatar.com/avatar/{$emailHash}?s=800&d=retro";

        return [
            'id'          => $this->id,
            'name'        => $this->name,
            'email'       => $this->email,
            'created_at'  => $this->created_at->format('d-m-Y'),
            'role'        => $roles,
            "permissions" => $userPermissions,
            "image"       => $this->getFirstMediaUrl('profile_images') ?: $defaultGravatar,
            "dark_mode"   => $this->dark_mode,
            'account_status' => $this->account_status
        ];
    }
}
