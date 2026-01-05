<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        // Admin User
        User::create([
            'name' => 'Admin User',
            'email' => 'admin@esigning.com',
            'password' => Hash::make('password'),
            'job_title' => 'System Administrator',
            'department' => 'IT Department',
            'company_name' => 'e-Signing Solutions Inc.',
            'email_verified_at' => now(),
        ]);

        // Test User 1
        User::create([
            'name' => 'John Doe',
            'email' => 'john@esigning.com',
            'password' => Hash::make('password'),
            'job_title' => 'Project Manager',
            'department' => 'Operations',
            'company_name' => 'e-Signing Solutions Inc.',
            'email_verified_at' => now(),
        ]);

        // Test User 2
        User::create([
            'name' => 'Jane Smith',
            'email' => 'jane@esigning.com',
            'password' => Hash::make('password'),
            'job_title' => 'Legal Counsel',
            'department' => 'Legal',
            'company_name' => 'e-Signing Solutions Inc.',
            'email_verified_at' => now(),
        ]);

        // Test User 3
        User::create([
            'name' => 'Michael Johnson',
            'email' => 'michael@esigning.com',
            'password' => Hash::make('password'),
            'job_title' => 'Finance Director',
            'department' => 'Finance',
            'company_name' => 'e-Signing Solutions Inc.',
            'email_verified_at' => now(),
        ]);

        // Test User 4
        User::create([
            'name' => 'Sarah Williams',
            'email' => 'sarah@esigning.com',
            'password' => Hash::make('password'),
            'job_title' => 'HR Manager',
            'department' => 'Human Resources',
            'company_name' => 'e-Signing Solutions Inc.',
            'email_verified_at' => now(),
        ]);
    }
}
