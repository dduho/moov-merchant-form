#!/bin/bash

# Script pour réinitialiser la base de données
# Garde uniquement l'utilisateur admin par défaut

set -e

echo "=========================================="
echo "  Réinitialisation de la base de données"
echo "=========================================="
echo ""
echo "⚠️  ATTENTION: Cette opération va:"
echo "   - Supprimer toutes les candidatures"
echo "   - Supprimer tous les documents"
echo "   - Supprimer toutes les notifications"
echo "   - Supprimer tous les utilisateurs sauf l'admin"
echo "   - Réinitialiser les objectifs"
echo ""
read -p "Êtes-vous sûr de vouloir continuer? (tapez 'oui' pour confirmer): " confirm

if [ "$confirm" != "oui" ]; then
    echo "❌ Opération annulée"
    exit 1
fi

echo ""
echo "🔄 Réinitialisation en cours..."

# Se placer dans le répertoire du backend
cd "$(dirname "$0")/backend" 2>/dev/null || cd /var/www/moov-merchant-form/backend

# Exécuter les commandes de nettoyage
php artisan db:seed --class=DatabaseCleanupSeeder

echo ""
echo "✅ Base de données réinitialisée avec succès!"
echo ""
echo "👤 Utilisateur admin conservé:"
echo "   Email: admin@moov.com"
echo "   Username: admin"
echo "   Password: Admin@2024"
echo ""
