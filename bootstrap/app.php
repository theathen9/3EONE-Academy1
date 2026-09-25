<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;

use App\Http\Middleware\ApiAuth;
use App\Http\Middleware\RoleMiddleware;

return Application::configure(
    basePath: dirname(__DIR__)
)
    ->withRouting(
        api: __DIR__.'/../routes/api.php',
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )

    ->withMiddleware(function (
        Middleware $middleware
    ): void {

        /*
        |--------------------------------------------------------------------------
        | Cookie Encryption
        |--------------------------------------------------------------------------
        |
        | JWT access and refresh tokens are already protected
        | by HTTPS + HttpOnly cookies.
        |
        | We need the raw JWT value so ApiAuth middleware can
        | read:
        |
        |     $request->cookie('access-token')
        |
        */

        $middleware->encryptCookies(except: [
            'access-token',
            'refresh-token',
        ]);


        
        $middleware->trustProxies(
        at: '*',
        headers: Request::HEADER_X_FORWARDED_FOR
            | Request::HEADER_X_FORWARDED_HOST
            | Request::HEADER_X_FORWARDED_PORT
            | Request::HEADER_X_FORWARDED_PROTO
    );


        /*
        |--------------------------------------------------------------------------
        | Middleware Aliases
        |--------------------------------------------------------------------------
        */

        $middleware->alias([
            'role' => RoleMiddleware::class,
            'api.auth' => ApiAuth::class,
        ]);
    })

    ->withExceptions(function (
        Exceptions $exceptions
    ): void {

        /*
        |--------------------------------------------------------------------------
        | API Exceptions
        |--------------------------------------------------------------------------
        */

        $exceptions->shouldRenderJsonWhen(
            fn (Request $request) =>
                $request->is('api/*')
        );
    })

    ->create();
