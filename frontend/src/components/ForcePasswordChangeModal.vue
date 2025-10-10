<template>
  <div v-if="show" class="fixed inset-0 z-50 overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
    <!-- Overlay -->
    <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
      <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" aria-hidden="true"></div>
      
      <!-- Spacer for centering -->
      <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>
      
      <!-- Modal panel -->
      <div class="relative inline-block align-bottom bg-white rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full" @click.stop>
        <div class="bg-white px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
          <div class="sm:flex sm:items-start">
            <div class="mx-auto flex-shrink-0 flex items-center justify-center h-12 w-12 rounded-full bg-orange-100 sm:mx-0 sm:h-10 sm:w-10">
              <i class="fas fa-key text-orange-600"></i>
            </div>
            <div class="mt-3 text-center sm:mt-0 sm:ml-4 sm:text-left flex-1">
              <h3 class="text-lg leading-6 font-medium text-gray-900" id="modal-title">
                Changement de mot de passe requis
              </h3>
              <div class="mt-2">
                <p class="text-sm text-gray-500 mb-4">
                  Pour des raisons de sécurité, vous devez changer votre mot de passe avant de continuer.
                </p>
                
                <form @submit.prevent="submitPasswordChange" class="space-y-4">
                  <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">
                      Mot de passe actuel *
                    </label>
                    <div class="relative">
                      <input
                        v-model="form.currentPassword"
                        :type="showCurrentPassword ? 'text' : 'password'"
                        class="block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-orange-500 focus:border-orange-500"
                        :class="{ 'border-red-500': errors.currentPassword }"
                        required
                      >
                      <button
                        type="button"
                        @click="showCurrentPassword = !showCurrentPassword"
                        class="absolute inset-y-0 right-0 pr-3 flex items-center"
                      >
                        <i :class="showCurrentPassword ? 'fas fa-eye-slash' : 'fas fa-eye'" class="text-gray-400"></i>
                      </button>
                    </div>
                    <p v-if="errors.currentPassword" class="mt-1 text-sm text-red-600">
                      {{ errors.currentPassword }}
                    </p>
                  </div>
                  
                  <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">
                      Nouveau mot de passe *
                    </label>
                    <div class="relative">
                      <input
                        v-model="form.newPassword"
                        :type="showNewPassword ? 'text' : 'password'"
                        class="block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-orange-500 focus:border-orange-500"
                        :class="{ 'border-red-500': errors.newPassword }"
                        minlength="6"
                        required
                      >
                      <button
                        type="button"
                        @click="showNewPassword = !showNewPassword"
                        class="absolute inset-y-0 right-0 pr-3 flex items-center"
                      >
                        <i :class="showNewPassword ? 'fas fa-eye-slash' : 'fas fa-eye'" class="text-gray-400"></i>
                      </button>
                    </div>
                    <p class="mt-1 text-xs text-gray-500">
                      Minimum 6 caractères
                    </p>
                    <p v-if="errors.newPassword" class="mt-1 text-sm text-red-600">
                      {{ errors.newPassword }}
                    </p>
                  </div>
                  
                  <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">
                      Confirmer le nouveau mot de passe *
                    </label>
                    <div class="relative">
                      <input
                        v-model="form.confirmPassword"
                        :type="showConfirmPassword ? 'text' : 'password'"
                        class="block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-orange-500 focus:border-orange-500"
                        :class="{ 'border-red-500': errors.confirmPassword }"
                        required
                      >
                      <button
                        type="button"
                        @click="showConfirmPassword = !showConfirmPassword"
                        class="absolute inset-y-0 right-0 pr-3 flex items-center"
                      >
                        <i :class="showConfirmPassword ? 'fas fa-eye-slash' : 'fas fa-eye'" class="text-gray-400"></i>
                      </button>
                    </div>
                    <p v-if="errors.confirmPassword" class="mt-1 text-sm text-red-600">
                      {{ errors.confirmPassword }}
                    </p>
                  </div>
                  
                  <!-- Error général -->
                  <div v-if="generalError" class="bg-red-50 border border-red-200 rounded-md p-3">
                    <div class="flex">
                      <i class="fas fa-exclamation-circle text-red-400 mr-2 mt-0.5"></i>
                      <p class="text-sm text-red-800">{{ generalError }}</p>
                    </div>
                  </div>
                  
                  <div class="flex justify-end space-x-3 pt-4">
                    <button
                      type="submit"
                      :disabled="isSubmitting"
                      class="inline-flex justify-center px-4 py-2 text-sm font-medium text-white bg-orange-600 border border-transparent rounded-md shadow-sm hover:bg-orange-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-orange-500 disabled:opacity-50 disabled:cursor-not-allowed"
                    >
                      <i v-if="isSubmitting" class="fas fa-spinner fa-spin mr-2"></i>
                      {{ isSubmitting ? 'Modification...' : 'Changer le mot de passe' }}
                    </button>
                  </div>
                </form>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
