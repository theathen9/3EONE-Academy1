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

            $table->id('token_id');

            /*
             * User
             */
            $table->foreignId('user_id')
                ->constrained('tblUsers', 'user_id')
                ->cascadeOnDelete();

            /*
             * JWT ID
             */
            $table->uuid('jti')
                ->unique();

            /*
             * JWT access token
             */
            $table->string('access_token', 255);

            /*
             * JWT expiration
             */
            $table->timestamp('access_expiry');

            /*
             * SHA-256 hash of refresh token
             */
            $table->char('refresh_token', 64)
                ->unique();

            /*
             * Refresh-token expiration
             */
            $table->timestamp('refresh_expiry');

            /*
             * Device information
             */
            $table->string('device_info', 255)
                ->nullable();

            /*
             * User agent
             */
            $table->text('user_agent')
                ->nullable();

            /*
             * IP address
             */
            $table->string('ip_address', 45)
                ->nullable();

            /*
             * Created timestamp
             */
            $table->timestamp('created_at')
                ->useCurrent();

            /*
             * NULL = active
             * timestamp = revoked
             */
            $table->timestamp('revoked_at')
                ->nullable();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tblUserTokens');
    }
};