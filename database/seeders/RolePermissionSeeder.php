<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class RolePermissionSeeder extends Seeder
{
    public function run(): void
    {
        // Define permissions
        $permissions = [
            'view departments', 'create departments', 'update departments', 'delete departments', 'update department status',
            'view employees', 'create employees', 'update employees', 'delete employees', 'update employee status',
        ];

        // Create permissions
        foreach ($permissions as $permission) {
            Permission::firstOrCreate(['name' => $permission, 'guard_name' => 'web']);
        }

        // Create roles
        $superAdmin = Role::firstOrCreate(['name' => 'super_admin', 'guard_name' => 'web']);
        $hr = Role::firstOrCreate(['name' => 'hr', 'guard_name' => 'web']);
        $staff = Role::firstOrCreate(['name' => 'staff', 'guard_name' => 'web']);

        // Assign permissions
        $superAdmin->givePermissionTo(Permission::all());

        $hr->givePermissionTo([
            'view departments', 'create departments', 'update departments', 'update department status',
            'view employees', 'create employees', 'update employees', 'update employee status',
        ]);

        $staff->givePermissionTo([
            'view departments', 'view employees',
        ]);
    }
}
