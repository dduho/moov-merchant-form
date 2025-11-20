# Ajout du rôle "Personnel" - Récapitulatif des modifications

## Vue d'ensemble
Le rôle "personnel" a été ajouté au système avec les mêmes permissions que les commerciaux pour soumettre des candidatures, **mais sans être soumis aux objectifs**.

## Modifications Backend

### 1. Base de données
- **Migration créée** : `2025_11_19_194242_add_personnel_role_to_roles_table.php`
  - Ajoute le rôle "Personnel" (slug: `personnel`) dans la table `roles`
  - Migration exécutée avec succès

- **RoleSeeder** : `backend/database/seeders/RoleSeeder.php`
  - Ajout du rôle Personnel entre Commercial et Client

### 2. Modèles

- **User.php** : `backend/app/Models/User.php`
  - `isPersonnel()` : Nouvelle méthode pour vérifier si l'utilisateur est personnel
  - `canSubmitApplications()` : Nouvelle méthode qui retourne true pour commercial ET personnel

### 3. Contrôleurs

#### AuthController
- **Validation** : Rôle 'personnel' ajouté dans la validation lors de la création d'utilisateur
- **Logique d'objectifs** : Le personnel n'est PAS soumis aux objectifs (seulement les commerciaux)

#### MerchantApplicationController
- **index()** : Personnel peut voir ses propres candidatures
- **show()** : Personnel peut voir uniquement ses propres candidatures
- **update()** : Personnel peut modifier ses candidatures (sauf si approuvées/exportées)
- **updateFull()** : Personnel a les mêmes restrictions que les commerciaux

#### UserManagementController
- **index()** : Statistiques ajoutées pour le personnel (comme pour les commerciaux)
- Note : Le personnel aura des stats de candidatures mais pas d'objectifs

## Modifications Frontend

### 1. Store Pinia

- **auth.js** : `frontend/src/stores/auth.js`
  - `isPersonnel` : Nouveau getter
  - `canSubmitApplications` : Nouveau getter (commercial OU personnel)
  - `canViewApplications` : Inclut maintenant le personnel
  - `canEditApplications` : Inclut maintenant le personnel
  - Les permissions admin restent exclusives (validation, rejet, suppression)

### 2. Composants

#### RegisterView.vue
- Ajout de l'option "Personnel" dans le select des rôles
- Message de confirmation adapté pour inclure le rôle Personnel

#### EditUserModal.vue
- Ajout de l'option "Personnel" dans le select des rôles

#### UserManagement.vue
- Ajout du filtre "Personnel" dans le select de filtrage
- `getRoleColor()` : Couleur verte pour le personnel (bg-green-100 text-green-800)
- `getRoleIcon()` : Icône fa-user-tie pour le personnel
- `getRoleName()` : Affichage "Personnel"

#### MerchantForm.vue
- Titre changé : "Informations du Soumissionnaire" (au lieu de "du Commercial")
- Labels simplifiés : "Nom", "Prénoms", "Téléphone" (au lieu de "du commercial")
- Validation adaptée : Commercial OU Personnel peuvent soumettre
- Champs requis pour commercial ET personnel
- Pré-remplissage automatique pour commercial ET personnel

## Permissions et Comportements

### Personnel peut :
✅ Se connecter à l'application
✅ Soumettre des candidatures de marchands
✅ Voir ses propres candidatures
✅ Modifier ses candidatures (sauf si approuvées/exportées)
✅ Avoir des statistiques de candidatures

### Personnel ne peut PAS :
❌ Être soumis à des objectifs
❌ Voir les candidatures des autres utilisateurs (sauf admin)
❌ Approuver/Rejeter des candidatures (réservé aux admins)
❌ Supprimer définitivement des candidatures (réservé aux admins)
❌ Vérifier des documents (réservé aux admins)
❌ Modifier les candidatures approuvées ou exportées

## Différences avec Commercial

| Fonctionnalité | Commercial | Personnel |
|----------------|-----------|-----------|
| Soumettre candidatures | ✅ | ✅ |
| Objectifs mensuels/annuels | ✅ | ❌ |
| Voir ses candidatures | ✅ | ✅ |
| Modifier candidatures | ✅ | ✅ |
| Stats candidatures | ✅ | ✅ |
| Widget objectifs | ✅ | ❌ |

## Tests recommandés

1. **Création d'utilisateur Personnel**
   - Via RegisterView
   - Vérifier que le rôle est bien assigné

2. **Soumission de candidature**
   - En tant que Personnel
   - Vérifier le pré-remplissage des champs
   - Vérifier la validation

3. **Consultation de candidatures**
   - Vérifier que le personnel ne voit que ses candidatures
   - Vérifier que les statistiques s'affichent

4. **Gestion des objectifs**
   - Vérifier que le personnel n'a PAS le widget d'objectifs
   - Vérifier qu'aucun objectif n'est créé automatiquement

5. **Permissions**
   - Vérifier que le personnel ne peut pas approuver/rejeter
   - Vérifier qu'il ne peut pas modifier les candidatures approuvées

## Déploiement

### En local
1. Exécuter la migration : `php artisan migrate`
2. Rebuild frontend : `npm run build`
3. Tester les fonctionnalités

### En production
1. Commit et push des modifications
2. Sur le serveur :
   ```bash
   cd /var/www/moov-merchant-form
   git pull origin main
   cd backend
   php artisan migrate
   php artisan config:clear
   php artisan cache:clear
   cd ../frontend
   npm run build
   ```

## Fichiers modifiés

### Backend (11 fichiers)
- `database/seeders/RoleSeeder.php`
- `database/migrations/2025_11_19_194242_add_personnel_role_to_roles_table.php`
- `app/Models/User.php`
- `app/Http/Controllers/AuthController.php`
- `app/Http/Controllers/MerchantApplicationController.php`
- `app/Http/Controllers/UserManagementController.php`

### Frontend (6 fichiers)
- `src/stores/auth.js`
- `src/views/RegisterView.vue`
- `src/views/UserManagement.vue`
- `src/views/MerchantForm.vue`
- `src/components/EditUserModal.vue`

**Total : 17 fichiers modifiés + 1 migration créée**
