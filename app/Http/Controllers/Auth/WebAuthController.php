<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Services\AuthService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class WebAuthController extends Controller
{
    public function __construct(
        private AuthService $authService
    ) {}

    /**
     * Show login page.
     */
    public function show()
    {
        return view('auth.signin');
    }

    /**
     * Process web login.
     *
     * Creates:
     * - Laravel web session
     * - API access token
     * - API refresh token
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
        return back()
            ->withInput(
                $request->only('username')
            )
            ->withErrors([
                'username' => 'Invalid username or password.',
            ]);
    }

    /*
     * Create JWT + refresh token.
     */
    $tokens = $this->authService->createTokens(
        $request,
        $user
    );

    /*
     * Create Laravel web session.
     */
    $this->authService->loginWeb(
        $request,
        $user
    );

    /*
     * Basic frontend display data.
     *
     * DO NOT put passwords, tokens, or sensitive
     * information into this cookie.
     */
    $cUser = [
        'id' => $user->user_id,
        'username' => $user->username,
        'role' => $user->role->role_name,
    ];

    /*
     * Determine dashboard.
     */
    $response = match (strtolower(
        $user->role->role_name
    )) {

        'admin' =>
            redirect()->route('admin.dashboard'),

        'accountant' =>
            redirect()->route('account.dashboard'),

        'teacher' =>
            redirect()->route('teacher.dashboard'),

        'student' =>
            redirect()->route('student.dashboard'),

        default =>
            abort(403, 'Invalid user role.'),
    };

    /*
     * JWT:
     * 15 minutes
     *
     * Refresh:
     * 1 day
     *
     * c_user:
     * 1 day
     */
    return $response

        /*
         * JWT access token.
         */
        ->withCookie(
            cookie(
                'access-token',
                $tokens['access_token'],
                15,
                '/',
                null,
                app()->environment('production'),
                true,
                false,
                'Lax'
            )
        )

        /*
         * Refresh token.
         */
        ->withCookie(
            cookie(
                'refresh-token',
                $tokens['refresh_token'],
                60 * 24,
                '/',
                null,
                app()->environment('production'),
                true,
                false,
                'Lax'
            )
        )

        /*
         * Frontend user information.
         *
         * HttpOnly = false
         */
        ->withCookie(
            cookie(
                'c_user',
                json_encode($cUser),
                60 * 24,
                '/',
                null,
                app()->environment('production'),
                false,
                false,
                'Lax'
            )
        );
}

    /**
     * Show forgot-password page.
     */
    public function showForgotPassword()
    {
        return view('auth.forgot-password');
    }

    /**
     * Generate password reset token.
     */
    public function sendResetLink(Request $request)
    {
        $validated = $request->validate([
            'email' => [
                'required',
                'email',
                'max:150',
            ],
        ]);

        $user = User::where(
            'email',
            $validated['email']
        )->first();

        /*
         * Don't reveal whether the email exists.
         */
        if (!$user) {
            return back()->with(
                'status',
                'If that email exists, a password reset link has been generated.'
            );
        }

        /*
         * Generate secure random token.
         */
        $token = Str::random(64);

        /*
         * Store only the SHA-256 hash.
         */
        $user->update([
            'reset_token' => hash(
                'sha256',
                $token
            ),
            'reset_expiry' => now()->addMinutes(30),
        ]);

        /*
         * Development only.
         *
         * In production this URL should
         * be sent by email.
         */
        $resetUrl = route(
            'auth.reset',
            [
                'token' => $token,
            ]
        );

        return back()
            ->with(
                'status',
                'Password reset link generated.'
            )
            ->with(
                'reset_url',
                $resetUrl
            );
    }

    /**
     * Show reset-password page.
     */
    public function showResetPassword(string $token)
    {
        $user = User::where(
            'reset_token',
            hash('sha256', $token)
        )
            ->where(
                'reset_expiry',
                '>',
                now()
            )
            ->first();

        if (!$user) {
            return redirect()
                ->route('auth.forgot')
                ->withErrors([
                    'email' =>
                        'This password reset link is invalid or expired.',
                ]);
        }

        return view(
            'auth.reset-password',
            [
                'token' => $token,
            ]
        );
    }

    /**
     * Reset user password.
     */
    public function resetPassword(Request $request)
    {
        $validated = $request->validate([
            'token' => [
                'required',
                'string',
            ],
            'password' => [
                'required',
                'string',
                'min:8',
                'confirmed',
            ],
        ]);

        $user = User::where(
            'reset_token',
            hash(
                'sha256',
                $validated['token']
            )
        )
            ->where(
                'reset_expiry',
                '>',
                now()
            )
            ->first();

        if (!$user) {
            return redirect()
                ->route('auth.forgot')
                ->withErrors([
                    'email' =>
                        'This password reset link is invalid or expired.',
                ]);
        }

        /*
         * Update password and invalidate
         * the reset token.
         */
        $user->update([
            'password' => Hash::make(
                $validated['password']
            ),
            'reset_token' => null,
            'reset_expiry' => null,
        ]);

        /*
         * Optional security improvement:
         * revoke existing API tokens after
         * password reset.
         */
        $user->tokens()->update([
            'revoked_at' => now(),
        ]);

        return redirect()
            ->route('auth.signin')
            ->with(
                'status',
                'Your password has been reset successfully.'
            );
    }

    /**
     * Logout from web session and revoke
     * current API token.
     */
    public function logout(Request $request)
    {
        /*
         * Revoke API token if available.
         */
        $accessToken = $request->cookie(
            'access-token'
        );

        if ($accessToken) {
            $this->authService->logout(
                $accessToken
            );
        }

        /*
         * Logout Laravel session.
         */
        $this->authService->logoutWeb(
            $request
        );

        /*
         * Remove token cookies.
         */
        return redirect()
            ->route('auth.signin')
            ->withCookie(
                cookie()->forget('access-token')
            )
            ->withCookie(
                cookie()->forget('refresh-token')
            );
    }
}