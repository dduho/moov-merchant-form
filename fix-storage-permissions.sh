#!/bin/bash
echo "Vérification et correction des permissions storage..."
cd /var/www/moov-merchant-form/backend

# Corriger les permissions sur le dossier merchant-documents
sudo chmod 755 storage/app/private/merchant-documents
sudo find storage/app/private/merchant-documents -type d -exec chmod 755 {} \;
sudo find storage/app/private/merchant-documents -type f -exec chmod 644 {} \;

echo "Permissions corrigées!"
echo ""
echo "Test d'accès au document ID 2:"
php -r "require 'vendor/autoload.php'; \$app = require 'bootstrap/app.php'; \$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap(); \$doc = App\Models\ApplicationDocument::find(2); if (\$doc && Storage::exists(\$doc->file_path)) { echo 'OK: Fichier accessible'; } else { echo 'ERREUR: Fichier inaccessible'; }"
