<?php
// ./routes/api.php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\Auth\ApiAuthController as ApiAuthController;

/*
|--------------------------------------------------------------------------
| API v1
|--------------------------------------------------------------------------
*/

Route::prefix('v1')->group(function () {

    /*
    |--------------------------------------------------------------------------
    | Authentication
    |--------------------------------------------------------------------------
    */

    Route::post('/auth/login', [
        ApiAuthController::class,
        'login'
    ])->name('api.v1.auth.login');


    Route::post('/auth/refresh', [
        ApiAuthController::class,
        'refresh'
    ])->name('api.v1.auth.refresh');


    Route::post('/auth/logout', [
        ApiAuthController::class,
        'logout'
    ])->name('api.v1.auth.logout');

    /*
    |--------------------------------------------------------------------------
    | Users
    |--------------------------------------------------------------------------
    */

    Route::get('/users', function (Request $request) {
        return response()->json([
            'message' => 'Users API',
        ]);
    });

    Route::get('/debug-url', function (Request $request) {
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
});
