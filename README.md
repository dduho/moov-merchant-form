# 🏪 Moov Money - Formulaire de Recrutement Marchand

Application web progressive (PWA) pour la candidature des marchands Moov Money au Togo.

## ✨ Fonctionnalités

### 🎨 Interface Utilisateur
- ✅ Design moderne aux couleurs Moov Money (orange/gradient)
- ✅ Interface responsive mobile‑first
- ✅ Formulaire multi‑étapes avec progression visuelle
- ✅ Animations fluides et micro‑interactions
- ✅ Mode sombre/clair automatique

### 📱 PWA (Progressive Web App)
- ✅ Installation sur mobile/desktop
- ✅ Fonctionnement 100 % hors‑ligne
- ✅ Synchronisation automatique
- ✅ Cache intelligent des données
- ✅ Notifications push

### 🔧 Fonctionnalités Métier
- ✅ Upload et compression automatique des documents
- ✅ Signature électronique
- ✅ Géolocalisation GPS avec carte interactive
- ✅ Validation en temps réel
- ✅ Sauvegarde automatique
- ✅ Gestion des documents (CNI, ANID, séjour, CFE, NIF)

### 🚀 Backend API
- ✅ API REST Laravel 12 complète
- ✅ Validation robuste des données
- ✅ Stockage sécurisé des fichiers
- ✅ Notifications Email/SMS
- ✅ Dashboard administratif
- ✅ Rapports et statistiques

## 🛠️ Technologies

### Frontend
- **Vue.js 3** + Composition API
- **Tailwind CSS** + Animations
- **PWA** avec Service Workers
- **IndexedDB** pour stockage offline
- **Leaflet** pour cartes interactives
- **Signature Pad** pour signatures électroniques

- **Laravel 12** + API Resources
- **MySQL 8.0** + Migrations
- **Intervention Image** pour traitement d'images
- **Queue System** pour tâches asynchrones
- **Mail/SMS** notifications
- **Rate Limiting** et sécurité

## 📦 Installation

### Prérequis

- PHP 8.2+
- Node.js 18+
- MySQL 8.0+
- Composer
- NPM/Yarn

### 🎯 Installation Rapide

#### Frontend

```bash
cd frontend
npm install
cp .env.example .env
# Configurer VITE_API_URL=http://localhost:8000/api
npm run dev
```

#### Backend

```bash
cd backend
composer install
cp .env.example .env
php artisan key:generate

# Configuration base de données dans .env
php artisan migrate
php artisan db:seed
php artisan storage:link

php artisan serve
```

## 🚀 Déploiement Production

### Avec Docker (Recommandé)

```bash
# Cloner et configurer
git clone <repo-url>
cd moov-merchant-form

# Lancer avec Docker Compose
docker-compose up -d
```

### Déploiement Manuel

#### Frontend (Nginx)

```bash
cd frontend
npm install --production
npm run build

# Copier dist/ vers /var/www/moov-merchant
sudo cp -r dist/* /var/www/moov-merchant/
```

#### Backend (Apache/Nginx + PHP‑FPM)

```bash
cd backend
composer install --no-dev --optimize-autoloader
php artisan config:cache
php artisan route:cache
php artisan migrate --force

# Configuration serveur web
# Voir deploy-backend.sh pour configuration Nginx
```

## ⚙️ Configuration

### Variables d'environnement Frontend

```
VITE_API_URL=https://api.merchant.moovmoney.com/api
VITE_API_TIMEOUT=30000
VITE_ENABLE_PWA=true
VITE_ENABLE_GEOLOCATION=true
VITE_ENABLE_SIGNATURE=true
VITE_ENABLE_OFFLINE_MODE=true
VITE_MAX_FILE_SIZE=5120
VITE_MAX_FILES=10
VITE_ALLOWED_FILE_TYPES=jpg,jpeg,png,pdf
VITE_GOOGLE_ANALYTICS_ID=
VITE_HOTJAR_ID=
```

### Variables d'environnement Backend

```
APP_NAME="Moov Merchant API"
APP_URL=https://api.merchant.moovmoney.com
APP_ENV=production

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_DATABASE=moov_merchant
DB_USERNAME=moov_user
DB_PASSWORD=secure_password

MAIL_MAILER=smtp
MAIL_HOST=smtp.gmail.com
MAIL_USERNAME=noreply@moovmoney.com
MAIL_PASSWORD=app_password

NOTIFICATION_SMS_API_URL=https://api.sms-provider.com/send
NOTIFICATION_SMS_API_KEY=your_sms_api_key

FILESYSTEM_DRIVER=s3
AWS_BUCKET=moov-merchant-documents
AWS_ACCESS_KEY_ID=your_access_key
AWS_SECRET_ACCESS_KEY=your_secret_key
```

