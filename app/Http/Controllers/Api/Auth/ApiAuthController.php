<?php

namespace App\Http\Controllers\Api\Auth;

use App\Http\Controllers\Controller;
use App\Services\AuthService;
use Illuminate\Http\Request;

class ApiAuthController extends Controller
{
    public function __construct(
        private AuthService $authService
    ) {}

    /**
     * Login
     */
    public function login(Request $request)
    {
        $validated = $request->validate([
            'username' => [
                'required',
                'string',
                'max:100',
            ],

            'password' => [
                'required',
                'string',
            ],
        ]);

        $user = $this->authService->authenticate(
            $validated['username'],
            $validated['password']
        );

        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid username or password.',
            ], 401);
        }

        $tokens = $this->authService->createTokens(
            $request,
            $user
        );

        return response()->json([
            'success' => true,
            'message' => 'Login successful.',

            'user' => [
                'id' => $user->user_id,
                'username' => $user->username,
                'email' => $user->email,
                'role' => $user->role->role_name,
            ],

            'tokens' => $tokens,
        ]);
    }

    /**
     * Refresh access token
     */
    public function refresh(Request $request)
{
    $validated = $request->validate([
        'refresh_token' => [
            'required',
            'string',
        ],
    ]);

    $tokens = $this->authService->refreshTokens(
        $request,
        $validated['refresh_token']
    );

    if (!$tokens) {
        return response()->json([
            'success' => false,
            'message' => 'Invalid or expired refresh token.',
        ], 401);
    }

    return response()->json([
        'success' => true,
        'message' => 'Token refreshed successfully.',
        'tokens' => $tokens,
    ]);
}

    /**
     * Logout
     */
    public function logout(Request $request)
    {
        $accessToken = $request->bearerToken();

        if (!$accessToken) {
            return response()->json([
                'success' => false,
                'message' => 'Access token is required.',
            ], 401);
        }

        $this->authService->logout($accessToken);

        return response()->json([
            'success' => true,
            'message' => 'Logout successful.',
        ]);
    }

    /**
     * Get current authenticated JWT information.
     */
//     public function token(Request $request)
// {
//     $accessToken = $request->bearerToken();

//     if (!$accessToken) {
//         return response()->json([
//             'success' => false,
//             'message' => 'Access token is required.',
//         ], 401);
//     }

//     $payload = $this->authService->getTokenPayload(
//         $accessToken
//     );

//     if (!$payload) {
//         return response()->json([
//             'success' => false,
//             'message' => 'Invalid or expired access token.',
//         ], 401);
//     }

//     $user = $request->user();

//     if (!$user) {
//         return response()->json([
//             'success' => false,
//             'message' => 'Unauthenticated.',
//         ], 401);
//     }

//     $now = now()->timestamp;

//     $issuedAt = isset($payload['iat'])
//         ? (int) $payload['iat']
//         : null;

//     $expiresAt = isset($payload['exp'])
//         ? (int) $payload['exp']
//         : null;

//     $remainingSeconds = $expiresAt
//         ? max(0, $expiresAt - $now)
//         : null;

//     return response()->json([
//         'success' => true,

//         'message' => 'Token is valid.',

//         'token' => [
//             'type' => 'Bearer',

//             'valid' => true,

//             'algorithm' => 'HS256',

//             'issuer' => $payload['iss'] ?? null,

//             'type_claim' => $payload['type'] ?? null,

//             'jti' => $payload['jti'] ?? null,

//             'subject' => $payload['sub'] ?? null,

//             'issued_at' => $issuedAt
//                 ? date(
//                     'Y-m-d H:i:s',
//                     $issuedAt
//                 )
//                 : null,

//             'expires_at' => $expiresAt
//                 ? date(
//                     'Y-m-d H:i:s',
//                     $expiresAt
//                 )
//                 : null,

//             'remaining_seconds' =>
//                 $remainingSeconds,

//             'remaining_minutes' =>
//                 $remainingSeconds !== null
//                     ? (int) ceil(
//                         $remainingSeconds / 60
//                     )
//                     : null,
//         ],

//         'user' => [
//             'id' => $user->user_id,

//             'username' => $user->username,

//             'email' => $user->email,

//             'role' => $user->role->role_name,
//         ],
//     ]);
// }

/**
 * Get current authenticated JWT information.
 */
// public function token(Request $request)
// {
//     /*
//      * ApiAuth middleware already:
//      *
//      * 1. Gets Bearer token OR cookie
//      * 2. Validates JWT
//      * 3. Checks JTI
//      * 4. Checks database token
//      * 5. Checks user
//      *
//      * Therefore we get the validated JWT from
//      * the request attributes.
//      */
//     $accessToken = $request->attributes->get('accessToken');

//     /*
//      * Safety check.
//      */
//     if (!$accessToken) {
//         return response()->json([
//             'success' => false,
//             'message' => 'Access token is required.',
//         ], 401);
//     }

//     /*
//      * Get validated JWT payload.
//      */
//     $payload = $request->attributes->get('jwt');

//     if (!$payload) {
//         $payload = $this->authService->getTokenPayload(
//             $accessToken
//         );
//     }

//     if (!$payload) {
//         return response()->json([
//             'success' => false,
//             'message' => 'Invalid or expired access token.',
//         ], 401);
//     }