import { ref, computed, watch } from 'vue'
import { useAuthStore } from '../stores/auth'
import { useNotificationStore } from '../stores/notifications'
import UserService from '../services/UserService'

export default {
  name: 'ForcePasswordChangeModal',
  props: {
    show: {
      type: Boolean,
      default: false
    }
  },
  emits: ['close', 'success'],
  setup(props, { emit }) {
    const authStore = useAuthStore()
    const notificationStore = useNotificationStore()
    
    const form = ref({
      currentPassword: '',
      newPassword: '',
      confirmPassword: ''
    })
    
    const errors = ref({})
    const generalError = ref('')
    const isSubmitting = ref(false)
    
    // Show/hide password toggles
    const showCurrentPassword = ref(false)
    const showNewPassword = ref(false)
    const showConfirmPassword = ref(false)
    
    // Computed
    const isFormValid = computed(() => {
      return form.value.currentPassword && 
             form.value.newPassword && 
             form.value.confirmPassword &&
             form.value.newPassword === form.value.confirmPassword &&
             form.value.newPassword.length >= 6
    })
    
    // Validate form
    const validateForm = () => {
      errors.value = {}
      
      if (!form.value.currentPassword) {
        errors.value.currentPassword = 'Le mot de passe actuel est requis'
      }
      
      if (!form.value.newPassword) {
        errors.value.newPassword = 'Le nouveau mot de passe est requis'
      } else if (form.value.newPassword.length < 6) {
        errors.value.newPassword = 'Le mot de passe doit contenir au moins 6 caractères'
      }
      
      if (!form.value.confirmPassword) {
        errors.value.confirmPassword = 'La confirmation du mot de passe est requise'
      } else if (form.value.newPassword !== form.value.confirmPassword) {
        errors.value.confirmPassword = 'Les mots de passe ne correspondent pas'
      }
      
      return Object.keys(errors.value).length === 0
    }
    
    // Submit password change
    const submitPasswordChange = async () => {
      if (!validateForm()) return
      
      isSubmitting.value = true
      generalError.value = ''
      
      try {
        await UserService.changePassword(
          form.value.currentPassword,
          form.value.newPassword,
          form.value.confirmPassword
        )
        
        // Mettre à jour le store d'authentification
        await authStore.refreshUser()
        
        notificationStore.success(
          'Mot de passe changé',
          'Votre mot de passe a été changé avec succès'
        )
        
        emit('success')
        
      } catch (error) {
        console.error('Erreur lors du changement de mot de passe:', error)
        
        if (error.response && error.response.status === 422) {
          // Erreurs de validation
          const validationErrors = error.response.data.errors
          if (validationErrors) {
            Object.keys(validationErrors).forEach(key => {
              if (key === 'current_password') {
                errors.value.currentPassword = validationErrors[key][0]
              } else if (key === 'new_password') {
                errors.value.newPassword = validationErrors[key][0]
              }
            })
          }
        } else {
          generalError.value = error.response?.data?.message || 'Erreur lors du changement de mot de passe'
        }
      } finally {
        isSubmitting.value = false
      }
    }
    
    // Reset form when modal opens/closes
    watch(() => props.show, (newShow) => {
      if (newShow) {
        // Reset form
        form.value = {
          currentPassword: '',
          newPassword: '',
          confirmPassword: ''
        }
        errors.value = {}
        generalError.value = ''
        showCurrentPassword.value = false
        showNewPassword.value = false
        showConfirmPassword.value = false
      }
    })
    
    // Clear errors when typing
    watch(() => form.value.currentPassword, () => {
      if (errors.value.currentPassword) {
        delete errors.value.currentPassword
      }
    })
    
    watch(() => form.value.newPassword, () => {
      if (errors.value.newPassword) {
        delete errors.value.newPassword
      }
      if (errors.value.confirmPassword && form.value.newPassword === form.value.confirmPassword) {
        delete errors.value.confirmPassword
      }
    })
    
    watch(() => form.value.confirmPassword, () => {
      if (errors.value.confirmPassword && form.value.newPassword === form.value.confirmPassword) {
        delete errors.value.confirmPassword
      }
    })
    
    return {
      form,
      errors,
      generalError,
      isSubmitting,
      showCurrentPassword,
      showNewPassword,
      showConfirmPassword,
      isFormValid,
      submitPasswordChange
    }
  }
}
</script>