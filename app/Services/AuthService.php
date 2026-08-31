<?php

namespace App\Services;

use App\Models\User;
use App\Models\UserToken;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class AuthService
{
    /*
    |--------------------------------------------------------------------------
    | Find + verify user
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

        if (!Hash::check(
            $password,
            $user->password
        )) {
            return null;
        }

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

            'reference_id' =>
                $user->reference_id,

            'reference_type' =>
                $user->reference_type,
        ]);
    }


    /*
    |--------------------------------------------------------------------------
    | API Tokens
    |--------------------------------------------------------------------------
    */

    public function createTokens(
        Request $request,
        User $user
    ): array {

        $accessToken = bin2hex(
            random_bytes(32)
        );

        $refreshToken = bin2hex(
            random_bytes(64)
        );

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

            'access_expiry' =>
                now()->addMinutes(5),

            'refresh_expiry' =>
                now()->addDay(),

            'user_agent' =>
                $request->userAgent(),

            'ip_address' =>
                $request->ip(),
        ]);

        return [
            'access_token' =>
                $accessToken,

            'refresh_token' =>
                $refreshToken,

            'token_type' =>
                'Bearer',

            'expires_in' =>
                300,
        ];
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