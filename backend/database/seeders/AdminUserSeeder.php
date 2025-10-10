<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Role;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class AdminUserSeeder extends Seeder
{
    public function run(): void
    {
        // Créer le rôle admin s'il n'existe pas
        $adminRole = Role::firstOrCreate(['name' => 'admin']);
        
        // Créer l'utilisateur admin principal
        $adminUser = User::firstOrCreate(
            ['email' => 'admin@moovmoney.com'],
            [
                'first_name' => 'Admin',
                'last_name' => 'Flooz',
                'phone' => '+22890000000',
                'username' => 'floozadmin',
                'password' => Hash::make('1210'),
                'is_active' => true,
                'password_changed_at' => now() // Éviter le changement de mot de passe forcé
            ]
        );
        
        // Attacher le rôle admin
        $adminUser->roles()->syncWithoutDetaching([$adminRole->id]);
        
        // Créer un utilisateur de test avec email et mot de passe simples
        $testUser = User::firstOrCreate(
            ['email' => 'admin@test.com'],
            [
                'first_name' => 'Test',
                'last_name' => 'Admin',
                'phone' => '+22899999999',
                'username' => 'testadmin',
                'password' => Hash::make('password'),
                'is_active' => true,
                'password_changed_at' => now()
            ]
        );
        
        // Attacher le rôle admin à l'utilisateur de test aussi
        $testUser->roles()->syncWithoutDetaching([$adminRole->id]);
        
        $this->command->info('Utilisateurs admin créés:');
        $this->command->info('1. admin@moovmoney.com / 1210');
        $this->command->info('2. admin@test.com / password');
    }
}