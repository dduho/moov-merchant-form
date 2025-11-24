# Guide de Configuration Docker Local

## Problème rencontré
Erreur 422 lors de la connexion : "Les identifiants fournis sont incorrects"

## Solution

### 1. Copier le fichier .env.local pour Docker
```bash
# Dans le dossier backend
cp .env.local .env
```

### 2. Démarrer les conteneurs Docker
```bash
# À la racine du projet
docker-compose up -d
```

### 3. Attendre que la base de données soit prête
```bash
# Vérifier que MySQL est démarré
docker-compose logs database
```

### 4. Exécuter les migrations et créer l'utilisateur admin
```bash
# Entrer dans le conteneur backend
docker-compose exec backend sh

# Une fois dans le conteneur :

# Installer les dépendances si nécessaire
composer install

# Générer la clé d'application
php artisan key:generate

# Exécuter les migrations
php artisan migrate:fresh

# Créer l'utilisateur admin
php artisan tinker
```

### 5. Dans Tinker, créer l'utilisateur admin
```php
// Créer le rôle admin
$adminRole = \App\Models\Role::create([
    'name' => 'Administrateur',
    'slug' => 'admin',
    'description' => 'Administrateur système avec tous les droits'
]);

// Créer l'utilisateur admin
$admin = \App\Models\User::create([
    'first_name' => 'Admin',
    'last_name' => 'System',
    'username' => 'admin',
    'email' => 'admin@moov.tg',
    'password' => bcrypt('Admin@2024'),
    'is_active' => true,
    'email_verified_at' => now(),
    'password_must_be_changed' => false
]);

// Attacher le rôle admin
$admin->roles()->attach($adminRole->id);

// Vérifier
$admin->fresh()->load('roles');

// Quitter tinker
exit
```

### 6. Quitter le conteneur et tester
```bash
# Quitter le conteneur
exit

# Vérifier que tout fonctionne
docker-compose ps
```

### 7. Accéder à l'application
- Frontend: http://localhost:3000
- Backend API: http://localhost:8000
- MailHog (emails): http://localhost:8025

### 8. Se connecter
- **Username**: admin
- **Password**: Admin@2024

## Commandes utiles

### Redémarrer tous les conteneurs
```bash
docker-compose restart
```

### Voir les logs
```bash
# Tous les services
docker-compose logs -f

# Un service spécifique
docker-compose logs -f backend
docker-compose logs -f frontend
docker-compose logs -f database
```

### Arrêter les conteneurs
```bash
docker-compose down
```

### Arrêter et supprimer les volumes (réinitialisation complète)
```bash
docker-compose down -v
```

### Reconstruire les images
```bash
docker-compose build --no-cache
docker-compose up -d
```

## Différences avec la production

Ce fichier `.env.local` est configuré spécifiquement pour Docker avec :
- `DB_HOST=database` (nom du service Docker)
- `REDIS_HOST=redis` (nom du service Docker)
- `MAIL_HOST=mailhog` (pour tester les emails localement)

Le fichier `.env` principal reste inchangé pour la production.

## Troubleshooting

### La base de données n'est pas prête
Attendez quelques secondes que MySQL démarre complètement :
```bash
docker-compose logs database | grep "ready for connections"
```

### Erreur de connexion à la base de données
Vérifiez que le conteneur database est bien démarré :
```bash
docker-compose ps
```

### Les migrations échouent
Supprimez les volumes et recommencez :
```bash
docker-compose down -v
docker-compose up -d
# Attendez 30 secondes
docker-compose exec backend php artisan migrate:fresh
```

### Le frontend ne se connecte pas au backend
Vérifiez le fichier `frontend/.env` ou créez-le :
```
VITE_API_URL=http://localhost:8000
```
