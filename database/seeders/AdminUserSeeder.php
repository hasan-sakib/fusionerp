<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\Tenant;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class AdminUserSeeder extends Seeder
{
    public function run(): void
    {
        // Create or find demo tenant
        $tenant = Tenant::firstOrCreate(
            ['slug' => 'demo'],
            [
                'name'   => 'FusionERP Demo',
                'status' => 'active',
                'plan'   => 'enterprise',
            ]
        );

        // Bind tenant so BelongsToTenant auto-sets tenant_id
        app()->instance('tenant', $tenant);

        $admin = User::withoutGlobalScopes()->updateOrCreate(
            ['email' => 'admin@fusionerp.com'],
            [
                'tenant_id'         => $tenant->id,
                'name'              => 'System Administrator',
                'phone'             => '+1-000-000-0000',
                'department'        => 'IT',
                'position'          => 'System Administrator',
                'status'            => 'active',
                'email_verified_at' => now(),
                'password'          => Hash::make('Admin@123456'),
            ]
        );
        $admin->assignRole('admin');

        $manager = User::withoutGlobalScopes()->updateOrCreate(
            ['email' => 'manager@fusionerp.com'],
            [
                'tenant_id'         => $tenant->id,
                'name'              => 'Jane Manager',
                'phone'             => '+1-000-000-0001',
                'department'        => 'Operations',
                'position'          => 'Operations Manager',
                'status'            => 'active',
                'email_verified_at' => now(),
                'password'          => Hash::make('Manager@123456'),
            ]
        );
        $manager->assignRole('manager');

        $employee = User::withoutGlobalScopes()->updateOrCreate(
            ['email' => 'employee@fusionerp.com'],
            [
                'tenant_id'         => $tenant->id,
                'name'              => 'John Employee',
                'phone'             => '+1-000-000-0002',
                'department'        => 'Sales',
                'position'          => 'Sales Associate',
                'status'            => 'active',
                'email_verified_at' => now(),
                'password'          => Hash::make('Employee@123456'),
            ]
        );
        $employee->assignRole('employee');
    }
}
