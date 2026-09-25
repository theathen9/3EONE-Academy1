<?php

namespace Database\Seeders;

use App\Models\Role;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $adminRole = Role::firstOrCreate(
            ['role_name' => 'Admin'],
            [
                'description' => 'System administrator',
            ]
        );

        User::updateOrCreate(
            ['username' => 'admin1'],
            [
                'reference_id' => 1,
                'reference_type' => 'Employee',
                'email' => 'admin@example.com',
                'password' => Hash::make('Admin@12345'),
                'role_id' => $adminRole->role_id,
                'status' => true,
                'public_id' => 'USR-000001',
            ]
        );
    }
}