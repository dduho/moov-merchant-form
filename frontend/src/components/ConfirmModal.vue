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
            <div class="mx-auto flex-shrink-0 flex items-center justify-center h-12 w-12 rounded-full bg-red-100">
              <i class="fas fa-exclamation-triangle text-red-600"></i>
            </div>
            <div class="ml-4">
              <h3 class="text-lg font-medium text-gray-900">
                {{ title }}
              </h3>
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
          <p class="text-sm text-gray-600">
            {{ message }}
          </p>
        </div>

        <!-- Actions -->
        <div class="flex items-center justify-end space-x-3">
          <button
            @click="closeModal"
            :disabled="isLoading"
            class="px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-md shadow-sm hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-gray-500 disabled:opacity-50 disabled:cursor-not-allowed"
          >
            {{ cancelText }}
          </button>
          <button
            @click="confirmAction"
            :disabled="isLoading"
            :class="confirmClass"
            class="inline-flex items-center px-4 py-2 text-sm font-medium text-white border border-transparent rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-offset-2 disabled:opacity-50 disabled:cursor-not-allowed"
          >
            <i v-if="isLoading" class="fas fa-spinner fa-spin mr-2"></i>
            <i v-else class="fas fa-check mr-2"></i>
            {{ isLoading ? 'En cours...' : confirmText }}
          </button>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
import { ref } from 'vue'

export default {
  name: 'ConfirmModal',
  props: {
    show: {
      type: Boolean,
      default: false
    },
    title: {
      type: String,
      default: 'Confirmer l\'action'
    },
    message: {
      type: String,
      default: 'Êtes-vous sûr de vouloir effectuer cette action ?'
    },
    confirmText: {
      type: String,
      default: 'Confirmer'
    },
    cancelText: {
      type: String,
      default: 'Annuler'
    },
    confirmClass: {
      type: String,
      default: 'bg-red-600 hover:bg-red-700 focus:ring-red-500'
    }
  },
  emits: ['close', 'confirm'],
  setup(props, { emit }) {
    const isLoading = ref(false)

    const closeModal = () => {
      if (!isLoading.value) {
        emit('close')
      }
    }

    const confirmAction = async () => {
      try {
        isLoading.value = true
        await emit('confirm')
      } finally {
        isLoading.value = false
      }
    }

    return {
      isLoading,
      closeModal,
      confirmAction
    }
  }
}
</script>