<?php

require_once 'backend/vendor/autoload.php';

echo "=== Débogage validation CFE ===\n\n";

// Test de différents scénarios CFE
$scenarios = [
    "Scénario 1: has_cfe=false (pas de CFE)" => [
        'has_cfe' => false,
        'cfe_number' => '',
        'cfe_expiry_date' => ''
    ],
    "Scénario 2: has_cfe=true avec date valide" => [
        'has_cfe' => true,
        'cfe_number' => 'CFE123456',
        'cfe_expiry_date' => '2025-12-31'
    ],
    "Scénario 3: has_cfe=true SANS date (ERREUR ATTENDUE)" => [
        'has_cfe' => true,
        'cfe_number' => 'CFE123456',
        'cfe_expiry_date' => ''
    ],
    "Scénario 4: has_cfe=true avec date passée (ERREUR ATTENDUE)" => [
        'has_cfe' => true,
        'cfe_number' => 'CFE123456',
        'cfe_expiry_date' => '2023-01-01'
    ],
    "Scénario 5: has_cfe='1' (string true)" => [
        'has_cfe' => '1',
        'cfe_number' => 'CFE123456',
        'cfe_expiry_date' => '2025-12-31'
    ],
    "Scénario 6: has_cfe=null" => [
        'has_cfe' => null,
        'cfe_number' => '',
        'cfe_expiry_date' => ''
    ]
];

use Illuminate\Support\Facades\Validator;

foreach ($scenarios as $name => $data) {
    echo "--- $name ---\n";
    echo "Données: " . json_encode($data) . "\n";
    
    // Créer les règles de validation
    $rules = [
        'has_cfe' => 'nullable|boolean',
        'cfe_number' => 'required_if:has_cfe,true|nullable|string|max:50',
        'cfe_expiry_date' => 'required_if:has_cfe,true|nullable|date|after:today'
    ];
    
    $validator = Validator::make($data, $rules);
    
    if ($validator->fails()) {
        echo "❌ ERREURS: " . json_encode($validator->errors()->toArray()) . "\n";
    } else {
        echo "✅ VALIDATION OK\n";
    }
    echo "\n";
}

echo "=== Analyse des règles ===\n";
echo "required_if:has_cfe,true signifie:\n";
echo "- Si has_cfe == true, alors le champ est obligatoire\n";
echo "- Si has_cfe != true, alors le champ est optionnel\n\n";

echo "Valeurs considérées comme 'true' par Laravel:\n";
echo "- true (boolean)\n";
echo "- 'true' (string)\n";
echo "- '1' (string)\n";
echo "- 1 (integer)\n";
echo "- 'yes'\n";
echo "- 'on'\n";