## 🎛️ Utilisation

### Pour les Candidats

1. **Accéder au formulaire** : https://merchant.moovmoney.com
2. **Remplir les informations** en 5 étapes :
   - Informations personnelles
   - Documents d'identité
   - Informations commerciales  
   - Localisation GPS
   - Signature électronique
3. **Soumission** : Automatique en ligne ou synchronisation hors‑ligne

### Pour les Administrateurs

1. **Dashboard** : https://admin.merchant.moovmoney.com
2. **Gestion des candidatures** : Validation, rejet, demande d'infos
3. **Rapports** : Statistiques et exports
4. **Configuration** : Paramètres système

## 🔧 API Documentation

### Endpoints Principaux

```
# Soumission candidature
POST /api/merchant-applications
Content-Type: multipart/form-data

# Statut candidature
GET /api/merchant-applications/{id}

# Upload document
POST /api/documents/upload
Content-Type: multipart/form-data

# Santé API
GET /api/health
```

### Exemple de réponse

```json
{
  "message": "Candidature soumise avec succès",
  "data": {
    "id": 123,
    "reference_number": "MM240101ABC123",
    "status": {
      "current": "pending",
      "label": "En attente"
    }
  }
}
```

## 🧪 Tests

### Frontend

```bash
cd frontend
npm run test
npm run test:e2e
```

### Backend

```bash
cd backend
php artisan test
```

## 📊 Monitoring

### Logs

```
# Frontend (Browser DevTools)
Application → Storage → IndexedDB
Application → Service Workers

# Backend
tail -f storage/logs/laravel.log
tail -f storage/logs/sync.log
```

### Métriques
- Taux de conversion des candidatures
- Performance API (temps de réponse)
- Utilisation cache offline
- Erreurs de validation

## 🔒 Sécurité

### Mesures Implémentées
- ✅ Validation stricte des uploads
- ✅ Compression et nettoyage des images (EXIF)
- ✅ Rate limiting API
- ✅ Protection CSRF
- ✅ Chiffrement des signatures
- ✅ URLs temporaires pour documents
- ✅ Headers de sécurité HTTP

### Bonnes Pratiques
- Chiffrement HTTPS obligatoire
- Sauvegarde automatique des données
- Logs d'audit des actions admin
- Monitoring des tentatives suspectes

## 🚨 Dépannage

### Problèmes Courants

#### Frontend ne se charge pas

```bash
# Vérifier la build
npm run build
# Vérifier la configuration Nginx
sudo nginx -t
```

#### API non accessible

```bash
# Vérifier les permissions
sudo chown -R www-data:www-data /var/www/moov-merchant-api
# Vérifier les logs
tail -f /var/log/nginx/error.log
```

#### Stockage offline ne fonctionne pas

```bash
# Vérifier Service Worker
Console → Application → Service Workers
# Vérifier IndexedDB
Console → Application → Storage → IndexedDB
```

## 🤝 Contribution

### Structure du Code

```
frontend/src/
├── components/     # Composants réutilisables
├── views/         # Pages/vues
├── stores/        # State management (Pinia)
├── services/      # Services API
└── utils/         # Fonctions utilitaires

backend/app/
├── Http/Controllers/  # Contrôleurs API
├── Models/           # Modèles Eloquent
├── Services/         # Services métier
└── Http/Resources/   # Resources API
```

### Standards de Code
- **Frontend** : Vue 3 Composition API + TypeScript
- **Backend** : PSR‑12 + Laravel Best Practices
- **Tests** : Coverage minimum 80 %
- **Documentation** : PHPDoc + JSDoc

## 📝 Changelog

### v1.0.0 (2024‑01‑01)
- ✨ Interface moderne aux couleurs Moov Money
- ✨ PWA complète avec mode offline
- ✨ API Laravel avec validation robuste
- ✨ Upload et traitement automatique des documents
- ✨ Géolocalisation et signature électronique
- ✨ Notifications Email/SMS
- ✨ Dashboard administratif

## 📞 Support

- **Email** : support@moovmoney.com
- **Téléphone** : +228 91 00 00 00
- **Documentation** : https://docs.merchant.moovmoney.com

## 📄 Licence

Propriétaire © 2024 Moov Money. Tous droits réservés.

---

**Développé avec ❤️ pour Moov Money Togo**
