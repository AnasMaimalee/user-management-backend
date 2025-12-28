<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        // Admin User
        $user = User::updateOrCreate(
            ['email' => 'admin@test.com'],
            [
                'id' => (string) Str::uuid(),  // ← Force UUID here
                'name' => 'Admin',
                'password' => Hash::make('password'),
            ]
        );
        $user->assignRole('super_admin');

        // HR Manager
        $hr = User::updateOrCreate(
            ['email' => 'hr@test.com'],
            [
                'id' => (string) Str::uuid(),  // ← Force UUID
                'name' => 'HR Manager',
                'password' => Hash::make('password'),
            ]
        );
        $hr->assignRole('hr');

        // Staff User
        $staff = User::updateOrCreate(
            ['email' => 'staff@test.com'],
            [
                'id' => (string) Str::uuid(),  // ← Force UUID
                'name' => 'Staff User',
                'password' => Hash::make('password'),
            ]
        );
        $staff->assignRole('staff');
    }
}
