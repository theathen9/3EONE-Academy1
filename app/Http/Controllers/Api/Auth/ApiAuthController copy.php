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
                'id'       => $user->user_id,
                'username' => $user->username,
                'email'    => $user->email,
                'role'     => $user->role->role_name,
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
            'tokens'  => $tokens,
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

public function token(Request $request)
{
    $user = $request->user();

    if (!$user) {
        return response()->json([
            'success' => false,
            'message' => 'Unauthenticated.',
        ], 401);
    }

    return response()->json([
        'success' => true,
        'message' => 'Authenticated token.',
        'user' => [
            'id'       => $user->user_id,
            'username' => $user->username,
            'email'    => $user->email,
            'role'     => $user->role->role_name,
        ],
    ]);
}
}
