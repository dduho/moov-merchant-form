# 🔐 Credentials - Environnement Local Docker

## Accès à l'application

Une fois Docker démarré avec `.\setup-local.ps1`, vous pouvez accéder à :

### 🌐 URLs

- **Frontend** : http://localhost:3000
- **Backend API** : http://localhost:8000
- **MailHog** (emails de test) : http://localhost:8025
- **Base de données MySQL** : localhost:3306

## 👤 Comptes utilisateurs disponibles

### 1. Admin Principal (Recommandé)
```
Username: admin
Password: password
Email: admin@flooz.com
Rôle: Administrateur
```

### 2. Flooz Admin
```
Username: floozadmin
Password: 1210
Email: admin@moovmoney.com
Rôle: Administrateur
```

### 3. Test Admin
```
Username: testadmin
Password: password
Email: admin@test.com
Rôle: Administrateur
```

### 4. Commercial (pour tests)
```
Username: commercial
Password: password
Email: commercial@flooz.com
Rôle: Commercial
```

## 🗄️ Base de données

```
Host: localhost (ou database depuis un conteneur)
Port: 3306
Database: moov_merchant
Username: root
Password: secret
```

## 📨 MailHog (Emails de test)

Pour voir tous les emails envoyés par l'application en local :
- Ouvrir http://localhost:8025
- Tous les emails sont capturés et affichés dans l'interface

## ⚠️ Important

- Ces credentials sont **UNIQUEMENT pour le développement local**
- Ne JAMAIS utiliser ces mots de passe en production
- Les fichiers `.env.local` ne sont pas committés dans Git
- La configuration de production reste inchangée

## 🔄 Réinitialiser les credentials

Si vous avez oublié un mot de passe ou voulez recréer les utilisateurs :

```powershell
# Arrêter et supprimer tout
docker-compose down -v

# Relancer le script de configuration
.\setup-local.ps1
```

Ou manuellement :

```bash
# Entrer dans le conteneur backend
docker-compose exec backend sh

# Lancer le script de vérification/création
php check-users.php
```

## 🔧 Changer un mot de passe

Pour changer le mot de passe d'un utilisateur :

```bash
docker-compose exec backend php artisan tinker
```

Puis dans Tinker :
```php
$user = \App\Models\User::where('username', 'admin')->first();
$user->password = bcrypt('nouveau_mot_de_passe');
$user->save();
echo "✅ Mot de passe mis à jour";
exit
```

## 📝 Notes

- Tous les mots de passe sont hashés avec bcrypt
- Le champ `password_changed_at` est défini pour éviter le changement forcé au premier login
- Tous les utilisateurs sont actifs par défaut (`is_active = true`)
