<?php

use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});
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
