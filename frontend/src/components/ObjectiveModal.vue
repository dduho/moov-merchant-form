<template>
  <div
    v-if="show"
    class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full z-50"
    @click="closeModal"
  >
    <div class="relative top-20 mx-auto p-5 border w-full max-w-2xl shadow-lg rounded-md bg-white" @click.stop>
      <form @submit.prevent="submitForm">
        <div class="mt-3">
          <!-- Header -->
          <div class="flex items-center justify-between mb-6">
            <div class="flex items-center">
              <div class="mx-auto flex-shrink-0 flex items-center justify-center h-12 w-12 rounded-full bg-orange-100">
                <i class="fas fa-bullseye text-orange-600 text-xl"></i>
              </div>
              <div class="ml-4">
                <h3 class="text-lg font-medium text-gray-900">
                  {{ isEditMode ? 'Modifier l\'objectif' : 'Créer un objectif' }}
                </h3>
                <p class="text-sm text-gray-500">
                  Définissez les objectifs pour vos commerciaux
                </p>
              </div>
            </div>
            <button
              @click="closeModal"
              type="button"
              class="text-gray-400 hover:text-gray-600 focus:outline-none"
            >
              <i class="fas fa-times"></i>
            </button>
          </div>

          <!-- Form -->
          <div class="space-y-6">
            <!-- Type d'objectif -->
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-2">
                Type d'objectif <span class="text-red-500">*</span>
              </label>
              <div class="grid grid-cols-2 gap-3">
                <button
                  type="button"
                  @click="form.user_id = null"
                  :class="[
                    'flex items-center justify-center px-4 py-3 border-2 rounded-lg transition-all',
                    form.user_id === null
                      ? 'border-orange-500 bg-orange-50 text-orange-700'
                      : 'border-gray-300 bg-white text-gray-700 hover:border-gray-400'
                  ]"
                >
                  <i class="fas fa-users mr-2"></i>
                  <span class="font-medium">Objectif global par défaut</span>
                </button>
                <button
                  type="button"
                  @click="form.user_id = ''"
                  :class="[
                    'flex items-center justify-center px-4 py-3 border-2 rounded-lg transition-all',
                    form.user_id !== null
                      ? 'border-orange-500 bg-orange-50 text-orange-700'
                      : 'border-gray-300 bg-white text-gray-700 hover:border-gray-400'
                  ]"
                >
                  <i class="fas fa-user mr-2"></i>
                  <span class="font-medium">Objectif particulier</span>
                </button>
              </div>
              <p class="mt-2 text-xs text-gray-500">
                <i class="fas fa-info-circle mr-1"></i>
                {{ form.user_id === null 
                  ? 'Cet objectif s\'appliquera à tous les commerciaux par défaut' 
                  : 'Cet objectif écrasera l\'objectif global pour le commercial sélectionné' 
                }}
              </p>
            </div>

            <!-- Commercial Selection (only if not global) -->
            <div v-if="form.user_id !== null">
              <label class="block text-sm font-medium text-gray-700 mb-2">
                Commercial <span class="text-red-500">*</span>
              </label>
              <select
                v-model="form.user_id"
                :disabled="isEditMode"
                class="block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-orange-500 focus:border-orange-500 disabled:bg-gray-100 disabled:cursor-not-allowed"
                required
              >
                <option value="">Sélectionnez un commercial</option>
                <option
                  v-for="commercial in commercials"
                  :key="commercial.id"
                  :value="commercial.id"
                >
                  {{ commercial.first_name }} {{ commercial.last_name }}
                </option>
              </select>
              <p v-if="isEditMode" class="mt-1 text-xs text-gray-500">
                Le commercial ne peut pas être modifié après création
              </p>
            </div>

            <!-- Période -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
              <div>
                  <label class="block text-sm font-medium text-gray-700 mb-2">
                    Mois (optionnel)
                  </label>
                  <select
                    v-model="form.target_month"
                    class="block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-orange-500 focus:border-orange-500"
                  >
                    <option value="">Tous les mois (toute l'année)</option>
                    <option :value="1">Janvier</option>
                    <option :value="2">Février</option>
                    <option :value="3">Mars</option>
                    <option :value="4">Avril</option>
                    <option :value="5">Mai</option>
                    <option :value="6">Juin</option>
                    <option :value="7">Juillet</option>
                    <option :value="8">Août</option>
                    <option :value="9">Septembre</option>
                    <option :value="10">Octobre</option>
                    <option :value="11">Novembre</option>
                    <option :value="12">Décembre</option>
                  </select>
              </div>
              <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">
                  Année <span class="text-red-500">*</span>
                </label>
                <select
                  v-model.number="form.target_year"
                  class="block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-orange-500 focus:border-orange-500"
                  required
                >
                  <option value="">Sélectionnez une année</option>
                  <option v-for="year in availableYears" :key="year" :value="year">{{ year }}</option>
                </select>
              </div>
            </div>

            <!-- Objectif mensuel -->
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-2">
                Objectif mensuel <span class="text-red-500">*</span>
              </label>
              <div class="relative">
                <input
                  v-model.number="form.monthly_target"
                  type="number"
                  min="1"
                  class="block w-full px-3 py-2 pr-32 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-orange-500 focus:border-orange-500"
                  placeholder="Ex: 50"
                  required
                >
                <div class="absolute inset-y-0 right-0 pr-3 flex items-center pointer-events-none">
                  <span class="text-gray-500 text-sm">candidatures/mois</span>
                </div>
              </div>
              <p class="mt-1 text-xs text-gray-500">
                Nombre de candidatures à atteindre pour ce mois
              </p>
            </div>

            <!-- Description -->
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-2">
                Description (optionnelle)
              </label>
              <textarea
                v-model="form.description"
                rows="3"
                class="block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-orange-500 focus:border-orange-500"
                placeholder="Ajoutez une description ou des notes pour cet objectif..."
              ></textarea>
            </div>
          </div>

          <!-- Actions -->
          <div class="flex items-center justify-end space-x-3 mt-8 pt-6 border-t border-gray-200">
            <button
              @click="closeModal"
              type="button"
              :disabled="isLoading"
              class="px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-md shadow-sm hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-orange-500 disabled:opacity-50 disabled:cursor-not-allowed"
            >
              Annuler
            </button>
            <button
              type="submit"
              :disabled="isLoading || !isFormValid"
              class="inline-flex items-center px-4 py-2 text-sm font-medium text-white bg-orange-600 border border-transparent rounded-md shadow-sm hover:bg-orange-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-orange-500 disabled:opacity-50 disabled:cursor-not-allowed"
            >
              <i v-if="isLoading" class="fas fa-spinner fa-spin mr-2"></i>
              <i v-else class="fas fa-save mr-2"></i>
              {{ isLoading ? 'Enregistrement...' : (isEditMode ? 'Modifier' : 'Créer') }}
            </button>
          </div>
        </div>
      </form>
    </div>
  </div>
</template>

<script>
import { ref, computed, watch } from 'vue'
import { useObjectiveStore } from '../stores/objective'
import { useNotificationStore } from '../stores/notifications'

export default {
  name: 'ObjectiveModal',
  props: {
    show: {
      type: Boolean,
      default: false
    },
    objective: {
      type: Object,
      default: null
    },
    commercials: {
      type: Array,
      default: () => []
    }
  },
  emits: ['close', 'success'],
  setup(props, { emit }) {
    const objectiveStore = useObjectiveStore()
    const notificationStore = useNotificationStore()

    // State
    const isLoading = ref(false)
    const currentYear = new Date().getFullYear()
    const form = ref({
      user_id: null, // null = global, string = specific user
      target_month: null,
      target_year: currentYear,
      monthly_target: null,
      description: ''
    })

    // Computed
    const isEditMode = computed(() => !!props.objective)

    const availableYears = computed(() => {
      const years = []
      for (let i = currentYear - 1; i <= currentYear + 2; i++) {
        years.push(i)
      }
      return years
    })

    const isFormValid = computed(() => {
      // Year is always required
      if (!form.value.target_year) return false

      // monthly_target must be positive
      if (!form.value.monthly_target || form.value.monthly_target <= 0) return false

      // For specific user: user_id required
      if (form.value.user_id !== null && !form.value.user_id) return false

      // Month is optional: if omitted, the objective applies to all months of the year
      return true
    })

    // Methods
    const closeModal = () => {
      if (!isLoading.value) {
        resetForm()
        emit('close')
      }
    }

    const resetForm = () => {
      form.value = {
        user_id: null,
        target_month: null,
        target_year: currentYear,
        monthly_target: null,
        description: ''
      }
    }

    const loadObjectiveData = () => {
      if (props.objective) {
        form.value = {
          user_id: props.objective.user_id || null,
          target_month: props.objective.target_month,
          target_year: props.objective.target_year,
          monthly_target: props.objective.monthly_target,
          description: props.objective.description || ''
        }
      }
    }

    const submitForm = async () => {
      if (!isFormValid.value) return

      try {
        isLoading.value = true

        const objectiveData = {
          user_id: form.value.user_id, // null for global, or user ID
          target_month: form.value.target_month === '' || form.value.target_month === null ? null : Number(form.value.target_month),
          target_year: Number(form.value.target_year),
          monthly_target: Number(form.value.monthly_target),
          description: form.value.description || null
        }

        if (isEditMode.value) {
          await objectiveStore.updateObjective(props.objective.id, objectiveData)
          
          const objectiveType = objectiveData.user_id ? 'particulier' : 'global'
          notificationStore.success(
            'Objectif modifié',
            `L'objectif ${objectiveType} a été mis à jour avec succès.`
          )
        } else {
          await objectiveStore.createObjective(objectiveData)
          
          const objectiveType = objectiveData.user_id ? 'particulier' : 'global'
          notificationStore.success(
            'Objectif créé',
            `Le nouvel objectif ${objectiveType} a été créé avec succès.`
          )
        }

        emit('success')
      } catch (error) {
        console.error('Error saving objective:', error)
        
        notificationStore.error(
          'Erreur',
          error.response?.data?.message || 'Une erreur est survenue lors de l\'enregistrement'
        )
      } finally {
        isLoading.value = false
      }
    }

    // Watch for objective changes
    watch(() => props.objective, () => {
      if (props.show) {
        loadObjectiveData()
      }
    }, { immediate: true })

    // Watch for modal visibility
    watch(() => props.show, (newValue) => {
      if (newValue) {
        loadObjectiveData()
      } else {
        resetForm()
      }
    })

    return {
      isLoading,
      form,
      isEditMode,
      isFormValid,
      availableYears,
      closeModal,
      submitForm
    }
  }
}
</script>