<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class AdminUserSeeder extends Seeder
{
    public function run(): void
    {
        $admin = User::firstOrCreate(
            ['email' => 'admin@fusionerp.com'],
            [
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

        $manager = User::firstOrCreate(
            ['email' => 'manager@fusionerp.com'],
            [
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

        $employee = User::firstOrCreate(
            ['email' => 'employee@fusionerp.com'],
            [
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
