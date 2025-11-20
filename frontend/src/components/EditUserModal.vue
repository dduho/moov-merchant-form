<template>
  <div
    v-if="show"
    class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full z-50"
    @click="closeModal"
  >
    <div class="relative top-20 mx-auto p-5 border w-full max-w-2xl shadow-lg rounded-md bg-white" @click.stop>
      <div class="mt-3">
        <!-- Header -->
        <div class="flex items-center justify-between mb-4">
          <div class="flex items-center">
            <div class="mx-auto flex-shrink-0 flex items-center justify-center h-12 w-12 rounded-full bg-blue-100">
              <i class="fas fa-user-edit text-blue-600"></i>
            </div>
            <div class="ml-4">
              <h3 class="text-lg font-medium text-gray-900">
                Modifier l'utilisateur
              </h3>
              <p class="text-sm text-gray-500">
                {{ form.first_name }} {{ form.last_name }}
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

        <!-- Form -->
        <form @submit.prevent="saveChanges" class="space-y-4">
          <!-- Error Message -->
          <div v-if="errorMessage" class="bg-red-50 border border-red-200 rounded-md p-3">
            <div class="flex">
              <div class="flex-shrink-0">
                <i class="fas fa-exclamation-circle text-red-400"></i>
              </div>
              <div class="ml-3">
                <p class="text-sm text-red-700">{{ errorMessage }}</p>
              </div>
            </div>
          </div>

          <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <!-- First Name -->
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-1">
                Prénom <span class="text-red-500">*</span>
              </label>
              <input
                v-model="form.first_name"
                type="text"
                required
                class="block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-orange-500 focus:border-orange-500"
                :class="{ 'border-red-500': errors.first_name }"
              >
              <p v-if="errors.first_name" class="mt-1 text-sm text-red-600">{{ errors.first_name }}</p>
            </div>

            <!-- Last Name -->
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-1">
                Nom <span class="text-red-500">*</span>
              </label>
              <input
                v-model="form.last_name"
                type="text"
                required
                class="block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-orange-500 focus:border-orange-500"
                :class="{ 'border-red-500': errors.last_name }"
              >
              <p v-if="errors.last_name" class="mt-1 text-sm text-red-600">{{ errors.last_name }}</p>
            </div>

            <!-- Email -->
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-1">
                Email
              </label>
              <input
                v-model="form.email"
                type="email"
                class="block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-orange-500 focus:border-orange-500"
                :class="{ 'border-red-500': errors.email }"
              >
              <p v-if="errors.email" class="mt-1 text-sm text-red-600">{{ errors.email }}</p>
            </div>

            <!-- Phone -->
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-1">
                Téléphone <span class="text-red-500">*</span>
              </label>
              <input
                v-model="form.phone"
                type="tel"
                required
                class="block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-orange-500 focus:border-orange-500"
                :class="{ 'border-red-500': errors.phone }"
              >
              <p v-if="errors.phone" class="mt-1 text-sm text-red-600">{{ errors.phone }}</p>
            </div>

            <!-- Username -->
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-1">
                Nom d'utilisateur <span class="text-red-500">*</span>
              </label>
              <input
                v-model="form.username"
                type="text"
                required
                class="block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-orange-500 focus:border-orange-500"
                :class="{ 'border-red-500': errors.username }"
              >
              <p v-if="errors.username" class="mt-1 text-sm text-red-600">{{ errors.username }}</p>
            </div>

            <!-- Role -->
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-1">
                Rôle <span class="text-red-500">*</span>
              </label>
              <select
                v-model="form.role_slug"
                required
                class="block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-orange-500 focus:border-orange-500"
                :class="{ 'border-red-500': errors.role_slug }"
              >
                <option value="">Sélectionnez un rôle</option>
                <option value="admin">Administrateur</option>
                <option value="commercial">Commercial</option>
                <option value="personnel">Personnel</option>
              </select>
              <p v-if="errors.role_slug" class="mt-1 text-sm text-red-600">{{ errors.role_slug }}</p>
            </div>
          </div>

          <!-- Actions -->
          <div class="flex items-center justify-end space-x-3 pt-4 border-t">
            <button
              type="button"
              @click="closeModal"
              :disabled="isLoading"
              class="px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-md shadow-sm hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-orange-500 disabled:opacity-50 disabled:cursor-not-allowed"
            >
              Annuler
            </button>
            <button
              type="submit"
              :disabled="isLoading"
              class="inline-flex items-center px-4 py-2 text-sm font-medium text-white bg-orange-600 border border-transparent rounded-md shadow-sm hover:bg-orange-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-orange-500 disabled:opacity-50 disabled:cursor-not-allowed"
            >
              <i v-if="isLoading" class="fas fa-spinner fa-spin mr-2"></i>
              <i v-else class="fas fa-save mr-2"></i>
              {{ isLoading ? 'Enregistrement...' : 'Enregistrer' }}
            </button>
          </div>
        </form>
      </div>
    </div>
  </div>
</template>

<script>
import { ref, watch } from 'vue'
import { useUserManagementStore } from '../stores/userManagement'
import { useNotificationStore } from '../stores/notifications'

export default {
  name: 'EditUserModal',
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

    const isLoading = ref(false)
    const errorMessage = ref('')
    const errors = ref({})

    const form = ref({
      first_name: '',
      last_name: '',
      email: '',
      phone: '',
      username: '',
      role_slug: ''
    })

    // Initialize form when user prop changes
    watch(() => props.user, (newUser) => {
      if (newUser) {
        form.value = {
          first_name: newUser.first_name || '',
          last_name: newUser.last_name || '',
          email: newUser.email || '',
          phone: newUser.phone || '',
          username: newUser.username || '',
          role_slug: newUser.roles && newUser.roles.length > 0 ? newUser.roles[0].slug : ''
        }
        errors.value = {}
        errorMessage.value = ''
      }
    }, { immediate: true })

    const closeModal = () => {
      if (!isLoading.value) {
        errors.value = {}
        errorMessage.value = ''
        emit('close')
      }
    }

    const saveChanges = async () => {
      if (!props.user) return

      try {
        isLoading.value = true
        errors.value = {}
        errorMessage.value = ''

        await userStore.updateUser(props.user.id, form.value)

        notificationStore.success(
          'Utilisateur modifié',
          `Les informations de ${form.value.first_name} ${form.value.last_name} ont été mises à jour avec succès.`
        )

        emit('success')
      } catch (error) {
        console.error('Error updating user:', error)

        if (error.response?.data?.errors) {
          errors.value = error.response.data.errors
          
          const errorMessages = []
          Object.keys(errors.value).forEach(key => {
            if (errors.value[key] && errors.value[key][0]) {
              errorMessages.push(errors.value[key][0])
            }
          })
          
          if (errorMessages.length > 0) {
            errorMessage.value = errorMessages.join(' • ')
          }
        } else if (error.response?.data?.message) {
          errorMessage.value = error.response.data.message
        } else {
          errorMessage.value = 'Une erreur est survenue lors de la modification de l\'utilisateur'
        }

        notificationStore.error(
          'Erreur de modification',
          errorMessage.value
        )
      } finally {
        isLoading.value = false
      }
    }

    return {
      isLoading,
      errorMessage,
      errors,
      form,
      closeModal,
      saveChanges
    }
  }
}
</script>
