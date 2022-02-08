<?php

use App\Http\Controllers\AuthController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\SocialAuthController;
use App\Http\Controllers\UsersController;

// Routes for login without sesion
Route::post('/login', [AuthController::class, 'login']);
Route::post('/register', [UsersController::class, 'create']);
Route::get('/refresh', [AuthController::class, 'refresh']);
// Routes for login with google
Route::get('/authorize/google/redirect', [SocialAuthController::class, 'redirectToProvider']);
Route::get('/authorize/google/callback', [SocialAuthController::class, 'handlesProviderCallback']);
// Routes protected by session middleware
Route::group(["middleware" => "auth:api"], function () {
    Route::get('/me', [UsersController::class, 'me']);
    Route::get('/logout', [AuthController::class, 'logout']);
});
// Test route
Route::resource('/product', ProductController::class);
