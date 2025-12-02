<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Role;

class RoleSeeder extends Seeder
{
    public function run()
    {
        Role::firstOrCreate(
            ['slug' => 'commercial'],
            ['name' => 'Commercial']
        );

        Role::firstOrCreate(
            ['slug' => 'admin'],
            ['name' => 'Admin']
        );

        Role::firstOrCreate(
            ['slug' => 'personnel'],
            ['name' => 'Personnel']
        );

        Role::firstOrCreate(
            ['slug' => 'client'],
            ['name' => 'Client']
        );

        Role::firstOrCreate(
            ['slug' => 'dealer'],
            ['name' => 'Dealer']
        );

        Role::firstOrCreate(
            ['slug' => 'moov_staff'],
            ['name' => 'Moov Staff']
        );
    }
}