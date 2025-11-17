# Déploiement Moov Merchant Form - Résumé

## ✅ Déploiement Terminé avec Succès!

### 📋 Informations du Serveur
- **IP**: 10.80.16.51
- **Système**: Ubuntu 24.04.3 LTS
- **Utilisateur**: moov
- **Mot de passe**: Root@1324

### 🌐 URLs d'Accès
- **Frontend**: http://10.80.16.51
- **API**: http://10.80.16.51/api
- **Health Check**: http://10.80.16.51/api/health

### 👤 Compte Administrateur
- **Email**: admin@moov.com
- **Username**: admin
- **Password**: Admin@2024

### 🔧 Services Installés
- ✅ Nginx 1.24.0
- ✅ PHP 8.2.29 (avec FPM)
- ✅ MySQL 8.0.43
- ✅ Node.js 20.19.5
- ✅ npm 10.8.2
- ✅ Composer 2.9.1

### 📦 Composants Déployés
- ✅ Backend Laravel (v12) dans `/var/www/moov-merchant-form/backend`
- ✅ Frontend Vue.js 3 (build production) dans `/var/www/moov-merchant-form/frontend`
- ✅ Base de données `moov_merchant` configurée
- ✅ Migrations exécutées (23 migrations)
- ✅ Service de queue Laravel actif

### 🗄️ Base de Données
- **Nom**: moov_merchant
- **Utilisateur**: moov_user
- **Mot de passe**: Moov@2024!
- **Host**: localhost (127.0.0.1)

### 🚀 Services Actifs
```bash
# Vérifier les services
sudo systemctl status nginx
sudo systemctl status php8.2-fpm
sudo systemctl status mysql
sudo systemctl status moov-queue
```

### 📊 Logs
```bash
# Logs Nginx
sudo tail -f /var/log/nginx/moov-merchant-access.log
sudo tail -f /var/log/nginx/moov-merchant-error.log

# Logs Laravel
sudo tail -f /var/www/moov-merchant-form/backend/storage/logs/laravel.log

# Logs Queue Worker
sudo journalctl -u moov-queue -f
```

### 🔄 Mise à Jour de l'Application
```bash
# Se connecter au serveur
ssh moov@10.80.16.51

# Mettre à jour le code
cd /var/www/moov-merchant-form
git pull

# Backend
cd backend
composer install --no-dev --optimize-autoloader
php artisan migrate --force
php artisan config:cache
php artisan route:cache
php artisan view:cache

# Frontend
cd ../frontend
npm install
npm run build

# Redémarrer les services
sudo systemctl restart nginx
sudo systemctl restart php8.2-fpm
sudo systemctl restart moov-queue
```

### 🛠️ Commandes Utiles
```bash
# Redémarrer Nginx
sudo systemctl restart nginx

# Redémarrer PHP-FPM
sudo systemctl restart php8.2-fpm

# Redémarrer le Queue Worker
sudo systemctl restart moov-queue

# Vider le cache Laravel
cd /var/www/moov-merchant-form/backend
php artisan cache:clear
php artisan config:clear
php artisan route:clear
php artisan view:clear

# Voir les logs en temps réel
sudo tail -f /var/log/nginx/moov-merchant-error.log
sudo journalctl -u moov-queue -f
```

### 📁 Structure des Dossiers
```
/var/www/moov-merchant-form/
├── backend/               # API Laravel
│   ├── app/
│   ├── config/
│   ├── database/
│   ├── public/           # Point d'entrée API
│   ├── routes/
│   ├── storage/          # Fichiers uploadés & logs
│   └── .env             # Configuration (DB, APP_KEY, etc.)
│
└── frontend/             # Application Vue.js
    ├── dist/             # Build de production (servi par Nginx)
    ├── src/
    └── .env.production   # Configuration frontend
```

### 🔐 Sécurité
- ✅ Fichiers sensibles (.env, etc.) protégés par Nginx
- ✅ Limite d'upload de fichiers: 50MB
- ✅ MySQL sécurisé avec utilisateur dédié
- ✅ Permissions correctes sur storage/ et bootstrap/cache/

### ✅ Tests Effectués
- ✅ Frontend accessible (http://10.80.16.51)
- ✅ API Health Check réussit (http://10.80.16.51/api/health)
- ✅ Base de données fonctionnelle
- ✅ Migrations exécutées
- ✅ Utilisateur admin créé
- ✅ Tous les services actifs

### 📝 Notes
- Le projet est cloné depuis GitHub: https://github.com/dduho/moov-merchant-form
- Les mises à jour peuvent être déployées via `git pull` puis rebuild
- Le service moov-queue traite les jobs en arrière-plan (emails, notifications, etc.)
- Le build frontend utilise Vite avec target "esnext" pour supporter le top-level await

### 🎉 Prochaines Étapes
1. Tester la création d'une nouvelle candidature
2. Tester le dashboard administrateur
3. Configurer les emails (actuellement en mode "log")
4. Configurer un domaine (optionnel)
5. Configurer HTTPS avec Let's Encrypt (recommandé pour la production)

---
**Date de déploiement**: 17 novembre 2025
**Déployé par**: Assistant GitHub Copilot
