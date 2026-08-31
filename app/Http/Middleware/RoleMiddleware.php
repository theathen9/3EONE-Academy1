<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class RoleMiddleware
{
    public function handle(
        Request $request,
        Closure $next,
        ...$roles
    ): Response {

        $user = $request->user();

        if (!$user) {
            return redirect()
                ->route('login');
        }

        $role = strtolower(
            $user->role->role_name
        );

        if (!in_array($role, $roles, true)) {
            abort(403, 'Unauthorized.');
        }

        return $next($request);
    }
}