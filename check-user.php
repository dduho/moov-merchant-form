<?php
// Script temporaire pour vérifier l'utilisateur osabi
require __DIR__ . '/backend/vendor/autoload.php';

$app = require_once __DIR__ . '/backend/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\User;

echo "=== Vérification de l'utilisateur osabi ===\n\n";

// Chercher par username
$userByUsername = User::where('username', 'osabi')->first();
if ($userByUsername) {
    echo "✓ Trouvé par username:\n";
    echo "  ID: {$userByUsername->id}\n";
    echo "  Username: {$userByUsername->username}\n";
    echo "  Email: {$userByUsername->email}\n";
    echo "  Active: " . ($userByUsername->is_active ? 'Oui' : 'Non') . "\n";
    echo "  Roles: " . $userByUsername->roles->pluck('slug')->implode(', ') . "\n";
    echo "  Password hash: " . substr($userByUsername->password, 0, 20) . "...\n\n";
    
    // Tester le mot de passe
    if (Hash::check('password', $userByUsername->password)) {
        echo "✓ Le mot de passe 'password' est correct\n";
    } else {
        echo "✗ Le mot de passe 'password' est incorrect\n";
    }
} else {
    echo "✗ Utilisateur 'osabi' introuvable\n\n";
}

// Lister tous les utilisateurs
echo "\n=== Liste de tous les utilisateurs ===\n";
$users = User::all();
foreach ($users as $user) {
    echo "- {$user->username} ({$user->email}) - Active: " . ($user->is_active ? 'Oui' : 'Non') . "\n";
}
