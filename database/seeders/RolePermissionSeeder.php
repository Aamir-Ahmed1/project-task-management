<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

class RolePermissionSeeder extends Seeder
{
    public function run(): void
    {
        // Reset cached roles and permissions
        app()[PermissionRegistrar::class]->forgetCachedPermissions();

        // Permissions
        $permissions = [
            'create-projects', 'edit-projects', 'delete-projects', 'view-projects',
            'create-tasks', 'edit-tasks', 'delete-tasks', 'view-tasks',
            'assign-tasks', 'manage-users', 'view-reports', 'view-audit-logs',
            'submit-work-logs', 'reply-work-logs',
        ];

        foreach ($permissions as $permission) {
            Permission::firstOrCreate(['name' => $permission]);
        }

        // Admin - all permissions
        $adminRole = Role::firstOrCreate(['name' => 'admin']);
        $adminRole->givePermissionTo(Permission::all());

        // Project Manager
        $pmRole = Role::firstOrCreate(['name' => 'project-manager']);
        $pmRole->givePermissionTo([
            'create-tasks', 'edit-tasks', 'view-tasks', 'assign-tasks',
            'view-projects', 'view-reports', 'reply-work-logs',
        ]);

        // Employee
        $employeeRole = Role::firstOrCreate(['name' => 'employee']);
        $employeeRole->givePermissionTo(['view-tasks', 'submit-work-logs']);

        // Users
        $admin = User::updateOrCreate(
            ['email' => 'admin@example.com'],
            ['name' => 'Admin User', 'password' => Hash::make('password'), 'is_active' => true]
        );
        $admin->assignRole('admin');

        $manager = User::updateOrCreate(
            ['email' => 'manager@example.com'],
            ['name' => 'Project Manager', 'password' => Hash::make('password'), 'is_active' => true]
        );
        $manager->assignRole('project-manager');

        $employee = User::updateOrCreate(
            ['email' => 'employee@example.com'],
            ['name' => 'Employee User', 'password' => Hash::make('password'), 'is_active' => true]
        );
        $employee->assignRole('employee');
    }
}
