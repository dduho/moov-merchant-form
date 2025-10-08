#!/bin/bash
# =================================================================
# SCRIPT: deploy-frontend.sh
# Déploiement du frontend en production
# =================================================================

echo "🚀 Déploiement Frontend Moov Merchant Form..."

# Variables
FRONTEND_DIR="./frontend"
BUILD_DIR="./frontend/dist"
NGINX_DIR="/var/www/moov-merchant"

# Vérification des prérequis
if ! command -v node &> /dev/null; then
    echo "❌ Node.js n'est pas installé"
    exit 1
fi
if ! command -v npm &> /dev/null; then
    echo "❌ NPM n'est pas installé"
    exit 1
fi

# Installation des dépendances
echo "📦 Installation des dépendances..."
cd $FRONTEND_DIR
npm install --production

# Build de production
echo "🏗️ Build de production..."
npm run build

# Vérification du build
if [ ! -d "$BUILD_DIR" ]; then
    echo "❌ Erreur lors du build"
    exit 1
fi

# Déploiement
echo "🚀 Déploiement vers $NGINX_DIR..."
sudo mkdir -p $NGINX_DIR
sudo cp -r $BUILD_DIR/* $NGINX_DIR/
sudo chown -R www-data:www-data $NGINX_DIR
sudo chmod -R 755 $NGINX_DIR

# Configuration Nginx
echo "⚙️ Configuration Nginx..."
sudo tee /etc/nginx/sites-available/moov-merchant > /dev/null <<EOF_CONF
server {
    listen 80;
    server_name merchant.moovmoney.com;
    root $NGINX_DIR;
    index index.html;
    
    # Gestion des assets
    location /assets/ {
        expires 1y;
        add_header Cache-Control "public, immutable";
    }
    
    # API proxy
    location /api/ {
        proxy_pass http://localhost:8000;
        proxy_set_header Host $host;
        proxy_set_header X-Real-IP $remote_addr;
        proxy_set_header X-Forwarded-For $proxy_add_x_forwarded_for;
        proxy_set_header X-Forwarded-Proto $scheme;
    }
    
    # SPA fallback
    location / {
        try_files $uri $uri/ /index.html;
    }
    
    # Security headers
    add_header X-Frame-Options DENY;
    add_header X-Content-Type-Options nosniff;
    add_header X-XSS-Protection "1; mode=block";
}
EOF_CONF

sudo ln -sf /etc/nginx/sites-available/moov-merchant /etc/nginx/sites-enabled/
sudo nginx -t && sudo systemctl reload nginx

echo "✅ Déploiement frontend terminé avec succès!"