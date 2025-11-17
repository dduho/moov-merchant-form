# 📋 Résumé des modifications - Préfixe des fichiers avec référence candidature

## ✅ Modifications effectuées

### 1. **DocumentStorageService.php** 
**Fichier:** `backend/app/Services/DocumentStorageService.php`

#### Changements:
- ✏️ Signature de `store()` modifiée : ajout du paramètre `?string $referencePrefix = null`
- ✏️ Signature de `generateSecureFilename()` modifiée : ajout du même paramètre
- 🆕 Logique ajoutée : si `$referencePrefix` est fourni, il est préfixé au nom du fichier

```php
// Avant
public function store(UploadedFile $file, string $documentType): array

// Après
public function store(UploadedFile $file, string $documentType, ?string $referencePrefix = null): array
```

---

### 2. **MerchantApplicationController.php**
**Fichier:** `backend/app/Http/Controllers/MerchantApplicationController.php`

#### Changements:
- ✏️ Méthode `storeDocument()` : passage du `reference_number` de l'application

```php
// Avant
$stored = $this->documentStorage->store($file, $type);

// Après
$stored = $this->documentStorage->store($file, $type, $application->reference_number);
```

---

### 3. **DocumentController.php**
**Fichier:** `backend/app/Http/Controllers/DocumentController.php`

#### Changements:
- 🆕 Récupération du `reference_number` depuis l'application associée
- ✏️ Passage du préfixe au service de stockage

```php
// Ajouté avant l'appel à store()
$referencePrefix = null;
if ($request->filled('merchant_application_id')) {
    $application = MerchantApplication::find($request->input('merchant_application_id'));
    if ($application) {
        $referencePrefix = $application->reference_number;
    }
}

// Modification de l'appel
$stored = $this->documentStorage->store($file, $type, $referencePrefix);
```

---

## 📊 Format des fichiers

### Avant (sans préfixe)
```
id_card_20251104143025_a1b2c3d4e5f6.jpg
cfe_document_20251104143030_x7y8z9w0v1u2.pdf
business_license_20251104143035_m9n8o7p6q5r4.jpg
```

### Après (avec préfixe référence)
```
MM251009ECLKUP_id_card_20251104143025_a1b2c3d4e5f6.jpg
MM251009ECLKUP_cfe_document_20251104143030_x7y8z9w0v1u2.pdf
MM251009ECLKUP_business_license_20251104143035_m9n8o7p6q5r4.jpg
```

### Structure complète
```
{REFERENCE}_{TYPE}_{TIMESTAMP}_{RANDOM}.{EXT}
```

Où:
- `REFERENCE` : Numéro de référence candidature (ex: MM251009ECLKUP)
- `TYPE` : Type de document (id_card, cfe_card, etc.)
- `TIMESTAMP` : Date/heure (YmdHis format)
- `RANDOM` : 12 caractères aléatoires
- `EXT` : Extension fichier (jpg, png, pdf)

---

## 🎯 Avantages

1. **📁 Traçabilité améliorée** : Identification immédiate de la candidature
2. **🔍 Recherche facilitée** : Filtrage des fichiers par référence dans le stockage
3. **💾 Backup simplifié** : Export/sauvegarde par candidature plus facile
4. **🔒 Audit** : Meilleure traçabilité pour les audits
5. **🗂️ Organisation** : Gestion des documents plus structurée

---

## 📂 Emplacement des fichiers

Les fichiers sont stockés dans:
```
storage/app/merchant-documents/{type}/{year}/{month}/{filename}
```

Exemple:
```
storage/app/merchant-documents/id_card/2025/11/MM251009ECLKUP_id_card_20251104172251_IIhdJG06o8nY.jpg
storage/app/merchant-documents/cfe_card/2025/11/MM251009ECLKUP_cfe_card_20251104172305_K7y09U9DvwsJ.pdf
```

---

## ✅ Tests effectués

- ✅ Vérification syntaxe PHP (DocumentStorageService.php)
- ✅ Vérification syntaxe PHP (MerchantApplicationController.php)
- ✅ Vérification syntaxe PHP (DocumentController.php)
- ✅ Test simulation du format de nommage

---

## 🚀 Prochaines étapes

Pour tester en conditions réelles:

1. **Redémarrer le serveur backend** (si nécessaire)
   ```bash
   cd backend
   php artisan serve
   ```

2. **Créer une nouvelle candidature** via le formulaire frontend

3. **Uploader des documents** pour cette candidature

4. **Vérifier** que les fichiers dans `storage/app/merchant-documents/` commencent bien par le numéro de référence

---

## 📝 Notes importantes

- ⚠️ **Rétrocompatibilité** : Les anciens fichiers (sans préfixe) restent valides et accessibles
- ⚠️ **Migration** : Les fichiers existants ne sont PAS renommés automatiquement
- ✅ **Nouveau comportement** : Seuls les NOUVEAUX uploads auront le préfixe
- ✅ **Paramètre optionnel** : Le préfixe est optionnel, donc aucun breaking change

---

Date de modification: 4 novembre 2025
