<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class AdminUserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Create default admin user
        $admin = User::create([
            'name' => 'Admin User',
            'email' => 'admin@sportgear.com',
            'password' => Hash::make('password'),
            'email_verified_at' => now(),
        ]);

        // Assign admin role
        $admin->assignRole('admin');

        $this->command->info('✓ Admin user created successfully!');
        $this->command->info('  Email: admin@sportgear.com');
        $this->command->info('  Password: password');
        $this->command->warn('  ⚠ Please change the password after first login!');
    }
}
