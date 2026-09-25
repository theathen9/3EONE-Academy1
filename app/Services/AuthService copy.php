<?php

namespace App\Services;

use App\Models\User;
use App\Models\UserToken;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class AuthService
{
    /*
    |--------------------------------------------------------------------------
    | Find + Verify User
    |--------------------------------------------------------------------------
    */

    public function authenticate(
        string $login,
        string $password
    ): ?User {
        $login = trim($login);

        $user = User::query()
            ->with('role')
            ->where(function ($query) use ($login) {
                $query->where('username', $login)
                    ->orWhere('email', $login);

                if (filter_var($login, FILTER_VALIDATE_INT)) {
                    $query->orWhere(
                        'user_id',
                        (int) $login
                    );
                }
            })
            ->first();

        if (!$user) {
            return null;
        }

        // Prevent disabled users from logging in.
        if (!$user->status) {
            return null;
        }

        if (!Hash::check(
            $password,
            $user->password
        )) {
            return null;
        }

        /*
         * Update last_login only after successful authentication.
         *
         * Do not put this inside createTokens(), because
         * createTokens() is also called during token refresh.
         */
        $user->update([
            'last_login' => now(),
        ]);

        return $user;
    }


    /*
    |--------------------------------------------------------------------------
    | Web Login
    |--------------------------------------------------------------------------
    */

    public function loginWeb(
        Request $request,
        User $user
    ): void {
        Auth::login($user);

        $request->session()->regenerate();

        session([
            'loggedin' => true,

            'user_id' => $user->user_id,

            'role' => strtolower(
                $user->role->role_name
            ),

            'reference_id' => $user->reference_id,

            'reference_type' => $user->reference_type,
        ]);
    }


    /*
    |--------------------------------------------------------------------------
    | Create API Token Pair
    |--------------------------------------------------------------------------
    |
    | Access token:
    |   Raw: 64 characters
    |   DB:  SHA-256 hash
    |   Life: 15 minutes
    |
    | Refresh token:
    |   Raw: 128 characters
    |   DB:  SHA-256 hash
    |   Life: 1 day
    |
    */

    public function createTokens(
        Request $request,
        User $user
    ): array {
        /*
         * Generate cryptographically secure random tokens.
         */
        $accessToken = bin2hex(
            random_bytes(32)
        );

        $refreshToken = bin2hex(
            random_bytes(64)
        );

        /*
         * Token expiration.
         */
        $accessExpiry = now()->addMinutes(15);

        $refreshExpiry = now()->addDay();

        /*
         * Store ONLY hashes in the database.
         *
         * The raw tokens are returned to the caller and
         * placed into HttpOnly cookies by the controller.
         */
        UserToken::create([
            'user_id' => $user->user_id,

            'access_token' => hash(
                'sha256',
                $accessToken
            ),

            'refresh_token' => hash(
                'sha256',
                $refreshToken
            ),

            'access_expiry' => $accessExpiry,

            'refresh_expiry' => $refreshExpiry,

            'device_info' => null,

            'user_agent' => $request->userAgent(),

            'ip_address' => $request->ip(),
        ]);

        /*
         * Return RAW tokens.
         *
         * These should never be stored directly in the database.
         */
        return [
            'access_token' => $accessToken,

            'refresh_token' => $refreshToken,

            'token_type' => 'Bearer',

            'expires_in' => 900, // 15 minutes

            'refresh_expires_in' => 86400, // 1 day
        ];
    }


    /*
    |--------------------------------------------------------------------------
    | Refresh API Tokens
    |--------------------------------------------------------------------------
    |
    | Refresh-token rotation:
    |
    |   Refresh A
    |       ↓
    |   validate
    |       ↓
    |   revoke A
    |       ↓
    |   create B
    |       ↓
    |   Access B + Refresh B
    |
    | The old refresh token cannot be reused.
    |
    */

    public function refreshTokens(
        Request $request,
        string $refreshToken
    ): ?array {
        $refreshToken = trim($refreshToken);

        if ($refreshToken === '') {
            return null;
        }

        /*
         * Hash the RAW refresh token received from
         * the cookie before searching the database.
         */
        $refreshTokenHash = hash(
            'sha256',
            $refreshToken
        );

        $token = UserToken::query()
            ->with('user.role')
            ->where(
                'refresh_token',
                $refreshTokenHash
            )
            ->whereNull('revoked_at')
            ->where(
                'refresh_expiry',
                '>',
                now()
            )
            ->first();

        if (!$token) {
            return null;
        }

        $user = $token->user;

        /*
         * Token belongs to a disabled/deleted user.
         */
        if (!$user || !$user->status) {
            return null;
        }

        /*
         * Revoke old token pair and create the new pair
         * inside one database transaction.
         */
        return DB::transaction(function () use (
            $request,
            $token,
            $user
        ) {
            /*
             * Revoke the old access + refresh token pair.
             */
            $token->update([
                'revoked_at' => now(),
            ]);

            /*
             * Create completely new access + refresh tokens.
             */
            return $this->createTokens(
                $request,
                $user
            );
        });
    }


    /*
    |--------------------------------------------------------------------------
    | API Logout
    |--------------------------------------------------------------------------
    |
    | The access token identifies the token pair.
    | Revoking the row also invalidates its refresh token.
    |
    */

    public function logout(
        string $accessToken
    ): void {
        $accessToken = trim($accessToken);

        if ($accessToken === '') {
            return;
        }

        /*
         * Hash the RAW access token received from
         * the Authorization header or cookie.
         */
        $accessTokenHash = hash(
            'sha256',
            $accessToken
        );

        UserToken::query()
            ->where(
                'access_token',
                $accessTokenHash
            )
            ->whereNull('revoked_at')
            ->update([
                'revoked_at' => now(),
            ]);
    }


    /*
    |--------------------------------------------------------------------------
    | Web Logout
    |--------------------------------------------------------------------------
    */

    public function logoutWeb(
        Request $request
    ): void {
        Auth::logout();

        $request->session()->invalidate();

        $request->session()->regenerateToken();
    }
}