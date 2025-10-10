<template>
  <div
    v-if="show"
    class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full z-50"
    @click="closeModal"
  >
    <div class="relative top-10 mx-auto p-5 border w-full max-w-4xl shadow-lg rounded-md bg-white" @click.stop>
      <div class="mt-3">
        <!-- Header -->
        <div class="flex items-center justify-between mb-6">
          <div class="flex items-center">
            <div class="mx-auto flex-shrink-0 flex items-center justify-center h-12 w-12 rounded-full bg-blue-100">
              <i class="fas fa-chart-bar text-blue-600"></i>
            </div>
            <div class="ml-4">
              <h3 class="text-xl font-medium text-gray-900">
                Statistiques de {{ user?.first_name }} {{ user?.last_name }}
              </h3>
              <p class="text-sm text-gray-500">
                Performance et progression des objectifs
              </p>
            </div>
          </div>
          <button
            @click="closeModal"
            class="text-gray-400 hover:text-gray-600 focus:outline-none"
          >
            <i class="fas fa-times text-xl"></i>
          </button>
        </div>

        <!-- Loading State -->
        <div v-if="isLoading" class="text-center py-12">
          <i class="fas fa-spinner fa-spin text-3xl text-gray-400 mb-4"></i>
          <p class="text-gray-500">Chargement des statistiques...</p>
        </div>

        <!-- Error State -->
        <div v-else-if="error" class="text-center py-12">
          <i class="fas fa-exclamation-circle text-3xl text-red-500 mb-4"></i>
          <p class="text-red-600 mb-4">{{ error }}</p>
          <button
            @click="loadStats"
            class="px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700"
          >
            Réessayer
          </button>
        </div>

        <!-- Content -->
        <div v-else-if="hasStats" class="space-y-6">
          <!-- Summary Cards -->
          <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
            <!-- Current Month -->
            <div class="bg-gradient-to-r from-blue-500 to-blue-600 rounded-lg p-6 text-white">
              <div class="flex items-center justify-between">
                <div>
                  <p class="text-blue-100 text-sm font-medium uppercase tracking-wide">
                    Ce mois
                  </p>
                  <p class="text-3xl font-bold">
                    {{ stats.applications_this_month || 0 }}
                  </p>
                  <p class="text-blue-100 text-sm">
                    candidatures
                  </p>
                </div>
                <div class="bg-blue-400 bg-opacity-30 rounded-full p-3">
                  <i class="fas fa-calendar-month text-2xl"></i>
                </div>
              </div>
            </div>

            <!-- Current Year -->
            <div class="bg-gradient-to-r from-green-500 to-green-600 rounded-lg p-6 text-white">
              <div class="flex items-center justify-between">
                <div>
                  <p class="text-green-100 text-sm font-medium uppercase tracking-wide">
                    Cette année
                  </p>
                  <p class="text-3xl font-bold">
                    {{ stats.applications_this_year || 0 }}
                  </p>
                  <p class="text-green-100 text-sm">
                    candidatures
                  </p>
                </div>
                <div class="bg-green-400 bg-opacity-30 rounded-full p-3">
                  <i class="fas fa-calendar-year text-2xl"></i>
                </div>
              </div>
            </div>

            <!-- Total -->
            <div class="bg-gradient-to-r from-purple-500 to-purple-600 rounded-lg p-6 text-white">
              <div class="flex items-center justify-between">
                <div>
                  <p class="text-purple-100 text-sm font-medium uppercase tracking-wide">
                    Total
                  </p>
                  <p class="text-3xl font-bold">
                    {{ stats.applications_total || 0 }}
                  </p>
                  <p class="text-purple-100 text-sm">
                    candidatures
                  </p>
                </div>
                <div class="bg-purple-400 bg-opacity-30 rounded-full p-3">
                  <i class="fas fa-infinity text-2xl"></i>
                </div>
              </div>
            </div>
          </div>

          <!-- Current Objective -->
          <div v-if="stats.objective" class="bg-white border border-gray-200 rounded-lg p-6">
            <div class="flex items-center justify-between mb-4">
              <h4 class="text-lg font-medium text-gray-900 flex items-center">
                <i class="fas fa-target text-orange-500 mr-2"></i>
                Objectif actuel
              </h4>
              <span class="text-sm text-gray-500">
                {{ formatDateRange(stats.objective.period_start, stats.objective.period_end) }}
              </span>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
              <div>
                <div class="flex items-center justify-between mb-2">
                  <span class="text-sm font-medium text-gray-700">Progression mensuelle</span>
                  <span class="text-sm text-gray-500">
                    {{ stats.applications_this_month || 0 }} / {{ stats.objective.monthly_target }}
                  </span>
                </div>
                <div class="w-full bg-gray-200 rounded-full h-3">
                  <div
                    class="bg-gradient-to-r from-orange-500 to-orange-600 h-3 rounded-full transition-all duration-300"
                    :style="{ width: getMonthlyProgress() + '%' }"
                  ></div>
                </div>
                <div class="mt-1 text-xs text-gray-500">
                  {{ getMonthlyProgress() }}% de l'objectif mensuel
                </div>
              </div>

              <div>
                <div class="flex items-center justify-between mb-2">
                  <span class="text-sm font-medium text-gray-700">Progression totale</span>
                  <span class="text-sm text-gray-500">
                    {{ stats.objective.current_progress || 0 }} / {{ stats.objective.target_applications }}
                  </span>
                </div>
                <div class="w-full bg-gray-200 rounded-full h-3">
                  <div
                    class="bg-gradient-to-r from-blue-500 to-blue-600 h-3 rounded-full transition-all duration-300"
                    :style="{ width: getTotalProgress() + '%' }"
                  ></div>
                </div>
                <div class="mt-1 text-xs text-gray-500">
                  {{ getTotalProgress() }}% de l'objectif total
                </div>
              </div>
            </div>

            <!-- Objective Status -->
            <div class="mt-4 p-4 rounded-md" :class="getObjectiveStatusClass()">
              <div class="flex items-center">
                <i :class="getObjectiveStatusIcon()" class="mr-2"></i>
                <span class="font-medium">{{ getObjectiveStatusText() }}</span>
              </div>
            </div>
          </div>

          <!-- No Objective -->
          <div v-else class="bg-gray-50 border border-gray-200 rounded-lg p-6 text-center">
            <i class="fas fa-target text-gray-400 text-3xl mb-4"></i>
            <h4 class="text-lg font-medium text-gray-900 mb-2">Aucun objectif défini</h4>
            <p class="text-gray-500">
              Aucun objectif n'a été assigné à cet utilisateur pour le moment.
            </p>
          </div>

          <!-- Recent Activity -->
          <div class="bg-white border border-gray-200 rounded-lg p-6">
            <h4 class="text-lg font-medium text-gray-900 mb-4 flex items-center">
              <i class="fas fa-history text-gray-500 mr-2"></i>
              Activité récente
            </h4>

            <div v-if="stats.recent_applications && stats.recent_applications.length > 0" class="space-y-3">
              <div
                v-for="application in stats.recent_applications"
                :key="application.id"
                class="flex items-center justify-between p-3 bg-gray-50 rounded-md"
              >
                <div class="flex items-center">
                  <div class="flex-shrink-0 h-8 w-8 bg-orange-100 rounded-full flex items-center justify-center">
                    <i class="fas fa-file-alt text-orange-600 text-sm"></i>
                  </div>
                  <div class="ml-3">
                    <p class="text-sm font-medium text-gray-900">
                      {{ application.business_name || 'Candidature' }}
                    </p>
                    <p class="text-xs text-gray-500">
                      {{ formatDate(application.created_at) }}
                    </p>
                  </div>
                </div>
                <div>
                  <span
                    class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium"
                    :class="getApplicationStatusClass(application.status)"
                  >
                    {{ getApplicationStatusText(application.status) }}
                  </span>
                </div>
              </div>
            </div>

            <div v-else class="text-center py-8">
              <i class="fas fa-inbox text-gray-400 text-2xl mb-2"></i>
              <p class="text-gray-500">Aucune activité récente</p>
            </div>
          </div>
        </div>

        <!-- Actions -->
        <div class="flex items-center justify-end space-x-3 mt-6 pt-6 border-t border-gray-200">
          <button
            @click="closeModal"
            class="px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-md shadow-sm hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500"
          >
            Fermer
          </button>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
