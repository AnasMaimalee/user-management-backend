<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class RolePermissionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $permissions = [
            'employee.view',
            'employee.create',
            'employee.update',
            'employee.delete',
            'employee.update_status',
        ];

        foreach ($permissions as $permission) {
            Permission::create(['name' => $permission]);
        }

        $superAdmin = Role::firstOrCreate(['name' => 'super_admin']);
        $admin = Role::firstOrCreate(['name' => 'admin']);
        $hr = Role::firstOrCreate(['name' => 'hr']);
        $staff = Role::firstOrCreate(['name' => 'staff']);

        $superAdmin->givePermissionTo(Permission::all());

        $admin->givePermissionTo([
            'employee.view',
            'employee.create',
            'employee.update',
            'employee.update_status',
        ]);

        $hr->givePermissionTo([
            'employee.view',
            'employee.update_status',
        ]);

        $staff->givePermissionTo([
            'employee.view',
        ]);
    }
}
