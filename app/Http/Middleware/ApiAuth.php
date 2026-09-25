<?php

namespace App\Http\Middleware;

use App\Models\UserToken;
use App\Services\JwtService;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class ApiAuth
{
    public function __construct(
        private JwtService $jwtService
    ) {}

    public function handle(
        Request $request,
        Closure $next
    ): Response {

        /*
        |--------------------------------------------------------------------------
        | 1. Get access token
        |--------------------------------------------------------------------------
        |
        | Priority:
        |
        | Authorization: Bearer eyJ...
        |
        | OR:
        |
        | access-token cookie
        |
        */

        $accessToken = $request->bearerToken();

        if (!$accessToken) {
            $accessToken = $request->cookie(
                'access-token'
            );
        }

        /*
        |--------------------------------------------------------------------------
        | 2. Token required
        |--------------------------------------------------------------------------
        */

        if (!$accessToken) {
            return response()->json([
                'success' => false,
                'message' => 'Access token is required.',
            ], 401);
        }

        /*
        |--------------------------------------------------------------------------
        | 3. Decode JWT
        |--------------------------------------------------------------------------
        |
        | JwtService returns an ARRAY.
        |
        */

        $payload = $this->jwtService->decode(
            $accessToken
        );

        if (!$payload) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid or expired access token.',
            ], 401);
        }

        /*
        |--------------------------------------------------------------------------
        | 4. Get JWT claims
        |--------------------------------------------------------------------------
        */

        $userId = $payload['sub'] ?? null;

        $jti = $payload['jti'] ?? null;

        /*
        |--------------------------------------------------------------------------
        | 5. Validate required JWT claims
        |--------------------------------------------------------------------------
        */

        if (
            empty($userId) ||
            empty($jti)
        ) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid access token payload.',
            ], 401);
        }

        /*
        |--------------------------------------------------------------------------
        | 6. Find token in database
        |--------------------------------------------------------------------------
        |
        | The JTI connects the JWT to tblUserTokens.
        |
        */

        $userToken = UserToken::query()
            ->with('user.role')
            ->where(
                'jti',
                $jti
            )
            ->where(
                'user_id',
                $userId
            )
            ->whereNull(
                'revoked_at'
            )
            ->where(
                'access_expiry',
                '>',
                now()
            )
            ->first();

        /*
        |--------------------------------------------------------------------------
        | 7. Check database token
        |--------------------------------------------------------------------------
        */

        if (!$userToken) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid or revoked access token.',
            ], 401);
        }

        /*
        |--------------------------------------------------------------------------
        | 8. Get user
        |--------------------------------------------------------------------------
        */

        $user = $userToken->user;

        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'User not found.',
            ], 401);
        }

        /*
        |--------------------------------------------------------------------------
        | 9. Check user status
        |--------------------------------------------------------------------------
        */

        if (!$user->status) {
            return response()->json([
                'success' => false,
                'message' => 'User account is disabled.',
            ], 403);
        }

        /*
        |--------------------------------------------------------------------------
        | 10. Make user available
        |--------------------------------------------------------------------------
        |
        | Controller can now use:
        |
        | $request->user()
        |
        */

        $request->setUserResolver(
            fn () => $user
        );

        /*
        |--------------------------------------------------------------------------
        | 11. Store token record
        |--------------------------------------------------------------------------
        */

        $request->attributes->set(
            'userToken',
            $userToken
        );

        /*
        |--------------------------------------------------------------------------
        | 12. Store JWT payload
        |--------------------------------------------------------------------------
        */

        $request->attributes->set(
            'jwt',
            $payload
        );

        /*
        |--------------------------------------------------------------------------
        | 13. Store raw access token
        |--------------------------------------------------------------------------
        */

        $request->attributes->set(
            'accessToken',
            $accessToken
        );

        return $next($request);
    }
}