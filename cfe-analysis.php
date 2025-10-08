<?php

echo "=== Analyse validation CFE ===\n\n";

// Analysons les règles
echo "Règle CFE actuelle:\n";
echo "cfe_expiry_date => 'required_if:has_cfe,true|nullable|date|after:today'\n\n";

echo "Cette règle signifie:\n";
echo "1. SI has_cfe == true ALORS cfe_expiry_date est OBLIGATOIRE\n";
echo "2. Le format doit être une date valide\n";
echo "3. La date doit être APRÈS aujourd'hui\n\n";

echo "Valeurs qui activent required_if:has_cfe,true:\n";
echo "- true (boolean)\n";
echo "- 'true' (string)\n";
echo "- '1' (string)\n";
echo "- 1 (integer)\n";
echo "- 'yes', 'on'\n\n";

echo "=== Solutions possibles ===\n\n";

echo "Si vous voulez que CFE soit optionnel:\n";
echo "1. Assurez-vous que has_cfe = false ou null quand pas de CFE\n";
echo "2. Ou supprimez complètement les champs CFE du formulaire\n\n";

echo "Si vous voulez garder la validation conditionnelle:\n";
echo "1. Vérifiez que has_cfe est bien false quand checkbox non cochée\n";
echo "2. Si has_cfe = true, alors cfe_expiry_date doit être fournie et future\n\n";

echo "=== Debug des données reçues ===\n";
echo "Pour déboguer, ajoutez dans votre contrôleur:\n";
echo "dd(\$request->all()); // pour voir toutes les données\n";
echo "ou\n";
echo "Log::info('CFE Debug', ['has_cfe' => \$request->has_cfe, 'cfe_expiry_date' => \$request->cfe_expiry_date]);\n\n";

echo "=== Modification suggérée ===\n";
echo "Si le problème persiste, on peut assouplir la règle:\n";
echo "Remplacer: 'required_if:has_cfe,true|nullable|date|after:today'\n";
echo "Par:       'nullable|date|after:today'\n";
echo "Et gérer la validation côté frontend seulement.\n";