# Test - Préfixe des noms de fichiers avec référence de candidature

## Modifications effectuées

### 1. DocumentStorageService.php
- **Méthode `store()`** : Ajout d'un paramètre optionnel `$referencePrefix`
- **Méthode `generateSecureFilename()`** : Ajout du préfixe au début du nom de fichier si fourni

### 2. MerchantApplicationController.php
- **Méthode `storeDocument()`** : Passage du `reference_number` de l'application au service de stockage

### 3. DocumentController.php
- **Méthode `upload()`** : Récupération du `reference_number` depuis l'application associée et passage au service

## Format des noms de fichiers

### Avant
```
id_card_20251104143025_a1b2c3d4e5f6.jpg
cfe_document_20251104143030_x7y8z9w0v1u2.pdf
```

### Après
```
MM251009ECLKUP_id_card_20251104143025_a1b2c3d4e5f6.jpg
MM251009ECLKUP_cfe_document_20251104143030_x7y8z9w0v1u2.pdf
```

## Structure
```
{reference_number}_{document_type}_{timestamp}_{random}.{extension}
```

Où :
- `reference_number` : Numéro de référence de la candidature (ex: MM251009ECLKUP)
- `document_type` : Type de document (id_card, cfe_document, etc.)
- `timestamp` : Horodatage au format YmdHis (ex: 20251104143025)
- `random` : Chaîne aléatoire de 12 caractères pour l'unicité
- `extension` : Extension du fichier original (jpg, png, pdf)

## Test manuel

Pour tester, vous pouvez :

1. Créer une nouvelle candidature via le formulaire
2. Uploader des documents
3. Vérifier dans `storage/app/merchant-documents/{type}/{year}/{month}/` que les fichiers commencent bien par le numéro de référence

Ou via l'API :

```bash
# 1. Créer/Récupérer une candidature
curl http://localhost:8000/api/merchant-applications/{id}

# 2. Uploader un document
curl -X POST http://localhost:8000/api/documents/upload \
  -F "file=@test.jpg" \
  -F "type=id_card" \
  -F "merchant_application_id={id}"

# 3. Vérifier le nom du fichier dans la réponse
```

## Bénéfices

1. **Traçabilité** : Identification immédiate de la candidature associée au fichier
2. **Organisation** : Facilite la recherche et le tri des fichiers dans le système de stockage
3. **Backup/Export** : Simplifie les opérations de sauvegarde et d'export par candidature
4. **Audit** : Améliore la traçabilité pour les audits
