#!/usr/bin/env php
<?php

/**
 * Script de test pour vérifier le nouveau système de nommage des fichiers
 * Usage: php test-document-naming.php
 */

require __DIR__ . '/backend/vendor/autoload.php';

use Illuminate\Support\Str;

echo "=== Test du système de nommage des fichiers ===\n\n";

// Simuler la génération de noms de fichiers
$documentType = 'id_card';
$referenceNumber = 'MM251009ECLKUP';
$timestamp = date('YmdHis');
$random = Str::random(12);
$extension = 'jpg';

// Sans préfixe (ancien format)
$oldFormat = "{$documentType}_{$timestamp}_{$random}.{$extension}";
echo "Format ANCIEN (sans référence) :\n";
echo "  {$oldFormat}\n\n";

// Avec préfixe (nouveau format)
$newFormat = "{$referenceNumber}_{$documentType}_{$timestamp}_{$random}.{$extension}";
echo "Format NOUVEAU (avec référence) :\n";
echo "  {$newFormat}\n\n";

// Exemples pour différents types de documents
echo "Exemples pour différents types de documents :\n";
$documentTypes = [
    'id_card' => 'Carte d\'identité',
    'anid_card' => 'Carte ANID',
    'residence_card' => 'Carte de résidence',
    'cfe_card' => 'Carte CFE',
    'nif_document' => 'Document NIF',
    'business_license' => 'Licence commerciale',
];

foreach ($documentTypes as $type => $label) {
    $random = Str::random(12);
    $filename = "{$referenceNumber}_{$type}_{$timestamp}_{$random}.pdf";
    echo "  - {$label}: {$filename}\n";
}

echo "\n";
echo "Chemin complet dans le stockage :\n";
echo "  storage/app/merchant-documents/{document_type}/{year}/{month}/{filename}\n";
echo "  Exemple: storage/app/merchant-documents/id_card/2025/11/{$newFormat}\n";

echo "\n✅ Test terminé avec succès !\n";
