#!/bin/bash

echo "🔧 Nettoyage et application de la configuration Nginx..."

# Désactiver l'ancienne configuration
echo "1️⃣ Désactivation de l'ancienne configuration..."
sudo rm -f /etc/nginx/sites-enabled/moov-merchant

# Copier et activer la nouvelle configuration
echo "2️⃣ Application de la nouvelle configuration..."
sudo cp /var/www/moov-merchant-form/nginx-config.conf /etc/nginx/sites-available/moov-merchant-form
sudo ln -sf /etc/nginx/sites-available/moov-merchant-form /etc/nginx/sites-enabled/

# Tester la configuration
echo "3️⃣ Test de la configuration Nginx..."
sudo nginx -t

if [ $? -eq 0 ]; then
    echo "✅ Configuration valide"
    echo "4️⃣ Rechargement de Nginx..."
    sudo systemctl reload nginx
    echo "✅ Nginx rechargé avec succès!"
    echo ""
    echo "📋 Configuration appliquée:"
    echo "  - Ancienne config 'moov-merchant' désactivée"
    echo "  - Nouvelle config 'moov-merchant-form' activée"
    echo "  - Site accessible sur: http://merch.moov-africa.tg/"
else
    echo "❌ Erreur dans la configuration Nginx"
    exit 1
fi
