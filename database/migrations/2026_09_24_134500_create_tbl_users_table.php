<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('tblUsers', function (Blueprint $table) {
            $table->id('user_id');

            $table->unsignedBigInteger('reference_id');

            $table->string('reference_type', 50);

            $table->string('username', 100)
                ->unique();

            $table->string('email', 150)
                ->unique();

            $table->string('password', 255);

            $table->foreignId('role_id')
                ->constrained('tblRoles', 'role_id');

            $table->boolean('status')
                ->default(true);

            $table->timestamp('last_login')
                ->nullable();

            $table->timestamp('created_at')
                ->useCurrent();

            $table->timestamp('updated_at')
                ->useCurrent();

            $table->char('reset_token', 64)
                ->nullable();

            $table->timestamp('reset_expiry')
                ->nullable();

            $table->string('public_id', 20)
                ->unique()
                ->nullable();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tblUsers');
    }
};