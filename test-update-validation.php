<?php

// Test des règles de validation pour la mise à jour
require_once 'backend/vendor/autoload.php';

use App\Http\Requests\StoreMerchantApplicationRequest;

echo "=== TEST DES RÈGLES DE VALIDATION POUR MISE À JOUR ===\n\n";

// Test de la méthode statique rulesFor
try {
    $rules = StoreMerchantApplicationRequest::rulesFor(123);
    echo "✅ La méthode rulesFor() fonctionne correctement\n";
    
    // Vérifier quelques règles clés
    $phoneRule = $rules['phone'] ?? 'NOT_FOUND';
    $emailRule = $rules['email'] ?? 'NOT_FOUND';
    $idNumberRule = $rules['id_number'] ?? 'NOT_FOUND';
    $cfeNumberRule = $rules['cfe_number'] ?? 'NOT_FOUND';
    $nifNumberRule = $rules['nif_number'] ?? 'NOT_FOUND';
    
    echo "\nRègles de validation générées pour l'application ID 123:\n";
    echo "- phone: $phoneRule\n";
    echo "- email: $emailRule\n";
    echo "- id_number: $idNumberRule\n";
    echo "- cfe_number: $cfeNumberRule\n";
    echo "- nif_number: $nifNumberRule\n";
    
    // Vérifier que les règles d'unicité incluent bien l'exception pour l'ID 123
    $phoneHasException = strpos($phoneRule, ',123') !== false;
    $emailHasException = strpos($emailRule, ',123') !== false;
    $idNumberHasException = strpos($idNumberRule, ',123') !== false;
    
    echo "\nVérification des exceptions d'unicité:\n";
    echo "- phone a exception ID 123: " . ($phoneHasException ? '✅ OUI' : '❌ NON') . "\n";
    echo "- email a exception ID 123: " . ($emailHasException ? '✅ OUI' : '❌ NON') . "\n";
    echo "- id_number a exception ID 123: " . ($idNumberHasException ? '✅ OUI' : '❌ NON') . "\n";
    
    // Vérifier que CFE et NIF n'ont pas de règles d'unicité
    $cfeHasUnique = strpos($cfeNumberRule, 'unique') !== false;
    $nifHasUnique = strpos($nifNumberRule, 'unique') !== false;
    
    echo "\nVérification absence d'unicité CFE/NIF:\n";
    echo "- cfe_number a unique: " . ($cfeHasUnique ? '❌ OUI (PROBLÈME)' : '✅ NON (CORRECT)') . "\n";
    echo "- nif_number a unique: " . ($nifHasUnique ? '❌ OUI (PROBLÈME)' : '✅ NON (CORRECT)') . "\n";
    
    // Test avec ID null (création)
    echo "\n" . str_repeat('-', 50) . "\n";
    $rulesCreate = StoreMerchantApplicationRequest::rulesFor(null);
    $phoneRuleCreate = $rulesCreate['phone'] ?? 'NOT_FOUND';
    $emailRuleCreate = $rulesCreate['email'] ?? 'NOT_FOUND';
    
    echo "Règles pour création (ID null):\n";
    echo "- phone: $phoneRuleCreate\n";
    echo "- email: $emailRuleCreate\n";
    
    $phoneCreateNoException = strpos($phoneRuleCreate, ',') === false || $phoneRuleCreate === 'NOT_FOUND';
    $emailCreateNoException = strpos($emailRuleCreate, ',') === false || $emailRuleCreate === 'NOT_FOUND';
    
    echo "\nVérification pas d'exception pour création:\n";
    echo "- phone sans exception: " . ($phoneCreateNoException ? '✅ CORRECT' : '❌ PROBLÈME') . "\n";
    echo "- email sans exception: " . ($emailCreateNoException ? '✅ CORRECT' : '❌ PROBLÈME') . "\n";
    
} catch (Exception $e) {
    echo "❌ ERREUR: " . $e->getMessage() . "\n";
    echo "Stack trace:\n" . $e->getTraceAsString() . "\n";
}

echo "\n=== ANALYSE DES PROBLÈMES ===\n";
echo "1. Règles d'unicité en modification:\n";
echo "   - phone, email, id_number doivent inclure l'exception pour l'ID de l'application\n";
echo "   - Format attendu: 'unique:table,column,exception_id'\n\n";

echo "2. Champs CFE/NIF:\n";
echo "   - cfe_number et nif_number NE DOIVENT PAS avoir de règle unique\n";
echo "   - Ces champs peuvent être dupliqués entre applications\n\n";

echo "3. Méthode rulesFor():\n";
echo "   - Doit être utilisée par fullUpdate() dans le contrôleur\n";
echo "   - Doit correctement gérer l'exception d'unicité pour les modifications\n";

?>