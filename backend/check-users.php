<?php

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\User;
use App\Models\Role;

echo "=== Vérification des utilisateurs ===\n\n";

$users = User::with('roles')->get();

if ($users->count() === 0) {
    echo "❌ Aucun utilisateur trouvé\n\n";

    echo "🔄 Création des utilisateurs par défaut...\n";

    // Créer les rôles
    $adminRole = Role::firstOrCreate(['slug' => 'admin'], ['name' => 'Administrateur']);
    $commercialRole = Role::firstOrCreate(['slug' => 'commercial'], ['name' => 'Commercial']);

    // Créer l'admin principal
    $admin = User::create([
        'first_name' => 'Admin',
        'last_name' => 'Flooz',
        'email' => 'admin@flooz.com',
        'phone' => '+22990000000',
        'username' => 'admin',
        'password' => bcrypt('password'),
        'is_active' => true,
        'password_changed_at' => now()
    ]);
    $admin->roles()->attach($adminRole->id);
    echo "✅ Utilisateur 'admin' créé\n";

    // Créer floozadmin
    $floozadmin = User::create([
        'first_name' => 'Admin',
        'last_name' => 'Flooz',
        'email' => 'admin@moovmoney.com',
        'phone' => '+22890000000',
        'username' => 'floozadmin',
        'password' => bcrypt('1210'),
        'is_active' => true,
        'password_changed_at' => now()
    ]);
    $floozadmin->roles()->attach($adminRole->id);
    echo "✅ Utilisateur 'floozadmin' créé\n";

    // Créer testadmin
    $testadmin = User::create([
        'first_name' => 'Test',
        'last_name' => 'Admin',
        'email' => 'admin@test.com',
        'phone' => '+22899999999',
        'username' => 'testadmin',
        'password' => bcrypt('password'),
        'is_active' => true,
        'password_changed_at' => now()
    ]);
    $testadmin->roles()->attach($adminRole->id);
    echo "✅ Utilisateur 'testadmin' créé\n";

    // Créer commercial
    $commercial = User::create([
        'first_name' => 'John',
        'last_name' => 'Doe',
        'email' => 'commercial@flooz.com',
        'phone' => '+22891234567',
        'username' => 'commercial',
        'password' => bcrypt('password'),
        'is_active' => true,
        'password_changed_at' => now()
    ]);
    $commercial->roles()->attach($commercialRole->id);
    echo "✅ Utilisateur 'commercial' créé\n";

    echo "\n✅ Tous les utilisateurs ont été créés avec succès!\n\n";

    $users = User::with('roles')->get();
}

echo "📊 Utilisateurs existants (" . $users->count() . "):\n\n";

foreach ($users as $user) {
    $roles = $user->roles->pluck('name')->join(', ');
    echo "👤 {$user->username} ({$user->email})\n";
    echo "   Rôles: {$roles}\n";
    echo "   Actif: " . ($user->is_active ? 'Oui' : 'Non') . "\n\n";
}

echo "🔐 Pour vous connecter, utilisez l'un de ces comptes:\n";
echo "   - username: admin, password: password\n";
echo "   - username: floozadmin, password: 1210\n";
echo "   - username: testadmin, password: password\n";
echo "   - username: commercial, password: password\n";
