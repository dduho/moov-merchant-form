# Importation en masse d'utilisateurs

## Description

Cette fonctionnalité permet aux administrateurs d'importer plusieurs utilisateurs à la fois depuis un fichier CSV.

## Format du fichier CSV

Le fichier CSV doit contenir les colonnes suivantes (en-têtes requis) :

| Colonne    | Type     | Requis | Description                                    |
|------------|----------|--------|------------------------------------------------|
| prenom     | Texte    | Oui    | Prénom de l'utilisateur                        |
| nom        | Texte    | Oui    | Nom de l'utilisateur                           |
| email      | Email    | Non    | Adresse email (optionnelle, mais doit être valide et unique si fournie) |
| telephone  | Texte    | Oui    | Numéro de téléphone (doit être unique)         |
| username   | Texte    | Oui    | Nom d'utilisateur (min 4 caractères, unique)   |
| role       | Texte    | Oui    | Rôle : `admin`, `commercial` ou `personnel`    |

## Exemple de fichier CSV

```csv
prenom,nom,email,telephone,username,role
Jean,Dupont,jean.dupont@example.com,90123456,jdupont,commercial
Marie,Martin,,91234567,mmartin,personnel
Admin,Test,admin@test.com,92345678,admintest,admin
```

## Mot de passe par défaut

Tous les utilisateurs importés recevront le mot de passe par défaut : **123456**

Ils devront changer ce mot de passe lors de leur première connexion.

## Utilisation

1. Connectez-vous en tant qu'administrateur
2. Accédez à la page "Gestion des Utilisateurs"
3. Cliquez sur le bouton "Importer"
4. Téléchargez le fichier template (bouton bleu dans le modal)
5. Remplissez le fichier CSV avec les informations des utilisateurs
6. Glissez-déposez le fichier dans la zone d'importation ou cliquez pour sélectionner
7. Cliquez sur "Importer"

## Validation

Le système vérifie :
- ✅ La présence de toutes les colonnes requises
- ✅ Le format de l'email (s'il est fourni)
- ✅ L'unicité de l'email, du téléphone et du nom d'utilisateur
- ✅ La longueur minimale du nom d'utilisateur (4 caractères)
- ✅ La validité du rôle (admin, commercial, personnel)

## Gestion des erreurs

Si des erreurs sont détectées :
- Les utilisateurs valides seront créés
- Les lignes avec erreurs seront affichées dans le modal
- Un résumé indique le nombre d'utilisateurs créés et le nombre d'erreurs

## Objectifs commerciaux

Les utilisateurs avec le rôle `commercial` recevront automatiquement les objectifs globaux en vigueur.

Le rôle `personnel` n'est pas soumis aux objectifs.

## Routes API

- **POST** `/api/users/bulk-import` - Importation en masse
- **GET** `/api/users/import-template` - Téléchargement du template CSV

## Limitations

- Taille maximale du fichier : 2 MB
- Format supporté : CSV uniquement
- Séparateur : virgule (`,`)
- Encodage recommandé : UTF-8
