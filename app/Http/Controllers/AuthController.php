<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;
use Tymon\JWTAuth\Exceptions\JWTException;
use Tymon\JWTAuth\Exceptions\TokenExpiredException;
use Tymon\JWTAuth\Facades\JWTAuth;

class AuthController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth:api', ['except' => ['login', 'refresh']]);
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
            ], 500);
        }
    }

    /**
     * Display the specified resource.
     *
     * @param  null  
     * @return \Illuminate\Http\Response
     */
    public function refresh(Request $request)
    {
        $token = $request->bearerToken();

        if (! $token) {
            return response()->json([
                'success' => false,
                'message' => 'Token no proporcionado',
            ], 401);
        }

        try {
            $newToken = JWTAuth::setToken($token)->refresh();

            return $this->respondWithToken($newToken);
        } catch (TokenExpiredException $e) {
            return response()->json([
                'success' => false,
                'message' => 'La sesión expiró, inicia sesión de nuevo',
            ], 401);
        } catch (JWTException $e) {
            return response()->json([
                'success' => false,
                'message' => 'No fue posible renovar la sesión',
            ], 401);
        }
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
