<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

class RolesAndPermissionsSeeder extends Seeder
{
    public function run(): void
    {
        // Clear cached permissions
        app()[PermissionRegistrar::class]->forgetCachedPermissions();

        // Define all permissions grouped by module
        $permissions = [
            // User Management
            'users.view',
            'users.create',
            'users.edit',
            'users.delete',
            'users.impersonate',

            // Role & Permission Management
            'roles.view',
            'roles.create',
            'roles.edit',
            'roles.delete',

            // Product Management
            'products.view',
            'products.create',
            'products.edit',
            'products.delete',

            // Category Management
            'categories.view',
            'categories.create',
            'categories.edit',
            'categories.delete',

            // Inventory Management
            'inventory.view',
            'inventory.adjust',
            'inventory.transfer',
            'inventory.export',

            // Order Management
            'orders.view',
            'orders.create',
            'orders.edit',
            'orders.delete',
            'orders.process',
            'orders.cancel',

            // Reports
            'reports.view',
            'reports.export',

            // Settings
            'settings.view',
            'settings.edit',
        ];

        foreach ($permissions as $permission) {
            Permission::firstOrCreate(['name' => $permission]);
        }

        // ─── Admin ──────────────────────────────────────────────────────────────
        $admin = Role::firstOrCreate(['name' => 'admin']);
        $admin->syncPermissions(Permission::all());

        // ─── Manager ────────────────────────────────────────────────────────────
        $manager = Role::firstOrCreate(['name' => 'manager']);
        $manager->syncPermissions([
            'products.view', 'products.create', 'products.edit',
            'categories.view', 'categories.create', 'categories.edit',
            'inventory.view', 'inventory.adjust', 'inventory.transfer', 'inventory.export',
            'orders.view', 'orders.create', 'orders.edit', 'orders.process', 'orders.cancel',
            'reports.view', 'reports.export',
            'users.view',
        ]);

        // ─── Employee ────────────────────────────────────────────────────────────
        $employee = Role::firstOrCreate(['name' => 'employee']);
        $employee->syncPermissions([
            'products.view',
            'categories.view',
            'inventory.view',
            'orders.view', 'orders.create',
            'reports.view',
        ]);
    }
}
