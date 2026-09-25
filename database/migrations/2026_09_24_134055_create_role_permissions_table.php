<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('tblRolePermissions', function (Blueprint $table) {
            $table->foreignId('role_id')
                ->constrained('tblRoles', 'role_id')
                ->cascadeOnDelete();

            $table->foreignId('permission_id')
                ->constrained('tblPermissions', 'permission_id')
                ->cascadeOnDelete();

            $table->primary([
                'role_id',
                'permission_id',
            ]);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tblRolePermissions');
    }
};