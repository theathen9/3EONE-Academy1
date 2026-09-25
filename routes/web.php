<?php

use Illuminate\Support\Facades\Route;

use App\Http\Controllers\Auth\WebAuthController;
use App\Http\Controllers\Admin\DashboardController as AdminDashboardController;


/*
|--------------------------------------------------------------------------
| Web Authentication
|--------------------------------------------------------------------------
*/

Route::middleware('guest')
    ->prefix('auth')
    ->name('auth.')
    ->group(function () {

        Route::get('/signin', [
            WebAuthController::class,
            'show'
        ])->name('signin');

        Route::post('/signin', [
        WebAuthController::class,
         'login'
        ])->name('login');

            // Forgot password
        Route::get('/forgot-password', [
            WebAuthController::class,
            'showForgotPassword'
        ])->name('forgot');

        Route::post('/forgot-password', [
            WebAuthController::class,
            'sendResetLink'
        ])->name('forgot.submit');

        // Reset password
        Route::get('/reset-password/{token}', [
            WebAuthController::class,
            'showResetPassword'
        ])->name('reset');

        Route::post('/reset-password', [
            WebAuthController::class,
            'resetPassword'
        ])->name('reset.submit');

    });


Route::post('/auth/logout', [
    WebAuthController::class,
    'logout'
])
    ->middleware('auth')
    ->name('auth.logout');


/*
|--------------------------------------------------------------------------
| Home
|--------------------------------------------------------------------------
*/

Route::get('/', function () {
    return view('welcome');
});

/*
|--------------------------------------------------------------------------
| Admin
|--------------------------------------------------------------------------
*/

Route::middleware([
    'auth',
    'role:admin'
])
    ->prefix('admin')
    ->name('admin.')
    ->group(function () {

        Route::get('/dashboard', [
            AdminDashboardController::class,
            'index'
        ])->name('dashboard');

    });
