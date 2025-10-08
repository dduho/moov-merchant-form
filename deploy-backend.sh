#!/bin/bash
# =================================================================
# SCRIPT: deploy-backend.sh
# Déploiement du backend Laravel en production
# =================================================================

echo "🚀 Déploiement Backend Moov Merchant API..."

# Variables
BACKEND_DIR="./backend"
APP_DIR="/var/www/moov-merchant-api"

# Vérification des prérequis
if ! command -v php &> /dev/null; then
    echo "❌ PHP n'est pas installé"
    exit 1
fi
if ! command -v composer &> /dev/null; then
    echo "❌ Composer n'est pas installé"
    exit 1
fi

# Installation des dépendances
echo "📦 Installation des dépendances Composer..."
cd $BACKEND_DIR
composer install --no-dev --optimize-autoloader

# Configuration
echo "⚙️ Configuration de l'application..."
php artisan key:generate
php artisan config:cache
php artisan route:cache
php artisan view:cache

# Migrations
echo "🗄️ Exécution des migrations..."
php artisan migrate --force

# Permissions
echo "🔐 Configuration des permissions..."
sudo mkdir -p $APP_DIR
sudo cp -r ./* $APP_DIR/
sudo chown -R www-data:www-data $APP_DIR
sudo chmod -R 755 $APP_DIR
sudo chmod -R 775 $APP_DIR/storage $APP_DIR/bootstrap/cache

# Création du lien symbolique pour le stockage
php artisan storage:link

# Configuration du serveur web (Nginx)
echo "🌐 Configuration du serveur web..."
sudo tee /etc/nginx/sites-available/moov-merchant-api > /dev/null <<EOF_CONF
server {
    listen 8000;
    server_name api.merchant.moovmoney.com;
    root $APP_DIR/public;
    index index.php;
    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }
    location ~ \.php$ {
        fastcgi_pass unix:/var/run/php/php8.1-fpm.sock;
        fastcgi_index index.php;
        fastcgi_param SCRIPT_FILENAME $realpath_root$fastcgi_script_name;
        include fastcgi_params;
    }
    location ~ /\. {
        deny all;
    }
}
EOF_CONF

sudo ln -sf /etc/nginx/sites-available/moov-merchant-api /etc/nginx/sites-enabled/
sudo nginx -t && sudo systemctl reload nginx

# Configuration des tâches CRON
echo "⏰ Configuration des tâches programmées..."
(crontab -l 2>/dev/null; echo "* * * * * cd $APP_DIR && php artisan schedule:run >> /dev/null 2>&1") | crontab -

echo "✅ Déploiement backend terminé avec succès!"