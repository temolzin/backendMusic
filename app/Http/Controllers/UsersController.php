<?php

namespace App\Http\Controllers;

use App\Http\Requests\Auth\UserRegisterRequest;
use App\Http\Resources\UserResource;
use App\Models\Role;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

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

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  Request  $request
     * @return \Illuminate\Http\Response
     */
    public function updateDetails(Request $request)
    {
        try {
            $name = $request->input("name");
            $email = $request->input("email");

            if (!empty($name) && !empty($email)) {

                $valiEmail = User::where('email', $email)->Where('id', '!=', Auth::user()->id)->first();
                if (!empty($valiEmail['email'])) {
                    return response()->json(['message' => 'El correo electrónico ya esta en uso'], 401);
                }

                DB::beginTransaction();
                $user = User::find(Auth::user()->id);
                $user->name = $name;
                $user->email = $email;
                $user->save();
                DB::commit();

                return response()->json([
                    'success' => true,
                    'message' => 'Usuario actualizado',
                ], 200);
            } else
                return response()->json([
                    'success' => false,
                    'message' => 'Error por campos vacíos',
                ], 401);
        } catch (\Exception $e) {
            DB::rollback();
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 401);
        }
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  Request  $request
     * @return \Illuminate\Http\Response
     */
    public function updatePassword(Request $request)
    {
        try {
            $newPassword = $request->input("newPassword");
            $confirmPassword = $request->input("confirmPassword");
            $currentPassword = $request->input("currentPassword");

            if (!empty($newPassword) && !empty($confirmPassword) && !empty($currentPassword)) {
                if ($newPassword != $confirmPassword) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Las contraseñas no coinciden',
                    ], 401);
                } else {

                    if (Hash::check($currentPassword, Auth::user()->password)) {
                        DB::beginTransaction();
                        $user = User::find(Auth::user()->id);
                        $user->password = Hash::make($newPassword);
                        $user->save();
                        DB::commit();

                        return response()->json([
                            'success' => true,
                            'message' => 'Contraseña actualizada',
                        ], 200);
                    } else {
                        return response()->json([
                            'success' => false,
                            'message' => 'La contraseña actual no coincide',
                        ], 401);
                    }
                }
            } else
                return response()->json([
                    'success' => false,
                    'message' => 'Error por campos vacíos',
                ], 401);
        } catch (\Exception $e) {
            DB::rollback();
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 401);
        }
    }
}
