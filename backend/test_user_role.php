<?php

require 'vendor/autoload.php';
$app = require 'bootstrap/app.php';
$app->boot();

echo "Test de la base de données...\n";

try {
    $user = App\Models\User::first();
    if ($user) {
        echo "Utilisateur trouvé: " . $user->email . "\n";
        echo "Attributs disponibles: " . implode(', ', array_keys($user->getAttributes())) . "\n";
        
        // Test d'accès direct à l'attribut role
        if (array_key_exists('role', $user->getAttributes())) {
            echo "L'attribut 'role' existe dans les attributs\n";
            echo "Role: " . $user->role . "\n";
        } else {
            echo "L'attribut 'role' n'existe PAS dans les attributs!\n";
            echo "Il faut vérifier la migration et la base de données\n";
        }
    } else {
        echo "Aucun utilisateur trouvé\n";
    }
} catch (Exception $e) {
    echo "Erreur: " . $e->getMessage() . "\n";
    echo "Trace: " . $e->getTraceAsString() . "\n";
}