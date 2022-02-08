<?php

namespace App\Http\Controllers;

use App\Http\Requests\Auth\UserRegisterRequest;
use App\Http\Resources\UserResource;
use App\Models\Role;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class UsersController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth:api', ['except' => ['create']]);
    }

    /**
     * Display the specified resource.
     *
     * @param  UserRegisterRequest  $request
     * @return \Illuminate\Http\Response
     */
    public function create(UserRegisterRequest $request)
    {
        try {
            $name = $request->input("name");
            $email = $request->input("email");
            $password = $request->input("password");

            if (!empty($name) && !empty($email) && !empty($password)) {

                //Busca en la BD el slug developer y lo guarda en la variable
                $developerRole = Role::where('slug', 'administrador')->first();
                //$developerRole = Role::developer()->first();

                DB::beginTransaction();
                $user = new User();
                $user->name = $name;
                $user->email = $email;
                $user->password = bcrypt($password);
                $user->save();
                //En la instancia de user busca la relación e inserta el id de usuario y del rol
                $user->roles()->attach($developerRole->id);
                DB::commit();
            }
            return response()->json([
                'success' => true,
                'message' => 'Usuario registrado',
            ], 200);
        } catch (\Exception $e) {
            DB::rollback();
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 401);
        }
    }

    /**
     * Display the specified resource.
     *
     * @param  null  
     * @return \Illuminate\Http\Response
     */
    public function me()
    {
        try {
            $user = new UserResource(Auth::user());
            return response()->json([
                'success' => true,
                'user' => $user,
            ], 200);
        } catch (\Exception $e) {
            DB::rollback();
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 401);
        }
    }
}
