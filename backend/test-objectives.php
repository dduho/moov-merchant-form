<?php

require_once __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

echo "=== Test d'application des objectifs globaux ===\n\n";

// 1. Créer un utilisateur commercial de test
echo "1. Création d'un utilisateur commercial de test...\n";
$user = \App\Models\User::create([
    'first_name' => 'Test',
    'last_name' => 'Commercial',
    'email' => 'test.commercial@example.com',
    'phone' => '0123456789',
    'username' => 'testcommercial',
    'password' => \Illuminate\Support\Facades\Hash::make('password123'),
    'is_active' => true,
    'must_change_password' => true
]);

// Assigner le rôle commercial
$role = \App\Models\Role::where('slug', 'commercial')->first();
$user->roles()->attach($role->id);

echo "✓ Utilisateur créé: {$user->username} (ID: {$user->id})\n";

// 2. Vérifier les objectifs globaux disponibles
echo "\n2. Objectifs globaux disponibles:\n";
$globalObjectives = \App\Models\UserObjective::whereNull('user_id')->active()->get();
echo "Nombre: {$globalObjectives->count()}\n";

foreach($globalObjectives as $obj) {
    echo "  - ID {$obj->id}: {$obj->monthly_target} par mois ({$obj->target_year}-{$obj->target_month})\n";
}

// 3. Appliquer les objectifs globaux
echo "\n3. Application des objectifs globaux...\n";
$user->applyGlobalObjectives();

// 4. Vérifier les objectifs de l'utilisateur
echo "\n4. Objectifs de l'utilisateur après application:\n";
$userObjectives = $user->objectives()->get();
echo "Nombre: {$userObjectives->count()}\n";

foreach($userObjectives as $obj) {
    echo "  - ID {$obj->id}: {$obj->monthly_target} par mois ({$obj->target_year}-{$obj->target_month})\n";
    echo "    Global: " . ($obj->user_id ? 'Non' : 'Oui') . "\n";
}

// 5. Nettoyer
echo "\n5. Nettoyage...\n";
$user->delete();
echo "✓ Utilisateur de test supprimé\n";

echo "\n=== Test terminé ===\n";