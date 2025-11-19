<script setup>
import { ref, reactive } from 'vue'
import { useRouter } from 'vue-router'
import { useAuthStore } from '../stores/auth'
import { useNotificationStore } from '../stores/notifications'
import { useVuelidate } from '@vuelidate/core'
import { required, email, minLength, helpers } from '@vuelidate/validators'
import PhoneInput from '../components/PhoneInput.vue'

const router = useRouter()
const authStore = useAuthStore()
const notificationStore = useNotificationStore()
const showPassword = ref(false)

const form = reactive({
  first_name: '',
  last_name: '',
  email: '',
  phone: '',
  username: '',
  password: '',
  role_slug: ''
})

const rules = {
  first_name: { required: helpers.withMessage('Le prénom est requis', required) },
  last_name: { required: helpers.withMessage('Le nom est requis', required) },
  email: { 
    required: helpers.withMessage('L\'email est requis', required),
    email: helpers.withMessage('L\'email n\'est pas valide', email)
  },
  phone: { required: helpers.withMessage('Le téléphone est requis', required) },
  username: { 
    required: helpers.withMessage('Le nom d\'utilisateur est requis', required),
    minLength: helpers.withMessage('Le nom d\'utilisateur doit faire au moins 4 caractères', minLength(4))
  },
  password: { 
    required: helpers.withMessage('Le mot de passe est requis', required),
    minLength: helpers.withMessage('Le mot de passe doit faire au moins 6 caractères', minLength(6))
  },
  role_slug: { required: helpers.withMessage('Le rôle est requis', required) }
}

const v$ = useVuelidate(rules, form)

const handleSubmit = async () => {
  const isValid = await v$.value.$validate()
  if (!isValid) {
    notificationStore.error(
      'Formulaire invalide',
      'Veuillez corriger les erreurs dans le formulaire avant de continuer.'
    )
    return
  }

  try {
    const response = await authStore.register(form)
    
    // Afficher la notification de succès
    notificationStore.success(
      'Utilisateur créé avec succès',
      `${form.first_name} ${form.last_name} a été créé avec le rôle ${form.role_slug === 'admin' ? 'Administrateur' : 'Commercial'}. L'utilisateur devra changer son mot de passe lors de sa première connexion.`
    )
    
    // Réinitialiser le formulaire
    Object.keys(form).forEach(key => {
      form[key] = ''
    })
    v$.value.$reset()
    
    // Rediriger après un court délai pour que l'utilisateur voie la notification
    setTimeout(() => {
      router.push('/dashboard')
    }, 2000)
    
  } catch (error) {
    console.error('Erreur de création:', error)
    
    // Construire un message d'erreur détaillé
    let errorMessage = 'Une erreur est survenue lors de la création de l\'utilisateur.'
    
    if (error.response?.data?.errors) {
      const errors = error.response.data.errors
      const errorMessages = []
      
      if (errors.email) errorMessages.push('Email: ' + errors.email[0])
      if (errors.phone) errorMessages.push('Téléphone: ' + errors.phone[0])
      if (errors.username) errorMessages.push('Nom d\'utilisateur: ' + errors.username[0])
      if (errors.password) errorMessages.push('Mot de passe: ' + errors.password[0])
      if (errors.first_name) errorMessages.push('Prénom: ' + errors.first_name[0])
      if (errors.last_name) errorMessages.push('Nom: ' + errors.last_name[0])
      if (errors.role_slug) errorMessages.push('Rôle: ' + errors.role_slug[0])
      
      if (errorMessages.length > 0) {
        errorMessage = errorMessages.join(' • ')
      }
    } else if (error.response?.data?.message) {
      errorMessage = error.response.data.message
    } else if (authStore.error) {
      errorMessage = authStore.error
    }
    
    // Afficher la notification d'erreur
    notificationStore.error(
      'Erreur de création',
      errorMessage
    )
  }
}
</script>

