<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\PermissionsApiController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\RolesApiController;
use App\Http\Controllers\SocialAuthController;
use App\Http\Controllers\UserApiController;
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
    //Route for user
    Route::resource('/user', UserApiController::class);
    Route::resource('/role', RolesApiController::class);
    Route::resource('/permission', PermissionsApiController::class);
});
// Test route
Route::resource('/product', ProductController::class);
