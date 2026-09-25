<?php

namespace App\Services;

use App\Models\User;
use App\Models\UserToken;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class AuthService
{
    public function __construct(
        private JwtService $jwtService
    ) {}

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

        /*
         * Disabled users cannot login.
         */
        if (!$user->status) {
            return null;
        }

        /*
         * Verify password.
         */
        if (!Hash::check(
            $password,
            $user->password
        )) {
            return null;
        }

        /*
         * Update last login.
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
    | Device ID
    |--------------------------------------------------------------------------
    */

    private function getDeviceId(
        Request $request
    ): string {
        /*
         * Browser sends existing device ID.
         */
        $deviceId = $request->cookie('device_id');

        /*
         * First login from this browser.
         */
        if (!$deviceId) {
            $deviceId = (string) Str::uuid();
        }

        return $deviceId;
    }

    /*
    |--------------------------------------------------------------------------
    | Device Information
    |--------------------------------------------------------------------------
    */

    private function getDeviceInfo(
        Request $request
    ): string {
        $userAgent = $request->userAgent() ?? '';

        if (str_contains(
            strtolower($userAgent),
            'android'
        )) {
            return 'Android';
        }

        if (
            str_contains(
                strtolower($userAgent),
                'iphone'
            )
            ||
            str_contains(
                strtolower($userAgent),
                'ipad'
            )
        ) {
            return 'iOS';
        }

        if (str_contains(
            strtolower($userAgent),
            'windows'
        )) {
            if (str_contains(
                strtolower($userAgent),
                'edg'
            )) {
                return 'Windows / Edge';
            }

            if (str_contains(
                strtolower($userAgent),
                'chrome'
            )) {
                return 'Windows / Chrome';
            }

            if (str_contains(
                strtolower($userAgent),
                'firefox'
            )) {
                return 'Windows / Firefox';
            }

            return 'Windows';
        }

        if (str_contains(
            strtolower($userAgent),
            'macintosh'
        )) {
            return 'macOS';
        }

        if (str_contains(
            strtolower($userAgent),
            'linux'
        )) {
            return 'Linux';
        }

        return 'Unknown';
    }

    /*
    |--------------------------------------------------------------------------
    | Create JWT + Refresh Token
    |--------------------------------------------------------------------------
    */

    public function createTokens(
        Request $request,
        User $user,
        ?string $deviceId = null
    ): array {
        /*
         * Get existing device ID or create one.
         */
        $deviceId ??= $this->getDeviceId($request);

        /*
         * Create JWT access token.
         */
        $accessToken = $this->jwtService->createAccessToken(
            $user
        );

        /*
         * Get exact JTI from JWT.
         */
        $payload = $this->jwtService->payload(
            $accessToken
        );

        if (!$payload || empty($payload['jti'])) {
            throw new \RuntimeException(
                'Unable to create JWT JTI.'
            );
        }

        $jti = (string) $payload['jti'];

        /*
         * Create secure opaque refresh token.
         */
        $refreshToken = bin2hex(
            random_bytes(64)
        );

        /*
         * Token lifetime.
         */
        $accessTtl = (int) config(
            'jwt.access_ttl',
            900
        );

        $refreshTtl = (int) config(
            'jwt.refresh_ttl',
            86400
        );

        $accessExpiry = now()->addSeconds(
            $accessTtl
        );

        $refreshExpiry = now()->addSeconds(
            $refreshTtl
        );

        /*
         * Store token session.
         *
         * IMPORTANT:
         * We do NOT store the raw access JWT.
         */
        UserToken::create([
            'user_id' => $user->user_id,

            'device_id' => $deviceId,

            'jti' => $jti,

            'access_expiry' => $accessExpiry,

            'refresh_token' => hash(
                'sha256',
                $refreshToken
            ),

            'refresh_expiry' => $refreshExpiry,

            'device_info' => $this->getDeviceInfo(
                $request
            ),

            'user_agent' => $request->userAgent(),

            'ip_address' => $request->ip(),
        ]);

        return [
            'access_token' => $accessToken,

            'refresh_token' => $refreshToken,

            'device_id' => $deviceId,

            'token_type' => 'Bearer',

            'expires_in' => $accessTtl,

            'refresh_expires_in' => $refreshTtl,
        ];
    }

    /*
    |--------------------------------------------------------------------------
    | Get JWT Payload
    |--------------------------------------------------------------------------
    */

    public function getTokenPayload(
        string $accessToken
    ): ?array {
        return $this->jwtService->payload(
            $accessToken
        );
    }

    /*
    |--------------------------------------------------------------------------
    | Refresh JWT + Refresh Token
    |--------------------------------------------------------------------------
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
         * Hash incoming refresh token.
         */
        $refreshTokenHash = hash(
            'sha256',
            $refreshToken
        );

        /*
         * Find active refresh token.
         */
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
         * User no longer exists or is disabled.
         */
        if (!$user || !$user->status) {
            return null;
        }

        /*
         * Preserve original device ID.
         */
        $deviceId = $token->device_id;

        /*
         * Refresh token rotation.
         */
        return DB::transaction(
            function () use (
                $request,
                $token,
                $user,
                $deviceId
            ) {
                /*
                 * Revoke old token pair.
                 */
                $token->update([
                    'revoked_at' => now(),
                ]);

                /*
                 * Create new JWT + refresh token.
                 */
                return $this->createTokens(
                    $request,
                    $user,
                    $deviceId
                );
            }
        );
    }

    /*
    |--------------------------------------------------------------------------
    | API Logout / Revoke JWT
    |--------------------------------------------------------------------------
    */

    public function logout(
        string $accessToken
    ): void {
        $accessToken = trim($accessToken);

        if ($accessToken === '') {
            return;
        }

        /*
         * Decode and verify JWT.
         */
        $payload = $this->jwtService->payload(
            $accessToken
        );

        if (!$payload) {
            return;
        }

        /*
         * Get JWT ID.
         */
        $jti = $payload['jti'] ?? null;

        if (!$jti) {
            return;
        }

        /*
         * Revoke token session.
         */
        UserToken::query()
            ->where('jti', $jti)
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