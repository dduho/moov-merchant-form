# 🚀 Démarrage Rapide - Environnement Local Docker

## Le problème
Vous obtenez l'erreur **"Les identifiants fournis sont incorrects"** car votre base de données locale est vide.

## ✅ La solution (AUTOMATIQUE)

### Script automatique (RECOMMANDÉ) ⚡

**Sur Windows (PowerShell) :**
```powershell
.\setup-local.ps1
```

**Sur Linux/Mac :**
```bash
chmod +x setup-local.sh
./setup-local.sh
```

Le script va automatiquement :
- ✅ Configurer les fichiers .env pour Docker
- ✅ Démarrer tous les conteneurs
- ✅ Créer la base de données
- ✅ Exécuter les migrations
- ✅ Créer 4 utilisateurs de test (admin, floozadmin, testadmin, commercial)

### Option 2 : Manuelle étape par étape 📝

#### Étape 1 : Configurer les fichiers .env
```bash
# Backend
cp backend/.env.local backend/.env

# Frontend
cp frontend/.env.local frontend/.env
```

#### Étape 2 : Démarrer Docker
```bash
docker-compose down -v
docker-compose up -d
```

#### Étape 3 : Attendre 30 secondes que MySQL démarre
```bash
# Vérifier que MySQL est prêt
docker-compose logs database | grep "ready for connections"
```

#### Étape 4 : Créer la base de données et l'admin
```bash
# Entrer dans le conteneur backend
docker-compose exec backend sh

# Exécuter les migrations
php artisan migrate:fresh

# Ouvrir Tinker
php artisan tinker
```

#### Étape 5 : Dans Tinker, copier-coller ce code
```php
$adminRole = \App\Models\Role::firstOrCreate(['slug' => 'admin'], ['name' => 'Administrateur', 'description' => 'Admin système']);

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

$admin->roles()->attach($adminRole->id);

echo "✅ Admin créé : " . $admin->username;

exit
```

### Option 3 : Migration rapide uniquement 🔄

Si les conteneurs tournent déjà et que vous voulez juste réinitialiser la base :

```bash
docker-compose exec backend php artisan migrate:fresh
docker-compose exec backend php artisan tinker
# Puis coller le code de création admin ci-dessus
```

## 🌐 Accès à l'application

Une fois configuré, accédez à :

- **Frontend** : http://localhost:3000
- **Backend API** : http://localhost:8000
- **MailHog** (emails de test) : http://localhost:8025

## 🔐 Identifiants de connexion

```
Username: admin
Password: Admin@2024
```

## 📊 Commandes utiles

### Voir les logs en temps réel
```bash
docker-compose logs -f
```

### Redémarrer les services
```bash
docker-compose restart
```

### Arrêter tout
```bash
docker-compose down
```

### Réinitialisation complète (avec suppression des données)
```bash
docker-compose down -v
.\setup-local.ps1  # ou ./setup-local.sh sur Linux/Mac
```

## ⚠️ Important

- Les fichiers `.env.local` sont configurés pour Docker uniquement
- Votre `.env` de production reste inchangé
- Ne commitez JAMAIS les fichiers `.env` dans Git
- Ces fichiers sont déjà dans `.gitignore`

## 🔧 Résolution de problèmes

### Erreur : "Connection refused" sur la base de données
➡️ Attendez que MySQL démarre complètement (30 secondes)

### Le frontend ne se connecte pas au backend
➡️ Vérifiez que `frontend/.env` contient : `VITE_API_URL=http://localhost:8000`

### Les migrations échouent
➡️ Réinitialisez tout :
```bash
docker-compose down -v
docker-compose up -d
# Attendez 30 secondes
docker-compose exec backend php artisan migrate:fresh
```

### L'utilisateur admin existe déjà mais je ne peux pas me connecter
➡️ Réinitialisez le mot de passe :
```bash
docker-compose exec backend php artisan tinker
```
Puis dans Tinker :
```php
$admin = \App\Models\User::where('username', 'admin')->first();
$admin->password = bcrypt('Admin@2024');
$admin->save();
echo "✅ Mot de passe réinitialisé";
exit
```

## 📝 Notes

Ce setup est **uniquement pour le développement local**. La production utilise d'autres configurations qui ne sont pas affectées par ces changements.
