# 📝 Guide d'Intégration - Validation Temps Réel

## Vue d'ensemble

Le système de validation temps réel se compose de 3 éléments :

1. **`useValidation`** - Composable de validation avec états et validateurs
2. **`ValidatedInput`** - Composant input avec validation intégrée
3. **`CompletionIndicator`** - Indicateur de progression du formulaire

---

## 🚀 Intégration dans MerchantForm.vue

### Étape 1 : Importer les dépendances

```vue
<script>
import { ref } from 'vue'
import { useValidation } from '../composables/useValidation'
import ValidatedInput from '../components/ValidatedInput.vue'
import CompletionIndicator from '../components/CompletionIndicator.vue'

export default {
  components: {
    ValidatedInput,
    CompletionIndicator
  },
  setup() {
    // Initialiser la validation
    const { 
      validateEmail, 
      validatePhone, 
      validateRequired,
      validateMinAge,
      completionScore,
      validationStats
    } = useValidation()

    // Données du formulaire
    const formData = ref({
      firstName: '',
      lastName: '',
      email: '',
      personalPhone: '',
      birthDate: ''
    })

    return {
      formData,
      validateEmail,
      validatePhone,
      validateRequired,
      validateMinAge,
      completionScore,
      validationStats
    }
  }
}
</script>
```

---

### Étape 2 : Ajouter le CompletionIndicator

```vue
<template>
  <!-- En haut du formulaire -->
  <CompletionIndicator
    :score="completionScore"
    :stats="validationStats"
    :show-details="true"
  />
</template>
```

---

### Étape 3 : Remplacer les inputs classiques

#### Avant (Input classique)

```vue
<div class="form-group">
  <label class="form-label">Email</label>
  <input 
    v-model="formData.email" 
    type="email" 
    class="form-input h-12"
    :class="{ 'border-red-500': errors.email }"
    placeholder="email@exemple.com"
    autocomplete="email"
    inputmode="email"
  />
  <p v-if="errors.email" class="mt-1 text-sm text-red-600">
    {{ errors.email }}
  </p>
</div>
```

#### Après (ValidatedInput)

```vue
<ValidatedInput
  v-model="formData.email"
  field-name="email"
  label="Email"
  type="email"
  placeholder="email@exemple.com"
  autocomplete="email"
  inputmode="email"
  :validation-fn="validateEmail"
  help-text="Votre adresse email professionnelle"
/>
```

---

## 📋 Exemples pour chaque type de champ

### 1. Email

```vue
<ValidatedInput
  v-model="formData.email"
  field-name="email"
  label="Email"
  type="email"
  inputmode="email"
  autocomplete="email"
  :validation-fn="validateEmail"
  help-text="Format: user@example.com"
/>
```

---

### 2. Téléphone (Togo)

```vue
<ValidatedInput
  v-model="formData.personalPhone"
  field-name="personalPhone"
  label="Téléphone personnel"
  type="tel"
  inputmode="tel"
  autocomplete="tel"
  :validation-fn="validatePhone"
  :required="true"
  help-text="Format: 90123456 ou +228 90123456"
/>
```

---

### 3. Nom / Prénom

```vue
<ValidatedInput
  v-model="formData.firstName"
  field-name="firstName"
  label="Prénom(s)"
  type="text"
  inputmode="text"
  autocomplete="given-name"
  :validation-fn="(value) => validateRequired(value, 'Le prénom')"
  :required="true"
/>
```

---

### 4. Date de naissance (avec validation âge)

```vue
<ValidatedInput
  v-model="formData.birthDate"
  field-name="birthDate"
  label="Date de naissance"
  type="date"
  autocomplete="bday"
  :validation-fn="(value) => validateMinAge(value, 18)"
  :required="true"
  help-text="Vous devez avoir au moins 18 ans"
/>
```

---

### 5. NIF (avec validation API)

