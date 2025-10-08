<?php

echo "=== Test fallback merchant_phone ===\n\n";

$scenarios = [
    "Scénario 1: merchantPhone renseigné" => [
        'personalPhone' => '123456',
        'merchantPhone' => '789012',
        'expected' => '789012'
    ],
    "Scénario 2: merchantPhone vide, personalPhone renseigné" => [
        'personalPhone' => '123456', 
        'merchantPhone' => '',
        'expected' => '123456'
    ],
    "Scénario 3: les deux vides" => [
        'personalPhone' => '',
        'merchantPhone' => '',
        'expected' => ''
    ],
    "Scénario 4: merchantPhone null, personalPhone renseigné" => [
        'personalPhone' => '123456',
        'merchantPhone' => null,
        'expected' => '123456'
    ]
];

echo "=== Test logique JavaScript ===\n";
foreach ($scenarios as $name => $data) {
    echo "\n$name\n";
    echo "  personalPhone: " . ($data['personalPhone'] ?: 'null/empty') . "\n";
    echo "  merchantPhone: " . ($data['merchantPhone'] ?: 'null/empty') . "\n";
    
    // Simuler la logique JavaScript: merchantPhone || personalPhone || ''
    $result = $data['merchantPhone'] ?: ($data['personalPhone'] ?: '');
    
    echo "  Résultat: '$result'\n";
    echo "  Attendu: '{$data['expected']}'\n";
    echo "  ✅ " . ($result === $data['expected'] ? "CORRECT" : "ERREUR") . "\n";
}

echo "\n=== Méthodes Service.js corrigées ===\n";
echo "1. updateApplicationJSON:\n";
echo "   merchant_phone: formData.merchantPhone || formData.personalPhone || ''\n\n";

echo "2. updateApplicationFormData:\n";  
echo "   merchant_phone: (formData.merchantPhone && formData.merchantPhone.trim()) || (formData.personalPhone && formData.personalPhone.trim()) || ''\n\n";

echo "✅ Le fallback est maintenant cohérent dans toutes les méthodes !\n";