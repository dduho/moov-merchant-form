<template>
  <div class="bg-gray-200 px-4 sm:px-6 lg:px-8 min-h-screen flex items-center">
    <div class="max-w-md w-full mx-auto main-content">
      <!-- Logo et titre -->
      <div class="text-center mb-8 pt-2">
        <img class="mx-auto h-16 w-auto" src="/logo.png" alt="Moov Money" />
        <h2 class="mt-6 text-3xl font-bold text-gray-900">
          Changement de mot de passe requis
        </h2>
        <p class="mt-2 text-sm text-gray-600">
          Pour des raisons de sécurité, vous devez changer votre mot de passe avant de continuer.
        </p>
      </div>

      <!-- Formulaire -->
      <div class="bg-white py-8 px-4 shadow-lg sm:rounded-lg sm:px-10">
        <form @submit.prevent="handleSubmit" class="space-y-6">
          <!-- Message d'erreur -->
          <div v-if="error" class="bg-red-50 border-l-4 border-red-500 p-4 mb-6">
            <div class="flex">
              <div class="flex-shrink-0">
                <i class="fas fa-exclamation-circle text-red-500"></i>
              </div>
              <div class="ml-3">
                <p class="text-sm text-red-700">
                  {{ error }}
                </p>
              </div>
            </div>
          </div>

          <!-- Nouveau mot de passe -->
          <div>
            <label for="new_password" class="block text-sm font-medium text-gray-700">
              Nouveau mot de passe
            </label>
            <div class="mt-1">
              <input
                id="new_password"
                v-model="form.new_password"
                type="password"
                required
                class="form-input h-12 block w-full"
                :class="{ 'border-red-500': v$.new_password.$error }"
                placeholder="Au moins 6 caractères"
              />
              <p v-if="v$.new_password.$error" class="mt-1 text-sm text-red-600">
                {{ v$.new_password.$errors[0].$message }}
              </p>
            </div>
          </div>

          <!-- Confirmation du mot de passe -->
          <div>
            <label for="new_password_confirmation" class="block text-sm font-medium text-gray-700">
              Confirmer le mot de passe
            </label>
            <div class="mt-1">
              <input
                id="new_password_confirmation"
                v-model="form.new_password_confirmation"
                type="password"
                required
                class="form-input h-12 block w-full"
                :class="{ 'border-red-500': v$.new_password_confirmation.$error }"
                placeholder="Répétez le mot de passe"
              />
              <p v-if="v$.new_password_confirmation.$error" class="mt-1 text-sm text-red-600">
                {{ v$.new_password_confirmation.$errors[0].$message }}
              </p>
            </div>
          </div>

          <!-- Bouton de soumission -->
          <div>
            <button
              type="submit"
              :disabled="loading"
              class="group relative w-full flex justify-center py-3 px-4 border border-transparent text-sm font-medium rounded-md text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 disabled:opacity-50 disabled:cursor-not-allowed"
            >
              <span v-if="loading" class="absolute left-1/2 transform -translate-x-1/2">
                <i class="fas fa-spinner fa-spin"></i>
              </span>
              <span v-else>
                Changer le mot de passe
              </span>
            </button>
          </div>
        </form>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, reactive, computed } from 'vue'
import { useRouter } from 'vue-router'
import { useAuthStore } from '../stores/auth'
import useVuelidate from '@vuelidate/core'
import { required, minLength, sameAs } from '@vuelidate/validators'
import axios from 'axios'

const router = useRouter()
const authStore = useAuthStore()

const loading = ref(false)
const error = ref('')

const form = reactive({
  new_password: '',
  new_password_confirmation: ''
})

const rules = {
  new_password: { required, minLength: minLength(6) },
  new_password_confirmation: { required, sameAsPassword: sameAs(computed(() => form.new_password)) }
}

const v$ = useVuelidate(rules, form)

const handleSubmit = async () => {
  const isValid = await v$.value.$validate()
  if (!isValid) return

  loading.value = true
  error.value = ''

  try {
    // Obtenir le CSRF cookie
    await axios.get('/sanctum/csrf-cookie')

    // Faire la requête de changement de mot de passe
    const response = await axios.post('/api/auth/change-password-required', {
      new_password: form.new_password,
      new_password_confirmation: form.new_password_confirmation
    })

    // Mettre à jour l'utilisateur dans le store
    await authStore.fetchUser()

    // Rediriger vers le dashboard
    router.push({ name: 'Dashboard' })
  } catch (err) {
    error.value = err.response?.data?.message || 'Une erreur est survenue'
  } finally {
    loading.value = false
  }
}
</script>