//     /*
//      * Authenticated user was also set
//      * by ApiAuth middleware.
//      */
//     $user = $request->user();

//     if (!$user) {
//         return response()->json([
//             'success' => false,
//             'message' => 'Unauthenticated.',
//         ], 401);
//     }

//     /*
//      * Token timestamps.
//      */
//     $now = now()->timestamp;

//     $issuedAt = isset($payload['iat'])
//         ? (int) $payload['iat']
//         : null;

//     $expiresAt = isset($payload['exp'])
//         ? (int) $payload['exp']
//         : null;

//     $remainingSeconds = $expiresAt !== null
//         ? max(0, $expiresAt - $now)
//         : null;

//     /*
//      * Return token information.
//      */
//     return response()->json([
//         'success' => true,

//         'message' => 'Token is valid.',

//         'token' => [
//             'type' => 'Bearer',

//             'valid' => true,

//             'algorithm' => config(
//                 'jwt.algorithm',
//                 'HS256'
//             ),

//             'issuer' => $payload['iss'] ?? null,

//             'type_claim' => $payload['type'] ?? null,

//             'jti' => $payload['jti'] ?? null,

//             'subject' => $payload['sub'] ?? null,

//             'issued_at' => $issuedAt
//                 ? date(
//                     'Y-m-d H:i:s',
//                     $issuedAt
//                 )
//                 : null,

//             'expires_at' => $expiresAt
//                 ? date(
//                     'Y-m-d H:i:s',
//                     $expiresAt
//                 )
//                 : null,

//             'remaining_seconds' => $remainingSeconds,

//             'remaining_minutes' =>
//                 $remainingSeconds !== null
//                     ? (int) ceil(
//                         $remainingSeconds / 60
//                     )
//                     : null,
//         ],

//         'user' => [
//             'id' => $user->user_id,

//             'username' => $user->username,

//             'email' => $user->email,

//             'role' => $user->role->role_name,
//         ],
//     ]);
// }


public function token(Request $request)
{
    /*
    |--------------------------------------------------------------------------
    | Get access token
    |--------------------------------------------------------------------------
    */

    $accessToken = $request->bearerToken();
    $refreshToken = $request->bearerToken();

    if (!$accessToken) {
        $accessToken = $request->cookie('access-token');
    }

       if (!$refreshToken) {
        $refreshToken = $request->cookie('refresh-token');
    }

    if (!$accessToken) {
        return response()->json([
            'success' => false,
            'message' => 'Access token is required.',
        ], 401);
    }

    /*
    |--------------------------------------------------------------------------
    | JWT payload
    |--------------------------------------------------------------------------
    */

    $payload = $this->authService->getTokenPayload(
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
    | Authenticated user
    |--------------------------------------------------------------------------
    */

    $user = $request->user();

    if (!$user) {
        return response()->json([
            'success' => false,
            'message' => 'Unauthenticated.',
        ], 401);
    }

    /*
    |--------------------------------------------------------------------------
    | Token record from ApiAuth middleware
    |--------------------------------------------------------------------------
    */

    $userToken = $request->attributes->get('userToken');

    if (!$userToken) {
        return response()->json([
            'success' => false,
            'message' => 'Token record not found.',
        ], 401);
    }

    /*
    |--------------------------------------------------------------------------
    | Calculate expiration
    |--------------------------------------------------------------------------
    */

    $now = now()->timestamp;

    $issuedAt = isset($payload['iat'])
        ? (int) $payload['iat']
        : null;

    $expiresAt = isset($payload['exp'])
        ? (int) $payload['exp']
        : null;

    $remainingSeconds = $expiresAt
        ? max(0, $expiresAt - $now)
        : null;

    /*
    |--------------------------------------------------------------------------
    | Return response
    |--------------------------------------------------------------------------
    */

    return response()->json([
        'success' => true,

        'message' => 'Token is valid.',

         'user' => [
            'id' => $user->user_id,

            'username' => $user->username,

            'email' => $user->email,

            'role' => $user->role->role_name,
        ],

        /*
        |--------------------------------------------------------------------------
        | Raw tokens
        |--------------------------------------------------------------------------
        */


        /*
         * IMPORTANT:
         * refresh_token is stored hashed in the database.
         *
         * Therefore we CANNOT recover the original
         * refresh token from tblUserTokens.
         */

        'token' => [
            'type' => 'Bearer',

            'valid' => true,

            'algorithm' => $payload['alg'] ?? 'HS256',

            'issuer' => $payload['iss'] ?? null,

            'type_claim' => $payload['type'] ?? null,

            'jti' => $payload['jti'] ?? null,

            'subject' => $payload['sub'] ?? null,

            'issued_at' => $issuedAt
                ? date(
                    'Y-m-d H:i:s',
                    $issuedAt
                )
                : null,
        'access_token' => $accessToken,
        'refresh_token' => $refreshToken,



            'expires_at' => $expiresAt
                ? date(
                    'Y-m-d H:i:s',
                    $expiresAt
                )
                : null,

            'remaining_seconds' =>
                $remainingSeconds,

            'remaining_minutes' =>
                $remainingSeconds !== null
                    ? (int) ceil(
                        $remainingSeconds / 60
                    )
                    : null,
        ],

       
    ]);
}
}