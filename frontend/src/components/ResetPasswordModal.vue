<template>
  <div
    v-if="show"
    class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full z-50"
    @click="closeModal"
  >
    <div class="relative top-20 mx-auto p-5 border w-96 shadow-lg rounded-md bg-white" @click.stop>
      <div class="mt-3">
        <!-- Header -->
        <div class="flex items-center justify-between mb-4">
          <div class="flex items-center">
            <div class="mx-auto flex-shrink-0 flex items-center justify-center h-12 w-12 rounded-full bg-orange-100">
              <i class="fas fa-key text-orange-600"></i>
            </div>
            <div class="ml-4">
              <h3 class="text-lg font-medium text-gray-900">
                Réinitialiser le mot de passe
              </h3>
              <p class="text-sm text-gray-500">
                {{ user?.first_name }} {{ user?.last_name }}
              </p>
            </div>
          </div>
          <button
            @click="closeModal"
            class="text-gray-400 hover:text-gray-600 focus:outline-none"
          >
            <i class="fas fa-times"></i>
          </button>
        </div>

        <!-- Content -->
        <div class="mb-6">
          <div class="bg-yellow-50 border border-yellow-200 rounded-md p-4 mb-4">
            <div class="flex">
              <div class="flex-shrink-0">
                <i class="fas fa-exclamation-triangle text-yellow-400"></i>
              </div>
              <div class="ml-3">
                <h3 class="text-sm font-medium text-yellow-800">
                  Attention
                </h3>
                <div class="mt-2 text-sm text-yellow-700">
                  <p>
                    Le mot de passe de l'utilisateur sera réinitialisé au mot de passe par défaut.
                    L'utilisateur devra le changer lors de sa prochaine connexion.
                  </p>
                </div>
              </div>
            </div>
          </div>

          <div class="space-y-4">
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-1">
                Mot de passe par défaut
              </label>
              <div class="relative">
                <input
                  v-model="defaultPassword"
                  :type="showPassword ? 'text' : 'password'"
                  class="block w-full pr-10 px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-orange-500 focus:border-orange-500"
                  readonly
                >
                <button
                  @click="showPassword = !showPassword"
                  type="button"
                  class="absolute inset-y-0 right-0 pr-3 flex items-center"
                >
                  <i :class="showPassword ? 'fas fa-eye-slash' : 'fas fa-eye'" class="text-gray-400 hover:text-gray-600"></i>
                </button>
              </div>
            </div>

            <div class="flex items-center">
              <input
                id="force-change"
                v-model="forcePasswordChange"
                type="checkbox"
                class="h-4 w-4 text-orange-600 focus:ring-orange-500 border-gray-300 rounded"
              >
              <label for="force-change" class="ml-2 block text-sm text-gray-900">
                Forcer le changement de mot de passe à la prochaine connexion
              </label>
            </div>

            <div class="flex items-center">
              <input
                id="send-email"
                v-model="sendEmailNotification"
                type="checkbox"
                class="h-4 w-4 text-orange-600 focus:ring-orange-500 border-gray-300 rounded"
              >
              <label for="send-email" class="ml-2 block text-sm text-gray-900">
                Envoyer un email de notification à l'utilisateur
              </label>
            </div>
          </div>
        </div>

        <!-- Actions -->
        <div class="flex items-center justify-end space-x-3">
          <button
            @click="closeModal"
            :disabled="isLoading"
            class="px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-md shadow-sm hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-orange-500 disabled:opacity-50 disabled:cursor-not-allowed"
          >
            Annuler
          </button>
          <button
            @click="resetPassword"
            :disabled="isLoading"
            class="inline-flex items-center px-4 py-2 text-sm font-medium text-white bg-orange-600 border border-transparent rounded-md shadow-sm hover:bg-orange-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-orange-500 disabled:opacity-50 disabled:cursor-not-allowed"
          >
            <i v-if="isLoading" class="fas fa-spinner fa-spin mr-2"></i>
            <i v-else class="fas fa-key mr-2"></i>
            {{ isLoading ? 'Réinitialisation...' : 'Réinitialiser' }}
          </button>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
import { ref, computed, watch } from 'vue'
import { useUserManagementStore } from '../stores/userManagement'
import { useNotificationStore } from '../stores/notifications'

export default {
  name: 'ResetPasswordModal',
  props: {
    show: {
      type: Boolean,
      default: false
    },
    user: {
      type: Object,
      default: null
    }
  },
  emits: ['close', 'success'],
  setup(props, { emit }) {
    const userStore = useUserManagementStore()
    const notificationStore = useNotificationStore()

    // State
    const isLoading = ref(false)
    const showPassword = ref(false)
    const defaultPassword = ref('password123')
    const forcePasswordChange = ref(true)
    const sendEmailNotification = ref(true)

    // Methods
    const closeModal = () => {
      if (!isLoading.value) {
        emit('close')
      }
    }

    const resetPassword = async () => {
      if (!props.user) return

      try {
        isLoading.value = true

        await userStore.resetUserPassword(props.user.id, {
          default_password: defaultPassword.value,
          force_password_change: forcePasswordChange.value,
          send_email: sendEmailNotification.value
        })

        notificationStore.success(
          'Mot de passe réinitialisé',
          `Le mot de passe de ${props.user.first_name} ${props.user.last_name} a été réinitialisé avec succès.`
        )

        emit('success')
      } catch (error) {
        console.error('Error resetting password:', error)
        notificationStore.error(
          'Erreur',
          error.response?.data?.message || 'Une erreur est survenue lors de la réinitialisation du mot de passe'
        )
      } finally {
        isLoading.value = false
      }
    }

    // Reset form when modal closes
    watch(() => props.show, (newValue) => {
      if (!newValue) {
        showPassword.value = false
        forcePasswordChange.value = true
        sendEmailNotification.value = true
      }
    })

    return {
      isLoading,
      showPassword,
      defaultPassword,
      forcePasswordChange,
      sendEmailNotification,
      closeModal,
      resetPassword
    }
  }
}
</script>