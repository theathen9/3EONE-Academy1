<?php

namespace App\Services;

use App\Models\User;
use Firebase\JWT\JWT;
use Firebase\JWT\Key;
use Illuminate\Support\Str;
use Throwable;

class JwtService
{
    private string $secret;

    private string $algorithm;

    private string $issuer;

    private int $accessLifetime;

    public function __construct()
    {
        $this->secret = (string) config(
            'jwt.secret'
        );

        $this->algorithm = (string) config(
            'jwt.algorithm',
            'HS256'
        );

        $this->issuer = (string) config(
            'jwt.issuer',
            '3eone-academy'
        );

        $this->accessLifetime = (int) config(
            'jwt.access_ttl',
            900
        );

        if ($this->secret === '') {
            throw new \RuntimeException(
                'JWT_SECRET is not configured.'
            );
        }
    }

    /**
     * Create access JWT.
     */
    public function createAccessToken(User $user): string
    {
        $now = now()->timestamp;

        $payload = [
            'iss' => $this->issuer,

            'sub' => (string) $user->user_id,

            'jti' => (string) Str::uuid(),

            'iat' => $now,

            'nbf' => $now,

            'exp' => $now + $this->accessLifetime,

            'type' => 'access',
        ];

        return JWT::encode(
            $payload,
            $this->secret,
            $this->algorithm
        );
    }

    /**
     * Decode and validate access JWT.
     */
    public function decode(string $token): ?array
    {
        try {
            $payload = JWT::decode(
                $token,
                new Key(
                    $this->secret,
                    $this->algorithm
                )
            );

            /*
             * firebase/php-jwt returns stdClass.
             *
             * Convert it to an array so the rest
             * of the application can use:
             *
             * $payload['sub']
             * $payload['jti']
             * $payload['exp']
             */
            $data = (array) $payload;

            /*
             * Validate issuer.
             */
            if (
                ($data['iss'] ?? null)
                !== $this->issuer
            ) {
                return null;
            }

            /*
             * Only access tokens are accepted.
             */
            if (
                ($data['type'] ?? null)
                !== 'access'
            ) {
                return null;
            }

            /*
             * JTI is required.
             */
            if (
                empty($data['jti'])
            ) {
                return null;
            }

            /*
             * Subject/user ID is required.
             */
            if (
                empty($data['sub'])
            ) {
                return null;
            }

            return $data;

        } catch (Throwable) {
            return null;
        }
    }

    /**
     * Validate access JWT.
     */
    public function validate(string $token): bool
    {
        return $this->decode($token) !== null;
    }

    /**
     * Get JWT payload.
     */
    public function payload(string $token): ?array
    {
        return $this->decode($token);
    }

    /**
     * Get remaining lifetime.
     */
    public function remainingSeconds(
        string $token
    ): ?int {
        $payload = $this->decode($token);

        if (
            !$payload ||
            !isset($payload['exp'])
        ) {
            return null;
        }

        return max(
            0,
            (int) $payload['exp']
            - now()->timestamp
        );
    }
}