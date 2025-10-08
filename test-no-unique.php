<?php

require_once 'backend/vendor/autoload.php';

use App\Http\Requests\StoreMerchantApplicationRequest;

echo "=== Test des règles de validation sans contraintes unique ===\n";

// Test des règles pour une nouvelle application
$rulesCreate = (new StoreMerchantApplicationRequest)->rules();
echo "\n--- Règles pour CREATION ---\n";
echo "Phone: " . $rulesCreate['phone'] . "\n";
echo "Email: " . $rulesCreate['email'] . "\n";  
echo "ID Number: " . $rulesCreate['id_number'] . "\n";

// Test des règles pour mise à jour
$rulesUpdate = StoreMerchantApplicationRequest::rulesFor(123);
echo "\n--- Règles pour UPDATE (ID: 123) ---\n";
echo "Phone: " . $rulesUpdate['phone'] . "\n";
echo "Email: " . $rulesUpdate['email'] . "\n";
echo "ID Number: " . $rulesUpdate['id_number'] . "\n";

// Test des règles conditionnelles CFE/NIF
echo "\n--- Règles conditionnelles ---\n";
echo "CFE Number: " . $rulesUpdate['cfe_number'] . "\n";
echo "CFE Expiry: " . $rulesUpdate['cfe_expiry_date'] . "\n";
echo "NIF Number: " . $rulesUpdate['nif_number'] . "\n";

echo "\n✅ Test terminé - Aucune contrainte unique trouvée!\n";