```vue
<script setup>
// Validation NIF avec appel API
const validateNIFWithAPI = async (nif) => {
  if (!nif || nif.length < 8) {
    return { valid: false, message: 'NIF trop court (min 8 caractères)' }
  }

  try {
    // Appel API pour vérifier le NIF
    const response = await fetch(`/api/validate-nif/${nif}`)
    const data = await response.json()
    
    if (data.valid) {
      return { valid: true, message: 'NIF valide et reconnu' }
    } else {
      return { valid: false, message: data.message || 'NIF non reconnu' }
    }
  } catch (error) {
    return { valid: false, message: 'Erreur de vérification' }
  }
}
</script>

<template>
  <ValidatedInput
    v-model="formData.nifNumber"
    field-name="nifNumber"
    label="Numéro NIF"
    type="text"
    inputmode="numeric"
    autocomplete="off"
    :validation-fn="validateNIFWithAPI"
    :debounce-delay="1000"
    help-text="Vérification automatique auprès de l'OTR"
  />
</template>
```

---

### 6. Champ avec validation personnalisée

```vue
<script setup>
// Validation personnalisée pour nom commercial
const validateBusinessName = (name) => {
  if (!name || name.trim() === '') {
    return { valid: false, message: 'Nom commercial requis' }
  }

  if (name.length < 3) {
    return { valid: false, message: 'Minimum 3 caractères' }
  }

  if (name.length > 100) {
    return { valid: false, message: 'Maximum 100 caractères' }
  }

  // Vérifier qu'il n'y a pas que des chiffres
  if (/^\d+$/.test(name)) {
    return { valid: false, message: 'Le nom ne peut pas contenir uniquement des chiffres' }
  }

  return { valid: true, message: 'Nom commercial valide' }
}
</script>

<template>
  <ValidatedInput
    v-model="formData.businessName"
    field-name="businessName"
    label="Nom commercial"
    type="text"
    inputmode="text"
    autocomplete="organization"
    :validation-fn="validateBusinessName"
    :required="true"
    :maxlength="100"
    help-text="Nom de votre commerce ou entreprise"
  />
</template>
```

---

## 🎨 Personnalisation

### Désactiver la validation automatique

```vue
<ValidatedInput
  v-model="formData.field"
  field-name="field"
  :validate-on-input="false"
  :validate-on-blur="true"
  <!-- Validation uniquement au blur -->
/>
```

---

### Changer le délai de debounce

```vue
<ValidatedInput
  v-model="formData.field"
  field-name="field"
  :debounce-delay="1000"
  <!-- Attendre 1 seconde après la dernière saisie -->
/>
```

---

### Masquer l'icône dans l'input

```vue
<ValidatedInput
  v-model="formData.field"
  field-name="field"
  :show-icon-in-input="false"
  <!-- Icône uniquement dans le label -->
/>
```

---

### Écouter les changements de validation

```vue
<ValidatedInput
  v-model="formData.email"
  field-name="email"
  @validation-change="handleEmailValidation"
/>

<script setup>
const handleEmailValidation = ({ fieldName, state, message, isValid }) => {
  console.log(`${fieldName}: ${state}`, message)
  
  if (isValid) {
    // Email valide, on peut faire quelque chose
    checkEmailDuplicate(formData.email)
  }
}
</script>
```

---

## 🔥 Migration Complète d'un Formulaire

### Étape 1 : Identifier tous les inputs

```bash
# Chercher tous les inputs dans MerchantForm.vue
grep -n "<input" frontend/src/views/MerchantForm.vue
```

---

### Étape 2 : Créer un plan de migration

| Champ | Type | Validateur | Priorité |
|-------|------|------------|----------|
| lastName | text | validateRequired | 🔴 Haute |
| firstName | text | validateRequired | 🔴 Haute |
| birthDate | date | validateMinAge(18) | 🔴 Haute |
| email | email | validateEmail | 🟠 Moyenne |
| personalPhone | tel | validatePhone | 🔴 Haute |
| nifNumber | text | validateNIF | 🟠 Moyenne |
| cfeNumber | text | validateCFE | 🟠 Moyenne |
| businessName | text | validateBusinessName | 🔴 Haute |

