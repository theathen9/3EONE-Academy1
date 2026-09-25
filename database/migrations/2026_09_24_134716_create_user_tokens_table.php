<?php

// 2026_09_24_134716_create_user_tokens_table.php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('tblUserTokens', function (Blueprint $table) {

            /*
             * Primary Key
             */
            $table->id('token_id');


            /*
             * User
             */
            $table->foreignId('user_id')
                ->constrained('tblUsers', 'user_id')
                ->cascadeOnDelete();


            /*
             * Browser / Device Identifier
             */
            $table->string('device_id', 100);


            /*
             * JWT ID
             */
            $table->uuid('jti')
                ->unique();


            /*
             * JWT Access Token Expiration
             */
            $table->timestampTz('access_expiry');


            /*
             * SHA-256 hash of refresh token
             */
            $table->char('refresh_token', 64)
                ->unique();


            /*
             * Refresh-token expiration
             */
            $table->timestampTz('refresh_expiry');


            /*
             * Human-readable device information
             */
            $table->string('device_info', 255)
                ->nullable();


            /*
             * Browser / Client User-Agent
             */
            $table->text('user_agent')
                ->nullable();


            /*
             * Client IP address
             */
            $table->string('ip_address', 45)
                ->nullable();


            /*
             * Session created timestamp
             */
            $table->timestampTz('created_at')
                ->useCurrent();


            /*
             * NULL = active
             * timestamp = revoked
             */
            $table->timestampTz('revoked_at')
                ->nullable();


            /*
             * Indexes
             */

            // Find sessions belonging to a user's device
            $table->index(
                ['user_id', 'device_id'],
                'idx_user_tokens_user_device'
            );

            // Find expired refresh tokens
            $table->index(
                'refresh_expiry',
                'idx_user_tokens_refresh_expiry'
            );

            // Find revoked/active sessions
            $table->index(
                'revoked_at',
                'idx_user_tokens_revoked_at'
            );
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tblUserTokens');
    }
};