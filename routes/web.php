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
        ])->name('signin.submit');

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
<<<<<<< Updated upstream
Route::get('/debug-url', function (\Illuminate\Http\Request $request) {
    return response()->json([
        'url' => url('/'),
        'asset' => asset('build/assets/app.css'),
        'scheme' => $request->getScheme(),
        'is_secure' => $request->isSecure(),
        'forwarded_proto' => $request->header('x-forwarded-proto'),
        'app_url' => config('app.url'),
        'asset_url' => config('app.asset_url'),
    ]);
});
=======



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

>>>>>>> Stashed changes
