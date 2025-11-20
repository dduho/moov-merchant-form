<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Role;

class RoleSeeder extends Seeder
{
    public function run()
    {
        Role::create([
            'name' => 'Commercial',
            'slug' => 'commercial',
        ]);

        Role::create([
            'name' => 'Admin',
            'slug' => 'admin',
        ]);

        Role::create([
            'name' => 'Personnel',
            'slug' => 'personnel',
        ]);

        Role::create([
            'name' => 'Client',
            'slug' => 'client',
        ]);
    }
}