<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Role;
use Illuminate\Database\Seeder;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Create default admin
        $admin = User::create([
            'first_name' => 'Admin',
            'last_name' => 'Flooz',
            'email' => 'admin@flooz.com',
            'phone' => '+22990000000',
            'username' => 'admin',
            'password' => bcrypt('password'),
            'is_active' => true
        ]);

        // Create a test commercial user
        $commercial = User::create([
            'first_name' => 'John',
            'last_name' => 'Doe',
            'email' => 'commercial@flooz.com',
            'phone' => '+22991234567',
            'username' => 'commercial',
            'password' => bcrypt('password'),
            'is_active' => true
        ]);

        // Assign roles
        $adminRole = Role::where('slug', 'admin')->first();
        $commercialRole = Role::where('slug', 'commercial')->first();

        $admin->roles()->attach($adminRole->id);
        $commercial->roles()->attach($commercialRole->id);
    }
}