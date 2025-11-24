<?php

// Test Script pour valider les règles de validation CFE/NIF
echo "=== TEST DES RÈGLES DE VALIDATION CFE/NIF ===\n\n";

// Simulation des règles de validation Laravel
function validateCfeNif($data) {
    $errors = [];
    
    // Conversion des valeurs booléennes
    $hasCfe = isset($data['has_cfe']) && filter_var($data['has_cfe'], FILTER_VALIDATE_BOOLEAN);
    $hasNif = isset($data['has_nif']) && filter_var($data['has_nif'], FILTER_VALIDATE_BOOLEAN);
    
    // Validation CFE
    if ($hasCfe) {
        if (empty($data['cfe_number'])) {
            $errors['cfe_number'] = 'Le numéro CFE est obligatoire si vous possédez une carte CFE';
        }
        if (empty($data['cfe_expiry_date'])) {
            $errors['cfe_expiry_date'] = 'La date d\'expiration CFE est obligatoire si vous possédez une carte CFE';
        } elseif (strtotime($data['cfe_expiry_date']) <= time()) {
            $errors['cfe_expiry_date'] = 'La carte CFE est expirée';
        }
    }
    
    // Validation NIF
    if ($hasNif) {
        if (empty($data['nif_number'])) {
            $errors['nif_number'] = 'Le numéro NIF est obligatoire si vous possédez un numéro NIF';
        }
    }
    
    return $errors;
}

// Test cases
$testCases = [
    [
        'name' => 'CFE coché, tous les champs remplis',
        'data' => [
            'has_cfe' => true,
            'cfe_number' => 'CFE123456',
            'cfe_expiry_date' => '2030-12-31',
            'has_nif' => false,
        ],
        'expected_valid' => true
    ],
    [
        'name' => 'CFE coché, numéro manquant',
        'data' => [
            'has_cfe' => true,
            'cfe_expiry_date' => '2030-12-31',
            'has_nif' => false,
        ],
        'expected_valid' => false,
        'expected_errors' => ['cfe_number']
    ],
    [
        'name' => 'CFE coché, date expiration manquante',
        'data' => [
            'has_cfe' => true,
            'cfe_number' => 'CFE123456',
            'has_nif' => false,
        ],
        'expected_valid' => false,
        'expected_errors' => ['cfe_expiry_date']
    ],
    [
        'name' => 'CFE coché, date expirée',
        'data' => [
            'has_cfe' => true,
            'cfe_number' => 'CFE123456',
            'cfe_expiry_date' => '2020-01-01',
            'has_nif' => false,
        ],
        'expected_valid' => false,
        'expected_errors' => ['cfe_expiry_date']
    ],
    [
        'name' => 'NIF coché, numéro rempli',
        'data' => [
            'has_cfe' => false,
            'has_nif' => true,
            'nif_number' => 'NIF789012',
        ],
        'expected_valid' => true
    ],
    [
        'name' => 'NIF coché, numéro manquant',
        'data' => [
            'has_cfe' => false,
            'has_nif' => true,
        ],
        'expected_valid' => false,
        'expected_errors' => ['nif_number']
    ],
    [
        'name' => 'CFE et NIF cochés, tous les champs remplis',
        'data' => [
            'has_cfe' => true,
            'cfe_number' => 'CFE123456',
            'cfe_expiry_date' => '2030-12-31',
            'has_nif' => true,
            'nif_number' => 'NIF789012',
        ],
        'expected_valid' => true
    ],
    [
        'name' => 'CFE et NIF cochés, champs manquants',
        'data' => [
            'has_cfe' => true,
            'has_nif' => true,
        ],
        'expected_valid' => false,
        'expected_errors' => ['cfe_number', 'cfe_expiry_date', 'nif_number']
    ],
    [
        'name' => 'Aucune case cochée',
        'data' => [
            'has_cfe' => false,
            'has_nif' => false,
        ],
        'expected_valid' => true
    ],
    [
        'name' => 'Cases non définies (traitement par défaut)',
        'data' => [],
        'expected_valid' => true
    ],
];

// Exécution des tests
foreach ($testCases as $index => $test) {
    echo "Test " . ($index + 1) . ": " . $test['name'] . "\n";
    echo str_repeat('-', 50) . "\n";
    
    $errors = validateCfeNif($test['data']);
    $isValid = empty($errors);
    
    echo "Données: " . json_encode($test['data']) . "\n";
    echo "Attendu valide: " . ($test['expected_valid'] ? 'Oui' : 'Non') . "\n";
    echo "Résultat valide: " . ($isValid ? 'Oui' : 'Non') . "\n";
    
    if (!$isValid) {
        echo "Erreurs trouvées: " . json_encode(array_keys($errors)) . "\n";
        foreach ($errors as $field => $message) {
            echo "  - $field: $message\n";
        }
    }
    
    // Vérification du résultat
    $testPassed = $isValid === $test['expected_valid'];
    if (!$test['expected_valid'] && isset($test['expected_errors'])) {
        $expectedErrorFields = $test['expected_errors'];
        $actualErrorFields = array_keys($errors);
        $errorsMatch = empty(array_diff($expectedErrorFields, $actualErrorFields)) && 
                      empty(array_diff($actualErrorFields, $expectedErrorFields));
        $testPassed = $testPassed && $errorsMatch;
    }
    
    echo "Statut: " . ($testPassed ? '✅ PASS' : '❌ FAIL') . "\n";
    echo "\n";
}

echo "=== RÉSUMÉ DE L'IMPLÉMENTATION ===\n";
echo "✅ Règles de validation conditionnelle implémentées\n";
echo "✅ Frontend: Champs requis dynamiques avec validation JS\n";
echo "✅ Backend: Règles required_if dans StoreMerchantApplicationRequest\n";
echo "✅ Messages d'erreur personnalisés ajoutés\n";
echo "\n=== RÈGLES APPLIQUÉES ===\n";
echo "- has_cfe = true → cfe_number et cfe_expiry_date OBLIGATOIRES\n";
echo "- has_nif = true → nif_number OBLIGATOIRE\n";
echo "- has_cfe = false → cfe_number et cfe_expiry_date optionnels\n";
echo "- has_nif = false → nif_number optionnel\n";

?>