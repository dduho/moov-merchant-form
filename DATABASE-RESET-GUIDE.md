# 🗑️ Script de Réinitialisation de la Base de Données

## Description

Ce script permet de vider complètement la base de données tout en conservant l'utilisateur administrateur par défaut.

## ⚠️ Attention

Ce script supprime **toutes** les données suivantes:
- ❌ Toutes les candidatures marchands
- ❌ Tous les documents uploadés
- ❌ Toutes les notifications
- ❌ Tous les utilisateurs (sauf l'admin)
- ❌ Tous les objectifs utilisateurs
- ❌ Jobs en attente et cache

**L'utilisateur admin est conservé:**
- Email: `admin@moov.com`
- Username: `admin`
- Password: `Admin@2024`

## 📋 Utilisation sur le Serveur

### 1. Se connecter au serveur
```bash
ssh moov@10.80.16.51
```

### 2. Aller dans le répertoire du projet
```bash
cd /var/www/moov-merchant-form
```

### 3. Exécuter le script
```bash
./reset-database.sh
```

Le script vous demandera une confirmation. Tapez **`oui`** pour continuer.

### Exemple d'exécution
```bash
moov@server:/var/www/moov-merchant-form$ ./reset-database.sh

==========================================
  Réinitialisation de la base de données
==========================================

⚠️  ATTENTION: Cette opération va:
   - Supprimer toutes les candidatures
   - Supprimer tous les documents
   - Supprimer toutes les notifications
   - Supprimer tous les utilisateurs sauf l'admin
   - Réinitialiser les objectifs

Êtes-vous sûr de vouloir continuer? (tapez 'oui' pour confirmer): oui

🔄 Réinitialisation en cours...
🗑️  Suppression des candidatures et documents...
🗑️  Suppression des notifications...
🗑️  Suppression des objectifs utilisateurs...
🗑️  Suppression des jobs et caches...
🗑️  Nettoyage des utilisateurs (conservation de l'admin)...
✅ Utilisateur admin conservé (ID: 1)

✨ Nettoyage terminé!

✅ Base de données réinitialisée avec succès!

👤 Utilisateur admin conservé:
   Email: admin@moov.com
   Username: admin
   Password: Admin@2024
```

## 🔧 Utilisation Manuelle (Alternative)

Si vous préférez exécuter la commande directement sans le script bash:

```bash
cd /var/www/moov-merchant-form/backend
php artisan db:seed --class=DatabaseCleanupSeeder
```

## 📝 Notes

- Le script est **sécurisé** et demande une confirmation avant d'agir
- Les contraintes de clés étrangères sont respectées
- Les fichiers uploadés dans `storage/app/public` ne sont pas supprimés (à faire manuellement si besoin)
- Le cache Laravel est vidé automatiquement

## 🚀 Après la Réinitialisation

Vous pouvez:
1. Vous connecter avec le compte admin
2. Créer de nouveaux utilisateurs
3. Commencer à saisir de nouvelles candidatures
4. Redéfinir les objectifs

## ⚙️ Nettoyage des Fichiers (Optionnel)

Pour supprimer aussi les fichiers uploadés:
```bash
cd /var/www/moov-merchant-form/backend
rm -rf storage/app/public/documents/*
php artisan storage:link
```
