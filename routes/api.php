<?php

use App\Http\Controllers\Artist\ArtistController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\Client\ClientController;
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
    Route::put('/user/change-details', [UsersController::class, 'updateDetails']);
    Route::put('/user/change-password', [UsersController::class, 'updatePassword']);
    Route::post('/user/change-image-profile', [UsersController::class, 'updateImageProfile']);

    //Route for user
    Route::resource('/admin/users', UserApiController::class);
    Route::resource('/admin/roles', RolesApiController::class);
    Route::resource('/admin/permissions', PermissionsApiController::class);
    //Route for artist
    Route::post('/artist-new/up-date/{id}', [ArtistController::class, 'updateDetails']);
    Route::resource('/artist-new', ArtistController::class);
    //Route for client
    Route::resource('/client-card', ClientController::class);
});
// Test route
Route::resource('/product', ProductController::class);