<template>
  <div class="min-h-screen bg-gray-50 py-12 px-4 sm:px-6 lg:px-8">
    <div class="max-w-2xl w-full mx-auto">
      <!-- En-tête -->
      <div class="text-center mb-8">
        <h2 class="text-3xl font-bold text-gray-900">
          Créer un nouvel utilisateur
        </h2>
        <p class="mt-2 text-gray-600">
          Remplissez les informations ci-dessous pour créer un compte utilisateur
        </p>
      </div>

      <!-- Formulaire -->
      <div class="bg-white py-8 px-4 shadow-lg sm:rounded-lg sm:px-10">
        <form @submit.prevent="handleSubmit" class="space-y-6">
          <!-- Message d'erreur global -->
          <div v-if="authStore.error" class="bg-red-50 border-l-4 border-red-500 p-4">
            <div class="flex">
              <div class="flex-shrink-0">
                <i class="fas fa-exclamation-circle text-red-500"></i>
              </div>
              <div class="ml-3">
                <p class="text-sm text-red-700">
                  {{ authStore.error }}
                </p>
              </div>
            </div>
          </div>

          <!-- Grille 2 colonnes -->
          <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <!-- Prénom -->
            <div>
              <label for="firstName" class="block text-sm font-medium text-gray-700">
                Prénom
              </label>
              <div class="mt-1">
                <input
                  id="firstName"
                  v-model="form.first_name"
                  type="text"
                  required
                  class="form-input h-12 block w-full"
                  :class="{ 'border-red-500': v$.first_name.$error }"
                  @blur="v$.first_name.$touch()"
                />
                <p v-if="v$.first_name.$error" class="mt-1 text-sm text-red-600">
                  {{ v$.first_name.$errors[0].$message }}
                </p>
              </div>
            </div>

            <!-- Nom -->
            <div>
              <label for="lastName" class="block text-sm font-medium text-gray-700">
                Nom
              </label>
              <div class="mt-1">
                <input
                  id="lastName"
                  v-model="form.last_name"
                  type="text"
                  required
                  class="form-input h-12 block w-full"
                  :class="{ 'border-red-500': v$.last_name.$error }"
                />
                <p v-if="v$.last_name.$error" class="mt-1 text-sm text-red-600">
                  {{ v$.last_name.$errors[0].$message }}
                </p>
              </div>
            </div>

            <!-- Email -->
            <div>
              <label for="email" class="block text-sm font-medium text-gray-700">
                Email
              </label>
              <div class="mt-1">
                <input
                  id="email"
                  v-model="form.email"
                  type="email"
                  required
                  class="form-input h-12 block w-full"
                  :class="{ 'border-red-500': v$.email.$error }"
                />
                <p v-if="v$.email.$error" class="mt-1 text-sm text-red-600">
                  {{ v$.email.$errors[0].$message }}
                </p>
              </div>
            </div>

            <!-- Téléphone -->
            <div>
              <label for="phone" class="block text-sm font-medium text-gray-700">
                Téléphone
              </label>
              <div class="mt-1">
                <PhoneInput
                  v-model="form.phone"
                  required
                  :class="{ 'border-red-500': v$.phone.$error }"
                />
                <p v-if="v$.phone.$error" class="mt-1 text-sm text-red-600">
                  {{ v$.phone.$errors[0].$message }}
                </p>
              </div>
            </div>

            <!-- Nom d'utilisateur -->
            <div>
              <label for="username" class="block text-sm font-medium text-gray-700">
                Nom d'utilisateur
              </label>
              <div class="mt-1">
                <input
                  id="username"
                  v-model="form.username"
                  type="text"
                  required
                  class="form-input h-12 block w-full"
                  :class="{ 'border-red-500': v$.username.$error }"
                />
                <p v-if="v$.username.$error" class="mt-1 text-sm text-red-600">
                  {{ v$.username.$errors[0].$message }}
                </p>
              </div>
            </div>

            <!-- Mot de passe -->
            <div>
              <label for="password" class="block text-sm font-medium text-gray-700">
                Mot de passe
              </label>
              <div class="mt-1 relative">
                <input
                  id="password"
                  v-model="form.password"
                  :type="showPassword ? 'text' : 'password'"
                  required
                  class="form-input h-12 block w-full pr-10"
                  :class="{ 'border-red-500': v$.password.$error }"
                />
                <button
                  type="button"
                  @click="showPassword = !showPassword"
                  class="absolute inset-y-0 right-0 pr-3 flex items-center"
                >
                  <i :class="[
                    'fas',
                    showPassword ? 'fa-eye-slash' : 'fa-eye',
                    'text-gray-400 hover:text-gray-500'
                  ]"></i>
                </button>
                <p v-if="v$.password.$error" class="mt-1 text-sm text-red-600">
                  {{ v$.password.$errors[0].$message }}
                </p>
              </div>
            </div>
          </div>

          <!-- Rôle -->
          <div>
            <label class="block text-sm font-medium text-gray-700">
              Rôle
            </label>
            <div class="mt-1">
              <select
                v-model="form.role_slug"
                required
                class="form-input h-12 block w-full"
                :class="{ 'border-red-500': v$.role_slug.$error }"
              >
                <option value="">Sélectionnez un rôle</option>
                <option value="admin">Administrateur</option>
                <option value="commercial">Commercial</option>
              </select>
              <p v-if="v$.role_slug.$error" class="mt-1 text-sm text-red-600">
                {{ v$.role.$errors[0].$message }}
              </p>
            </div>
          </div>

          <!-- Boutons d'action -->
          <div class="flex space-x-4">
            <router-link
              to="/dashboard"
              class="h-12 flex-1 rounded-xl border border-gray-300 bg-white text-gray-700 font-semibold hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-orange-500 focus:ring-offset-2 transition-colors flex items-center justify-center"
            >
              Annuler
            </router-link>
            <button
              type="submit"
              class="h-12 flex-1 rounded-xl bg-[#EC6707] text-white font-semibold hover:bg-orange-600 focus:outline-none focus:ring-2 focus:ring-orange-500 focus:ring-offset-2 disabled:opacity-50 transition-colors flex items-center justify-center"
              :disabled="authStore.loading"
            >
              <i v-if="authStore.loading" class="fas fa-spinner fa-spin mr-2"></i>
              <span>{{ authStore.loading ? 'Création...' : 'Créer l\'utilisateur' }}</span>
            </button>
          </div>
        </form>
      </div>
    </div>
  </div>
</template>