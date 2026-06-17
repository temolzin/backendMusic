<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;

class AuthController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth:api', ['except' => ['login']]);
    }

    /**
     * Display the specified resource.
     *
     * @param  Request  $request
     * @return \Illuminate\Http\Response
     */
    public function login(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required',
        ]);

        $throttleKey = Str::lower($request->input('email')) . '|' . $request->ip();

        if (RateLimiter::tooManyAttempts($throttleKey, 3)) {
            $seconds = RateLimiter::availableIn($throttleKey);
            $minutesLeft = ceil($seconds / 60);

            return response()->json([
                'success' => false,
                'error' => "Demasiados intentos. Intenta de nuevo en {$minutesLeft} minutos."
            ], 429);
        }

        $credentials = $request->only('email', 'password');

        if ($token = Auth::attempt($credentials)) {
            RateLimiter::clear($throttleKey);
            
            return $this->respondWithToken($token);
        }

        RateLimiter::hit($throttleKey, 300);

        return response()->json([
            'success' => false,
            'error' => 'Las credenciales no corresponden a ningún usuario'
        ], 401);
    }

    /**
     * Display the specified resource.
     *
     * @param  null  
     * @return \Illuminate\Http\Response
     */
    public function logout()
    {
        try {
            auth()->logout();
            return response()->json([
                'success' => true,
                'message' => 'Se cerró la sesión con éxito',
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
    public function refresh()
    {
        return $this->respondWithToken(auth()->refresh());
    }

    /**
     * Display the specified resource.
     *
     * @param  $token  
     * @return \Illuminate\Http\Response
     */
    protected function respondWithToken($token)
    {
        return response()->json([
            'success' => true,
            'access_token' => $token,
            'token_type' => 'bearer',
            'expires_in' => auth()->factory()->getTTL() * 60
        ]);
    }
}
