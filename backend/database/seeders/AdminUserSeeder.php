<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class AdminUserSeeder extends Seeder
{
    public function run(): void
    {
        User::create([
            'first_name' => 'Admin',
            'last_name' => 'Flooz',
            'email' => 'admin@moovmoney.com',
            'phone' => '+22890000000',
            'username' => 'floozadmin',
            'password' => Hash::make('1210'),
            'role' => 'admin',
            'is_active' => true
        ]);
    }
}