import { ref, computed, watch } from 'vue'
import { useUserManagementStore } from '../stores/userManagement'

export default {
  name: 'UserStatsModal',
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
  emits: ['close'],
  setup(props, { emit }) {
    const userStore = useUserManagementStore()

    // State
    const isLoading = ref(false)
    const error = ref(null)
    const stats = ref(null)

    // Computed
    const hasStats = computed(() => stats.value !== null && stats.value !== undefined)

    // Methods
    const closeModal = () => {
      emit('close')
    }

    const loadStats = async () => {
      if (!props.user) return

      try {
        isLoading.value = true
        error.value = null
        
        const response = await userStore.getUserStats(props.user.id)
        console.log('Stats response:', response)
        // Le service retourne response.data d'axios, qui contient {data: {...stats...}}
        // Donc on doit accéder à response.data pour obtenir les stats
        stats.value = response.data
        console.log('Stats value:', stats.value)
      } catch (err) {
        console.error('Error loading user stats:', err)
        error.value = err.response?.data?.message || 'Erreur lors du chargement des statistiques'
      } finally {
        isLoading.value = false
      }
    }

    const formatDateRange = (start, end) => {
      const startDate = new Date(start).toLocaleDateString('fr-FR', {
        day: '2-digit',
        month: '2-digit',
        year: 'numeric'
      })
      const endDate = new Date(end).toLocaleDateString('fr-FR', {
        day: '2-digit',
        month: '2-digit',
        year: 'numeric'
      })
      return `${startDate} - ${endDate}`
    }

    const formatDate = (date) => {
      return new Date(date).toLocaleDateString('fr-FR', {
        day: '2-digit',
        month: '2-digit',
        year: 'numeric',
        hour: '2-digit',
        minute: '2-digit'
      })
    }

    const getMonthlyProgress = () => {
      if (!stats.value.objective || !stats.value.objective.monthly_target) return 0
      const progress = (stats.value.applications_this_month || 0) / stats.value.objective.monthly_target * 100
      return Math.min(100, Math.round(progress))
    }

    const getTotalProgress = () => {
      if (!stats.value.objective || !stats.value.objective.target_applications) return 0
      const progress = (stats.value.objective.current_progress || 0) / stats.value.objective.target_applications * 100
      return Math.min(100, Math.round(progress))
    }

    const getObjectiveStatusClass = () => {
      const monthlyProgress = getMonthlyProgress()
      if (monthlyProgress >= 100) return 'bg-green-50 text-green-800'
      if (monthlyProgress >= 75) return 'bg-blue-50 text-blue-800'
      if (monthlyProgress >= 50) return 'bg-yellow-50 text-yellow-800'
      return 'bg-red-50 text-red-800'
    }

    const getObjectiveStatusIcon = () => {
      const monthlyProgress = getMonthlyProgress()
      if (monthlyProgress >= 100) return 'fas fa-check-circle text-green-600'
      if (monthlyProgress >= 75) return 'fas fa-thumbs-up text-blue-600'
      if (monthlyProgress >= 50) return 'fas fa-exclamation-triangle text-yellow-600'
      return 'fas fa-times-circle text-red-600'
    }

    const getObjectiveStatusText = () => {
      const monthlyProgress = getMonthlyProgress()
      if (monthlyProgress >= 100) return 'Objectif mensuel atteint !'
      if (monthlyProgress >= 75) return 'Excellente progression'
      if (monthlyProgress >= 50) return 'Progression correcte'
      return 'Progression insuffisante'
    }

    const getApplicationStatusClass = (status) => {
      switch (status) {
        case 'approved':
          return 'bg-green-100 text-green-800'
        case 'pending':
          return 'bg-yellow-100 text-yellow-800'
        case 'rejected':
          return 'bg-red-100 text-red-800'
        default:
          return 'bg-gray-100 text-gray-800'
      }
    }

    const getApplicationStatusText = (status) => {
      switch (status) {
        case 'approved':
          return 'Approuvée'
        case 'pending':
          return 'En attente'
        case 'rejected':
          return 'Rejetée'
        default:
          return status || 'Inconnu'
      }
    }

    // Watch for modal visibility and user changes
    watch([() => props.show, () => props.user], ([show, user], [oldShow]) => {
      if (show && user) {
        loadStats()
      } else if (!show && oldShow) {
        // Reset stats when modal closes
        stats.value = null
        error.value = null
      }
    })

    return {
      isLoading,
      error,
      stats,
      hasStats,
      closeModal,
      loadStats,
      formatDateRange,
      formatDate,
      getMonthlyProgress,
      getTotalProgress,
      getObjectiveStatusClass,
      getObjectiveStatusIcon,
      getObjectiveStatusText,
      getApplicationStatusClass,
      getApplicationStatusText
    }
  }
}
</script>