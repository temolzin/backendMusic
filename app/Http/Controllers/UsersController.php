<?php

namespace App\Http\Controllers;

use App\Http\Requests\Auth\UserRegisterRequest;
use App\Http\Resources\UserResource;
use App\Models\Role;
use App\Models\User;
use App\Rules\ValidImageUpload;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;

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
            $hash =  md5(strtolower(trim($email)));
            $user = null;

            if (empty($name) || empty($email) || empty($password)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Error por campos vacíos',
                ], 422);
            }

            $developerRole = Role::where('slug', 'cliente')->first();

            DB::beginTransaction();
            $user = new User();
            $user->name = $name;
            $user->email = $email;
            $user->password = bcrypt($password);
            $user->save();
            $user->roles()->attach($developerRole->id);
            $absoluteImageUrl = 'https://secure.gravatar.com/avatar/' . $hash . '?s=800&d=retro';
            $user->addMediaFromUrl($absoluteImageUrl)->toMediaCollection('profile_images');
            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Usuario registrado',
                'image_profile' => $absoluteImageUrl,
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

            if (empty($name) || empty($email)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Error por campos vacíos',
                ], 401);
            }

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

            if (empty($newPassword) || empty($confirmPassword) || empty($currentPassword)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Error por campos vacíos',
                ], 401);
            }

            if ($newPassword != $confirmPassword) {
                return response()->json([
                    'success' => false,
                    'message' => 'Las contraseñas no coinciden',
                ], 401);
            }

            if (!Hash::check($currentPassword, Auth::user()->password)) {
                return response()->json([
                    'success' => false,
                    'message' => 'La contraseña actual no coincide',
                ], 401);
            }

            DB::beginTransaction();
            $user = User::find(Auth::user()->id);
            $user->password = Hash::make($newPassword);
            $user->save();
            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Contraseña actualizada',
            ], 200);
        } catch (\Exception $e) {
            DB::rollback();
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 401);
        }
    }

    public function updateImageProfile(Request $request)
    {
        try {
            $request->validate([
                'image_profile' => ['required', 'file', 'max:1024', new ValidImageUpload()],
            ]);

            /** @var User $user */
            $user = User::query()->findOrFail(Auth::id());

            $media = $user->addMediaFromRequest('image_profile')
                        ->toMediaCollection('profile_images');

            $absoluteImageUrl = $media->getUrl();

            return response()->json([
                'success' => true,
                'message' => 'Imagen actualizada',
                'image_profile' => $absoluteImageUrl,
                'image' => $absoluteImageUrl,
            ], 200);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Hay campos inválidos o faltantes en el formulario.',
                'errors'  => $e->errors(),
            ], 422);
        } catch (\Exception $e) {
            DB::rollback();
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 401);
        }
    }

    public function updateDarkMode(Request $request)
    {
        User::query()->whereKey(Auth::id())->update([
            'dark_mode' => $request->dark_mode,
        ]);

        $user = User::query()->findOrFail(Auth::id());
        return response()->json(['dark_mode' => $user->dark_mode], 200);
    }
}
