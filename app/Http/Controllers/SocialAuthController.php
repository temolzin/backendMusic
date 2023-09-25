<?php

namespace App\Http\Controllers;

use App\Models\Role;
use App\Models\SocialAccount;

use Illuminate\Support\Facades\Auth;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Laravel\Socialite\Facades\Socialite;

class SocialAuthController extends Controller
{
    public function redirectToProvider()
    {
        $url = Socialite::driver('google')->stateless()->redirect()->getTargetUrl();
        return  response()->json([
            'success' => true,
            'url' => $url,
        ], 200);
    }

    public function handlesProviderCallback()
    {
        $user = Socialite::driver('google')->stateless()->user();

        if (!$user->token) {

            return  response()->json([
                'success' => false,
                'message' => 'Failed to login',
            ], 401);
        }

        $appUser = User::whereEmail($user->email)->first();

        if (!$appUser) {
            $developerRole = Role::where('slug', 'cliente')->first();
            $appUser = User::create([
                'name' => $user->name,
                'email' => $user->email,
                'password' => Hash::make($user->email),
                'image_profile' => $user->avatar,
            ]);

            $appUser->roles()->attach($developerRole->id);

            $socialAccount = SocialAccount::create([
                'provider' => 'google',
                'provider_user_id' => $user->id,
                'user_id' => $appUser->id
            ]);

            $credentials = ([
                'email' => $appUser->email,
                'password' => $appUser->email,
            ]);

            if ($token = Auth::attempt($credentials)) {
                return  response()->json([
                    'success' => true,
                    'acction' => 'Usuario creado con éxito',
                    'access_token' => $token,
                    'token_type' => 'bearer',
                    'expires_in' => auth()->factory()->getTTL() * 60
                ], 200);
            }
        } else {

            $socialAccount = $appUser->socialAccounts()->where('provider', 'google')->first();
            if (!$socialAccount) {
                $socialAccount = SocialAccount::create([
                    'provider' => 'google',
                    'provider_user_id' => $user->id,
                    'user_id' => $appUser->id
                ]);
            }
        }
        $credentials = ([
            'email' => $appUser->email,
            'password' => $appUser->email,
        ]);

        if ($token = Auth::attempt($credentials)) {
            return  response()->json([
                'success' => true,
                'acction' => 'Usuario encontrado en los registros',
                'access_token' => $token,
                'token_type' => 'bearer',
                'expires_in' => auth()->factory()->getTTL() * 60
            ], 200);
        }

        return  response()->json([
            'success' => false,
            'message' => 'Unexpected error',
        ], 401);
    }

    public function redirectToFacebookProvider()
    {
        $url = Socialite::driver('facebook')->stateless()->redirect()->getTargetUrl();
        return  response()->json([
            'success' => true,
            'url' => $url,
        ], 200);
    }

    public function handleFacebookProviderCallback()
    {
        $user = Socialite::driver('facebook')->stateless()->user();

        if (!$user->token) {

            return  response()->json([
                'success' => false,
                'message' => 'Failed to login',
            ], 401);
        }

        $appUser = User::whereEmail($user->email)->first();

        if (!$appUser) {


            $developerRole = Role::where('slug', 'cliente')->first();

            $appUser = User::create([
                'name' => $user->name,
                'email' => $user->email,
                'password' => Hash::make($user->email),
                'image_profile' => $user->avatar,
            ]);

            $appUser->roles()->attach($developerRole->id);
        }

        $socialAccount = $appUser->socialAccounts()->where('provider', 'facebook')->first();
        if (!$socialAccount) {
            $socialAccount = SocialAccount::create([
                'provider' => 'facebook',
                'provider_user_id' => $user->id,
                'user_id' => $appUser->id
            ]);
        }

        $credentials = ([
            'email' => $appUser->email,
            'password' => $appUser->email,
        ]);

        if ($token = Auth::attempt($credentials)) {
            return  response()->json([
                'success' => true,
                'acction' => 'Usuario encontrado en los registros',
                'access_token' => $token,
                'token_type' => 'bearer',
                'expires_in' => auth()->factory()->getTTL() * 60
            ], 200);
        }

        return  response()->json([
            'success' => false,
            'message' => 'Unexpected error',
        ], 401);
    }
}
