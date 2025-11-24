<template>
  <div class="bg-gray-200 px-4 sm:px-6 lg:px-8">
    <div class="max-w-md w-full mx-auto main-content">
      <!-- Logo et titre -->
      <div class="text-center mb-8 pt-12">
        <img class="mx-auto h-16 w-auto" src="/logo.png" alt="Moov Money" />
        <h2 class="mt-6 text-3xl font-bold text-gray-900">
          Connexion à l'administration
        </h2>
      </div>

      <!-- Formulaire -->
      <div class="bg-white py-8 px-4 shadow-lg sm:rounded-lg sm:px-10">
        <form @submit.prevent="handleSubmit" class="space-y-6">
          <!-- Message d'erreur -->
          <div v-if="authStore.error" class="bg-red-50 border-l-4 border-red-500 p-4 mb-6">
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

          <!-- Username -->
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

          <div>
            <button
              type="submit"
              class="h-12 w-full rounded-xl bg-[#EC6707] text-white font-semibold hover:bg-orange-600 focus:outline-none focus:ring-2 focus:ring-orange-500 focus:ring-offset-2 disabled:opacity-50 transition-colors"
              :disabled="authStore.loading"
            >
              <i v-if="authStore.loading" class="fas fa-spinner fa-spin mr-2"></i>
              <span>{{ authStore.loading ? 'Connexion...' : 'Se connecter' }}</span>
            </button>
          </div>
        </form>

        <!-- Lien vers le formulaire marchand -->
        <div class="mt-6">
          <div class="relative">
            <div class="absolute inset-0 flex items-center">
              <div class="w-full border-t border-gray-300"></div>
            </div>
            <div class="relative flex justify-center text-sm">
              <span class="px-2 bg-white text-gray-500">Retourner à</span>
            </div>
          </div>
          <div class="mt-2 text-center">
            <router-link
              to="/"
              class="font-medium text-orange-600 hover:text-orange-500"
            >
              Formulaire marchand
            </router-link>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, reactive } from 'vue'
import { useRouter } from 'vue-router'
import { useAuthStore } from '../stores/auth'
import { useVuelidate } from '@vuelidate/core'
import { required, minLength } from '@vuelidate/validators'

const router = useRouter()
const authStore = useAuthStore()
const showPassword = ref(false)

const form = reactive({
  username: '',
  password: ''
})

const rules = {
  username: { required, minLength: minLength(4) },
  password: { required, minLength: minLength(4) }
}

const v$ = useVuelidate(rules, form)

const handleSubmit = async () => {
  const isValid = await v$.value.$validate()
  if (!isValid) return

  try {
    const loginResult = await authStore.login(form.username, form.password)
    
    // Si l'utilisateur doit changer son mot de passe, rediriger vers la page dédiée
    if (loginResult.must_change_password) {
      router.push('/change-password-required')
    } else {
      // Sinon, rediriger vers le dashboard
      router.push('/dashboard')
    }
  } catch (error) {
    // Erreur déjà gérée par le store
    console.error('Erreur de connexion:', error)
  }
}
</script>