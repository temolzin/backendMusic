<?php

use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

Route::get('/', function () {
    return view('welcome');
});

Route::get('/password/reset/{token}', function ($token) {
    $frontend = env('FRONTEND_URL', 'http://localhost:8080');
    $url = rtrim($frontend, '/') . '/reset-password?token=' . urlencode($token);

    if (request()->filled('email')) {
        $url .= '&email=' . urlencode(request()->query('email'));
    }

    return redirect($url);
})->name('password.reset');
