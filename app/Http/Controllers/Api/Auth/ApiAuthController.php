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

    public function login(Request $request)
    {
        $validated = $request->validate([
            'username' => [
                'required',
                'string',
                'max:255',
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


    public function refresh(Request $request)
    {
        // refresh token logic
    }


    public function logout(Request $request)
    {
        // API logout logic
    }
}