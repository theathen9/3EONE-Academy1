<?php

use App\Http\Controllers\Api\Auth\ApiAuthController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API v1
|--------------------------------------------------------------------------
*/

Route::prefix('v1')->group(function () {

    /*
    |--------------------------------------------------------------------------
    | Authentication - Public
    |--------------------------------------------------------------------------
    */

    Route::post('/auth/login', [
        ApiAuthController::class,
        'login',
    ])->name('api.v1.auth.login');

    Route::post('/auth/refresh', [
        ApiAuthController::class,
        'refresh',
    ])->name('api.v1.auth.refresh');


    /*
    |--------------------------------------------------------------------------
    | Authentication - JWT Protected
    |--------------------------------------------------------------------------
    */

    Route::middleware('api.auth')->group(function () {

        Route::get('/auth/token', [
            ApiAuthController::class,
            'token',
        ])->name('api.v1.auth.token');

        Route::post('/auth/logout', [
            ApiAuthController::class,
            'logout',
        ])->name('api.v1.auth.logout');


        /*
        |--------------------------------------------------------------------------
        | Users - JWT Protected
        |--------------------------------------------------------------------------
        */

        Route::get('/users', function () {
            return response()->json([
                'success' => true,
                'message' => 'Users API',
            ]);
        });

    });


    /*
    |--------------------------------------------------------------------------
    | Debug
    |--------------------------------------------------------------------------
    */

    Route::get('/debug-url', function () {
        return response()->json([
            'url' => url('/'),
            'asset' => asset('build/assets/app.css'),
        ]);
    })->name('api.v1.debug-url');

});