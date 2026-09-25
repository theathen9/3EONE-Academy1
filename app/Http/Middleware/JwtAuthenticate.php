<?php

namespace App\Http\Middleware;

use App\Models\User;
use App\Services\JwtService;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class JwtAuthenticate
{
    public function __construct(
        private JwtService $jwtService
    ) {}

    public function handle(
        Request $request,
        Closure $next
    ): Response {
        $token = $request->bearerToken();

        if (!$token) {
            return response()->json([
                'success' => false,
                'message' => 'Access token is required.',
            ], 401);
        }

        $payload = $this->jwtService->decode(
            $token
        );

        if (!$payload) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid or expired access token.',
            ], 401);
        }

        $user = User::query()
            ->with('role')
            ->where(
                'user_id',
                $payload['sub']
            )
            ->where('status', true)
            ->first();

        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'User not found or disabled.',
            ], 401);
        }

        /*
         * Make authenticated user available to:
         *
         * $request->user()
         */
        $request->setUserResolver(
            fn () => $user
        );

        return $next($request);
    }
}