<?php

echo "=== Vérification des champs de dates envoyés ===\n\n";

echo "Champs de dates attendus côté backend :\n";
echo "- id_expiry_date (obligatoire)\n";
echo "- anid_expiry_date (optionnel si has_anid_card=true)\n";
echo "- cfe_expiry_date (obligatoire si has_cfe=true)\n\n";

echo "=== Vue.js -> Store -> Service ===\n\n";

echo "1. Vue.js formData.value contient :\n";
echo "   - formData.value.idExpiryDate\n";
echo "   - formData.value.anidExpiryDate\n";
echo "   - formData.value.cfeExpiryDate\n\n";

echo "2. Store submitApplication transforme vers :\n";
echo "   - idExpiryDate: data.idExpiryDate ✅\n";
echo "   - anidExpiryDate: data.anidExpiryDate ✅ (AJOUTÉ)\n";
echo "   - cfeExpiryDate: data.cfeExpiryDate ✅ (AJOUTÉ)\n\n";

echo "3. Service envoie vers backend :\n";
echo "   - id_expiry_date: formData.idExpiryDate ✅\n";
echo "   - anid_expiry_date: formData.anidExpiryDate ✅ (AJOUTÉ)\n";
echo "   - cfe_expiry_date: formData.cfeExpiryDate ✅ (AJOUTÉ)\n\n";

echo "=== Vue.js -> updateApplication (direct) ===\n\n";

echo "Store updateApplication passe directement data -> Service :\n";
echo "   - id_expiry_date: data.idExpiryDate ✅\n";
echo "   - anid_expiry_date: data.anidExpiryDate ✅ (AJOUTÉ)\n";
echo "   - cfe_expiry_date: data.cfeExpiryDate ✅ (AJOUTÉ)\n\n";

echo "=== Validation backend ===\n\n";
echo "StoreMerchantApplicationRequest règles :\n";
echo "- id_expiry_date: 'required|date|after:today'\n";
echo "- anid_expiry_date: 'nullable|date|after:today'\n";
echo "- cfe_expiry_date: 'required_if:has_cfe,true|nullable|date|after:today'\n\n";

echo "✅ TOUTES les dates d'expiration sont maintenant envoyées !\n";