#!/bin/bash

# Script de déploiement Nginx pour moov-merchant-form
echo "🚀 Déploiement de la configuration Nginx..."

# Copier la configuration
sudo cp /var/www/moov-merchant-form/nginx-config.conf /etc/nginx/sites-available/moov-merchant-form

# Créer le lien symbolique
sudo ln -sf /etc/nginx/sites-available/moov-merchant-form /etc/nginx/sites-enabled/

# Supprimer le site par défaut s'il existe
sudo rm -f /etc/nginx/sites-enabled/default

# Tester la configuration
echo "🔍 Test de la configuration Nginx..."
sudo nginx -t

if [ $? -eq 0 ]; then
    echo "✅ Configuration valide"
    echo "🔄 Rechargement de Nginx..."
    sudo systemctl reload nginx
    echo "✅ Nginx rechargé avec succès!"
    echo ""
    echo "📋 Résumé:"
    echo "  - Frontend: http://10.80.16.51/"
    echo "  - API: http://10.80.16.51/api"
    echo "  - Auth: http://10.80.16.51/auth"
else
    echo "❌ Erreur dans la configuration Nginx"
    exit 1
fi