---

### Étape 3 : Migrer par étape du formulaire

```vue
<!-- Étape 1: Informations personnelles -->
<template v-if="currentStep === 1">
  <div class="form-section">
    <h2 class="section-title">
      <i class="fas fa-user-circle text-orange-500 mr-2"></i>
      Informations Personnelles
    </h2>

    <div class="grid grid-cols-1 md:grid-cols-2 gap-4 sm:gap-6">
      <ValidatedInput
        v-model="formData.lastName"
        field-name="lastName"
        label="Nom"
        type="text"
        inputmode="text"
        autocomplete="family-name"
        :validation-fn="(value) => validateRequired(value, 'Le nom')"
        :required="true"
      />

      <ValidatedInput
        v-model="formData.firstName"
        field-name="firstName"
        label="Prénom(s)"
        type="text"
        inputmode="text"
        autocomplete="given-name"
        :validation-fn="(value) => validateRequired(value, 'Le prénom')"
        :required="true"
      />

      <ValidatedInput
        v-model="formData.birthDate"
        field-name="birthDate"
        label="Date de naissance"
        type="date"
        autocomplete="bday"
        :validation-fn="(value) => validateMinAge(value, 18)"
        :required="true"
        help-text="Vous devez avoir au moins 18 ans"
      />

      <ValidatedInput
        v-model="formData.email"
        field-name="email"
        label="Email"
        type="email"
        inputmode="email"
        autocomplete="email"
        :validation-fn="validateEmail"
        help-text="Votre adresse email professionnelle"
      />

      <ValidatedInput
        v-model="formData.personalPhone"
        field-name="personalPhone"
        label="Téléphone personnel"
        type="tel"
        inputmode="tel"
        autocomplete="tel"
        :validation-fn="validatePhone"
        :required="true"
        help-text="Format: 90123456"
      />
    </div>
  </div>
</template>
```

---

## 🎯 Recommandations

### 1. Valider progressivement
Migrer étape par étape (Step 1, puis Step 2, etc.) plutôt que tout d'un coup.

### 2. Tester chaque champ
Vérifier que la validation fonctionne pour chaque type de données.

### 3. Adapter les validateurs
Personnaliser les validateurs selon les règles métier spécifiques.

### 4. Gérer les erreurs
Prévoir des messages d'erreur clairs et en français.

### 5. Optimiser le debounce
- Champs simples: 500ms
- Validation API: 1000-2000ms
- Validation au blur uniquement pour champs sensibles

---

## 📱 Support Mobile

Tous les composants sont optimisés pour mobile :

- ✅ Touch targets 44px minimum
- ✅ Inputmode approprié pour clavier mobile
- ✅ Autocomplete pour suggestions
- ✅ Animations performantes (GPU)
- ✅ Dark mode intégré
- ✅ Responsive design

---

## 🐛 Debugging

### Afficher l'état de validation

```vue
<template>
  <!-- Dev only -->
  <pre v-if="isDev">{{ validationStats }}</pre>
  <pre v-if="isDev">Score: {{ completionScore }}%</pre>
</template>

<script setup>
const isDev = import.meta.env.DEV
</script>
```

---

### Logger les validations

```vue
<ValidatedInput
  v-model="formData.field"
  field-name="field"
  @validation-change="console.log"
/>
```

---

## 🚀 Prochaines Étapes

1. ✅ Migrer Step 1 (Informations personnelles)
2. ⏳ Migrer Step 2 (Documents)
3. ⏳ Migrer Step 3 (Informations commerciales)
4. ⏳ Connecter validations NIF/CFE au backend
5. ⏳ Ajouter détection de doublons téléphone
6. ⏳ Implémenter suggestions auto (format, etc.)

---

**Besoin d'aide ?** Consulter `useValidation.js` pour la liste complète des validateurs disponibles.
