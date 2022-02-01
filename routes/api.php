<?php

use App\Http\Controllers\AuthController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\SocialAuthController;
use App\Http\Controllers\VerificationController;

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});


Route::get('/greeting', function () {
    return 'Hello World';
});

    Route::post('/login', [AuthController::class, 'login']);
    Route::post('/register', [AuthController::class, 'create']);
    Route::get('/logout', [AuthController::class, 'logout']);
    Route::get('/me', [AuthController::class, 'me']);
    Route::get('/refresh', [AuthController::class, 'refresh']);

    Route::get('/authorize/google/redirect', [SocialAuthController::class, 'redirectToProvider']);
    Route::get('/authorize/google/callback', [SocialAuthController::class, 'handlesProviderCallback']);

    Route::resource('/product', ProductController::class);


    
    Route::get('/email-verification', [VerificationController::class, 'verify']);