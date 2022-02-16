<?php

namespace App\Http\Requests\User;

use Illuminate\Foundation\Http\FormRequest;

class UserApiCreateRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {

        return [
            'name' => 'required',
            'email' => 'required|email|unique:users',
            'password' => 'required',
            'role_id' => 'required'
        ];
    }
    public function attributes()
    {
        return [
            'name' => 'nombre del usuario',
            'email' => 'email del usuario',
            'password' => 'contraseña del usuario',
            'role_id' => 'rol del usuario'
        ];
    }
}
