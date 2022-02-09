<?php

namespace App\Http\Requests\User;

use Illuminate\Foundation\Http\FormRequest;

class UserApiUpdateRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return false;
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
                //'email' =>  ['required', 'email', Rule::unique('users')->ignore($user->id)],
                'password' => 'required'
        ];
    }
}
