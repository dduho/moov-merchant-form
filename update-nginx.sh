#!/bin/bash

# Script pour mettre à jour la configuration Nginx
echo "🔧 Mise à jour de la configuration Nginx..."

# Déplacer le fichier de configuration
sudo mv /tmp/moov-merchant.conf /etc/nginx/sites-available/moov-merchant

# Vérifier la configuration
echo "✓ Vérification de la configuration Nginx..."
sudo nginx -t

if [ $? -eq 0 ]; then
    echo "✓ Configuration valide, rechargement de Nginx..."
    sudo systemctl reload nginx
    echo "✅ Nginx rechargé avec succès!"
else
    echo "❌ Erreur dans la configuration Nginx"
    exit 1
fi
