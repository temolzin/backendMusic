<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Str;

class ForgotPasswordController extends Controller
{
    public function sendResetLinkEmail(Request $request)
    {
        $request->validate([
            'email' => 'required|email'
        ]);

        $status = Password::broker()->sendResetLink($request->only('email'));

        if ($status === Password::RESET_LINK_SENT) {
            return response()->json([
                'message' => 'Se envió un enlace de recuperación a tu correo electrónico.',
            ], 200);
        }

        return response()->json([
            'error' => 'No encontramos un usuario con ese correo.',
        ], 400);
    }

    public function resetPassword(Request $request)
    {
        $request->validate([
            'token' => 'required',
            'email' => 'required|email',
            'password' => 'required|min:6|confirmed',
        ]);

        $user = User::where('email', $request->input('email'))->first();
        if (!$user) {
            return response()->json(['error' => 'No encontramos un usuario con ese correo.'], 404);
        }

        if (Hash::check($request->input('password'), $user->password)) {
            return response()->json([
                'error' => 'La nueva contraseña no puede ser igual a la anterior.'
            ], 422);
        }

        $status = Password::reset(
            $request->only('email', 'password', 'password_confirmation', 'token'),
            function ($user, $password) {
                $user->password = Hash::make($password);
                $user->setRememberToken(Str::random(60));
                $user->save();
            }
        );

        if ($status === Password::PASSWORD_RESET) {
            return response()->json(['message' => 'Tu contraseña fue restablecida correctamente.'], 200);
        }

        return response()->json(['error' => 'El enlace de recuperación es inválido o ha expirado.'], 400);
    }
}
