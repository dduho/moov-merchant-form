<template>
  <div class="min-h-screen bg-gray-50 py-6">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
      <!-- Header -->
      <div class="mb-8">
        <div class="md:flex md:items-center md:justify-between">
          <div class="flex-1 min-w-0">
            <div class="flex items-center mb-2">
              <button
                @click="$router.push('/dashboard')"
                class="mr-3 px-3 py-1.5 text-sm text-gray-600 hover:text-gray-900 hover:bg-gray-100 rounded-md transition-colors duration-200 flex items-center"
              >
                <i class="fas fa-arrow-left mr-2"></i>
                Retourner au dashboard
              </button>
            </div>
            <h1 class="text-2xl font-bold leading-7 text-gray-900 sm:text-3xl sm:truncate">
              <i class="fas fa-target text-orange-600 mr-3"></i>
              Gestion des Objectifs
            </h1>
            <p class="mt-1 text-sm text-gray-500">
              Définissez et suivez les objectifs de vos commerciaux
            </p>
          </div>
          <div class="mt-4 flex md:mt-0 md:ml-4">
            <button
              @click="showCreateModal = true"
              class="inline-flex items-center px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-orange-600 hover:bg-orange-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-orange-500"
            >
              <i class="fas fa-plus mr-2"></i>
              Nouvel objectif
            </button>
          </div>
        </div>
      </div>

      <!-- Tabs -->
      <div class="bg-white shadow rounded-lg mb-6">
        <div class="border-b border-gray-200">
          <nav class="-mb-px flex" aria-label="Tabs">
            <button
              @click="activeTab = 'global'"
              :class="[
                activeTab === 'global'
                  ? 'border-orange-500 text-orange-600'
                  : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300',
                'flex-1 py-4 px-2 sm:px-4 border-b-2 font-medium text-xs sm:text-sm flex items-center justify-center'
              ]"
            >
              <i class="fas fa-users mr-1 sm:mr-2"></i>
              <span class="hidden sm:inline">Objectifs Globaux</span>
              <span class="sm:hidden">Globaux</span>
              <span
                :class="[
                  activeTab === 'global' ? 'bg-orange-100 text-orange-600' : 'bg-gray-100 text-gray-900',
                  'ml-2 sm:ml-3 py-0.5 px-2 sm:px-2.5 rounded-full text-xs font-medium'
                ]"
              >
                {{ globalObjectives.length }}
              </span>
            </button>
            <button
              @click="activeTab = 'particular'"
              :class="[
                activeTab === 'particular'
                  ? 'border-orange-500 text-orange-600'
                  : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300',
                'flex-1 py-4 px-2 sm:px-4 border-b-2 font-medium text-xs sm:text-sm flex items-center justify-center'
              ]"
            >
              <i class="fas fa-user mr-1 sm:mr-2"></i>
              <span class="hidden sm:inline">Objectifs Particuliers</span>
              <span class="sm:hidden">Particuliers</span>
              <span
                :class="[
                  activeTab === 'particular' ? 'bg-orange-100 text-orange-600' : 'bg-gray-100 text-gray-900',
                  'ml-2 sm:ml-3 py-0.5 px-2 sm:px-2.5 rounded-full text-xs font-medium'
                ]"
              >
                {{ particularObjectives.length }}
              </span>
            </button>
          </nav>
        </div>

        <!-- Filters for Global Objectives -->
        <div v-if="activeTab === 'global'" class="p-4 border-b border-gray-200 bg-gray-50">
          <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-1">Période</label>
              <select
                v-model="globalFilters.period"
                class="block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-orange-500 focus:border-orange-500"
              >
                <option value="">Toutes les périodes</option>
                <option value="current">Période actuelle</option>
                <option value="upcoming">Périodes à venir</option>
                <option value="past">Périodes passées</option>
              </select>
            </div>

            <div>
              <label class="block text-sm font-medium text-gray-700 mb-1">Statut</label>
              <select
                v-model="globalFilters.status"
                class="block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-orange-500 focus:border-orange-500"
              >
                <option value="">Tous les statuts</option>
                <option value="active">Actif</option>
                <option value="completed">Terminé</option>
                <option value="overdue">En retard</option>
              </select>
            </div>

            <div class="flex items-end">
              <button
                @click="clearGlobalFilters"
                class="w-full px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-md shadow-sm hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-orange-500"
              >
                <i class="fas fa-times mr-2"></i>
                Effacer
              </button>
            </div>
          </div>
        </div>

        <!-- Filters for Particular Objectives -->
        <div v-if="activeTab === 'particular'" class="p-4 border-b border-gray-200 bg-gray-50">
          <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-1">Commercial</label>
              <select
                v-model="particularFilters.user_id"
                class="block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-orange-500 focus:border-orange-500"
              >
                <option value="">Tous les commerciaux</option>
                <option
                  v-for="user in commercials"
                  :key="user.id"
                  :value="user.id"
                >
                  {{ user.first_name }} {{ user.last_name }}
                </option>
              </select>
            </div>

            <div>
              <label class="block text-sm font-medium text-gray-700 mb-1">Période</label>
              <select
                v-model="particularFilters.period"
                class="block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-orange-500 focus:border-orange-500"
              >
                <option value="">Toutes les périodes</option>
                <option value="current">Période actuelle</option>
                <option value="upcoming">Périodes à venir</option>
                <option value="past">Périodes passées</option>
              </select>
            </div>

            <div>
              <label class="block text-sm font-medium text-gray-700 mb-1">Statut</label>
              <select
                v-model="particularFilters.status"
                class="block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-orange-500 focus:border-orange-500"
              >
                <option value="">Tous les statuts</option>
                <option value="active">Actif</option>
                <option value="completed">Terminé</option>
                <option value="overdue">En retard</option>
              </select>
            </div>

            <div class="flex items-end">
              <button
                @click="clearParticularFilters"
                class="w-full px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-md shadow-sm hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-orange-500"
              >
                <i class="fas fa-times mr-2"></i>
                Effacer
              </button>
            </div>
          </div>
        </div>
      </div>

      <!-- Loading State -->
      <div v-if="isLoading" class="bg-white shadow rounded-lg p-8">
        <div class="text-center">
          <i class="fas fa-spinner fa-spin text-3xl text-gray-400 mb-4"></i>
          <p class="text-gray-500">Chargement des objectifs...</p>
        </div>
      </div>

      <!-- Error State -->
      <div v-else-if="error" class="bg-white shadow rounded-lg p-8">
        <div class="text-center">
          <i class="fas fa-exclamation-circle text-3xl text-red-500 mb-4"></i>
          <p class="text-red-600">{{ error }}</p>
          <button
            @click="loadObjectives"
            class="mt-4 px-4 py-2 bg-orange-600 text-white rounded-md hover:bg-orange-700 focus:outline-none focus:ring-2 focus:ring-orange-500"
          >
            <i class="fas fa-redo mr-2"></i>
            Réessayer
          </button>
        </div>
      </div>

      <!-- Objectives Grid -->
      <div v-else class="grid grid-cols-1 lg:grid-cols-2 xl:grid-cols-2 gap-6">
        <div
          v-for="objective in displayedObjectives"
          :key="objective.id"
          class="bg-white shadow rounded-lg overflow-hidden hover:shadow-md transition-shadow duration-200"
        >
          <!-- Header -->
          <div class="px-6 py-4 border-b border-gray-200">
            <div class="flex items-center justify-between">
              <div class="flex items-center">
                <div class="flex-shrink-0 h-10 w-10">
                  <div :class="[
                    'h-10 w-10 rounded-full flex items-center justify-center',
                    objective.user ? 'bg-blue-100' : 'bg-orange-100'
                  ]">
                    <span :class="[
                      'text-sm font-medium',
                      objective.user ? 'text-blue-600' : 'text-orange-600'
                    ]">
                      {{ getUserInitials(objective.user) }}
                    </span>
                  </div>
                </div>
                <div class="ml-3">
                  <h3 class="text-lg font-medium text-gray-900">
                    {{ objective.user ? `${objective.user.first_name} ${objective.user.last_name}` : 'Objectif global par défaut' }}
                  </h3>
                  <p class="text-sm text-gray-500">
                    {{ formatMonth(objective.target_month) }} {{ objective.target_year }}
                  </p>
                </div>
              </div>
              <div class="flex items-center space-x-2">
                <button
                  @click="editObjective(objective)"
                  class="text-gray-400 hover:text-gray-600 p-1"
                  title="Modifier"
                >
                  <i class="fas fa-edit"></i>
                </button>
                <button
                  @click="deleteObjective(objective)"
                  class="text-gray-400 hover:text-red-600 p-1"
                  title="Supprimer"
                >
                  <i class="fas fa-trash"></i>
                </button>
              </div>
            </div>
          </div>

          <!-- Content -->
          <div class="px-6 py-4">
            <!-- Objective Info -->
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
              <div class="bg-blue-50 rounded-lg p-4">
                <div class="flex items-center justify-between">
                  <div>
                    <p class="text-xs font-medium text-blue-600 uppercase tracking-wide">
                      Objectif mensuel
                    </p>
                    <p class="mt-1 text-2xl font-bold text-blue-700">
                      {{ objective.monthly_target }}
                    </p>
                    <p class="text-xs text-blue-600">
                      candidatures
                    </p>
                  </div>
                  <div class="bg-blue-100 rounded-full p-3">
                    <i class="fas fa-bullseye text-blue-600 text-xl"></i>
                  </div>
                </div>
              </div>

              <div class="bg-green-50 rounded-lg p-4">
                <div class="flex items-center justify-between">
                  <div>
                    <p class="text-xs font-medium text-green-600 uppercase tracking-wide">
                      Période
                    </p>
                    <p class="mt-1 text-lg font-bold text-green-700">
                      {{ formatMonth(objective.target_month) }}
                    </p>
                    <p class="text-xs text-green-600">
                      {{ objective.target_year }}
                    </p>
                  </div>
                  <div class="bg-green-100 rounded-full p-3">
                    <i class="fas fa-calendar text-green-600 text-xl"></i>
                  </div>
                </div>
              </div>

              <div :class="[
                'rounded-lg p-4',
                objective.user ? 'bg-purple-50' : 'bg-orange-50'
              ]">
                <div class="flex items-center justify-between">
                  <div>
                    <p :class="[
                      'text-xs font-medium uppercase tracking-wide',
                      objective.user ? 'text-purple-600' : 'text-orange-600'
                    ]">
                      Type
                    </p>
                    <p :class="[
                      'mt-1 text-lg font-bold',
                      objective.user ? 'text-purple-700' : 'text-orange-700'
                    ]">
                      {{ objective.user ? 'Particulier' : 'Global' }}
                    </p>
                    <p :class="[
                      'text-xs',
                      objective.user ? 'text-purple-600' : 'text-orange-600'
                    ]">
                      {{ objective.user ? 'Spécifique' : 'Par défaut' }}
                    </p>
                  </div>
                  <div :class="[
                    'rounded-full p-3',
                    objective.user ? 'bg-purple-100' : 'bg-orange-100'
                  ]">
                    <i :class="[
                      'fas text-xl',
                      objective.user ? 'fa-user text-purple-600' : 'fa-users text-orange-600'
                    ]"></i>
                  </div>
                </div>
              </div>
            </div>

            <!-- Description -->
            <div v-if="objective.description" class="mt-4 p-3 bg-gray-50 rounded-md">
              <p class="text-sm text-gray-600">
                <i class="fas fa-info-circle mr-1"></i>
                {{ objective.description }}
              </p>
            </div>

            <!-- Progress (if data available) -->
            <div v-if="objective.current_progress !== undefined" class="mt-4">
              <div>
                <div class="flex items-center justify-between mb-2">
                  <span class="text-sm font-medium text-gray-700">Progression actuelle</span>
                  <span class="text-sm text-gray-500">
                    {{ objective.current_progress || 0 }} / {{ objective.monthly_target }}
                  </span>
                </div>
                <div class="w-full bg-gray-200 rounded-full h-2">
                  <div
                    class="bg-gradient-to-r from-orange-500 to-orange-600 h-2 rounded-full transition-all duration-300"
                    :style="{ width: getMonthlyProgress(objective) + '%' }"
                  ></div>
                </div>
                <div class="mt-1 text-xs text-gray-500">
                  {{ getMonthlyProgress(objective) }}% ce mois
                </div>
              </div>
            </div>

            <!-- Status -->
            <div class="mt-4 pt-4 border-t border-gray-200">
              <div class="flex items-center justify-between">
                <span
                  class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium"
                  :class="getObjectiveStatusClass(objective)"
                >
                  <i :class="getObjectiveStatusIcon(objective)" class="mr-1"></i>
                  {{ getObjectiveStatusText(objective) }}
                </span>
                <span v-show="false" class="text-xs text-gray-500">
                  Créé par {{ objective.created_by_user?.first_name }} {{ objective.created_by_user?.last_name }}
                </span>
              </div>
            </div>
          </div>
        </div>

        <!-- Empty State -->
        <div v-if="displayedObjectives.length === 0" class="col-span-full">
          <div class="text-center py-12">
            <i class="fas fa-target text-4xl text-gray-400 mb-4"></i>
            <h3 class="text-lg font-medium text-gray-900 mb-2">Aucun objectif trouvé</h3>
            <p class="text-gray-500 mb-6">
              {{ activeTab === 'global' ? 'Aucun objectif global trouvé.' : 'Aucun objectif particulier trouvé.' }}
            </p>
            <button
              @click="showCreateModal = true"
              class="inline-flex items-center px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-orange-600 hover:bg-orange-700"
            >
              <i class="fas fa-plus mr-2"></i>
              Créer le premier objectif
            </button>
          </div>
        </div>
      </div>

      <!-- Pagination -->
      <div v-if="pagination.total > pagination.per_page" class="mt-8">
        <div class="bg-white px-4 py-3 border border-gray-200 rounded-lg sm:px-6">
          <div class="flex items-center justify-between">
            <div class="flex-1 flex justify-between sm:hidden">
              <button
                @click="loadObjectives(pagination.current_page - 1)"
                :disabled="pagination.current_page <= 1"
                class="relative inline-flex items-center px-4 py-2 border border-gray-300 text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 disabled:opacity-50 disabled:cursor-not-allowed"
              >
                Précédent
              </button>
              <button
                @click="loadObjectives(pagination.current_page + 1)"
                :disabled="pagination.current_page >= pagination.last_page"
                class="ml-3 relative inline-flex items-center px-4 py-2 border border-gray-300 text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 disabled:opacity-50 disabled:cursor-not-allowed"
              >
                Suivant
              </button>
            </div>
            <div class="hidden sm:flex-1 sm:flex sm:items-center sm:justify-between">
              <div>
                <p class="text-sm text-gray-700">
                  Affichage de
                  <span class="font-medium">{{ (pagination.current_page - 1) * pagination.per_page + 1 }}</span>
                  à
                  <span class="font-medium">{{ Math.min(pagination.current_page * pagination.per_page, pagination.total) }}</span>
                  sur
                  <span class="font-medium">{{ pagination.total }}</span>
                  résultats
                </p>
              </div>
              <div>
                <nav class="relative z-0 inline-flex rounded-md shadow-sm -space-x-px" aria-label="Pagination">
                  <button
                    @click="loadObjectives(pagination.current_page - 1)"
                    :disabled="pagination.current_page <= 1"
                    class="relative inline-flex items-center px-2 py-2 rounded-l-md border border-gray-300 bg-white text-sm font-medium text-gray-500 hover:bg-gray-50 disabled:opacity-50 disabled:cursor-not-allowed"
                  >
                    <i class="fas fa-chevron-left"></i>
                  </button>
                  <button
                    @click="loadObjectives(pagination.current_page + 1)"
                    :disabled="pagination.current_page >= pagination.last_page"
                    class="relative inline-flex items-center px-2 py-2 rounded-r-md border border-gray-300 bg-white text-sm font-medium text-gray-500 hover:bg-gray-50 disabled:opacity-50 disabled:cursor-not-allowed"
                  >
                    <i class="fas fa-chevron-right"></i>
                  </button>
                </nav>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Modals -->
    <ObjectiveModal
      :show="showCreateModal"
      :objective="selectedObjective"
      :commercials="commercials"
      @close="closeModal"
      @success="handleObjectiveSuccess"
    />

    <ConfirmModal
      :show="showDeleteModal"
      title="Supprimer l'objectif"
      :message="`Êtes-vous sûr de vouloir supprimer l'objectif de ${selectedObjective?.user?.first_name} ${selectedObjective?.user?.last_name} ?`"
      confirm-text="Supprimer"
      confirm-class="bg-red-600 hover:bg-red-700 focus:ring-red-500"
      @confirm="handleDeleteConfirm"
      @close="showDeleteModal = false"
    />
  </div>
</template>

<script>
import { ref, computed, watch, onMounted } from 'vue'
import { useObjectiveStore } from '../stores/objective'
import { useUserManagementStore } from '../stores/userManagement'
import { useNotificationStore } from '../stores/notifications'
import ObjectiveModal from '../components/ObjectiveModal.vue'
import ConfirmModal from '../components/ConfirmModal.vue'

export default {
  name: 'ObjectiveManagement',
  components: {
    ObjectiveModal,
    ConfirmModal
  },
  setup() {
    const objectiveStore = useObjectiveStore()
    const userStore = useUserManagementStore()
    const notificationStore = useNotificationStore()

    // State
    const showCreateModal = ref(false)
    const showDeleteModal = ref(false)
    const selectedObjective = ref(null)
    const activeTab = ref('global') // 'global' ou 'particular'
    
    // Filtres pour objectifs globaux
    const globalFilters = ref({
      period: '',
      status: ''
    })

    // Filtres pour objectifs particuliers
    const particularFilters = ref({
      user_id: '',
      period: '',
      status: ''
    })

    // Computed
    const allObjectives = computed(() => objectiveStore.objectives)
    const pagination = computed(() => objectiveStore.pagination)
    const isLoading = computed(() => objectiveStore.isLoading)
    const error = computed(() => objectiveStore.error)
    const commercials = computed(() => userStore.commercials)
    
    // Séparer les objectifs globaux et particuliers
    const globalObjectives = computed(() => {
      return allObjectives.value.filter(obj => obj.user_id === null)
    })

    const particularObjectives = computed(() => {
      return allObjectives.value.filter(obj => obj.user_id !== null)
    })

    // Appliquer les filtres selon l'onglet actif
    const applyFilters = (objectivesList, filters) => {
      let filtered = [...objectivesList]
      
      // Filtre par commercial (seulement pour particuliers)
      if (filters.user_id) {
        filtered = filtered.filter(obj => obj.user_id === parseInt(filters.user_id))
      }
      
      // Filtre par période
      if (filters.period) {
        const now = new Date()
        const currentMonth = now.getMonth() + 1
        const currentYear = now.getFullYear()
        
        filtered = filtered.filter(obj => {
          const isPast = obj.target_year < currentYear || 
                        (obj.target_year === currentYear && obj.target_month < currentMonth)
          const isCurrent = obj.target_year === currentYear && obj.target_month === currentMonth
          const isFuture = obj.target_year > currentYear || 
                          (obj.target_year === currentYear && obj.target_month > currentMonth)
          
          if (filters.period === 'current') return isCurrent
          if (filters.period === 'upcoming') return isFuture
          if (filters.period === 'past') return isPast
          
          return true
        })
      }
      
      // Filtre par statut
      if (filters.status) {
        const now = new Date()
        const currentMonth = now.getMonth() + 1
        const currentYear = now.getFullYear()
        
        filtered = filtered.filter(obj => {
          const progress = getMonthlyProgress(obj)
          const isPast = obj.target_year < currentYear || 
                        (obj.target_year === currentYear && obj.target_month < currentMonth)
          const isCurrent = obj.target_year === currentYear && obj.target_month === currentMonth
          
          if (filters.status === 'active') {
            return isCurrent || (!isPast && !isCurrent)
          }
          if (filters.status === 'completed') {
            return isPast && progress >= 100
          }
          if (filters.status === 'overdue') {
            return isPast && progress < 100
          }
          
          return true
        })
      }
      
      return filtered
    }

    // Objectifs affichés selon l'onglet actif
    const displayedObjectives = computed(() => {
      if (activeTab.value === 'global') {
        return applyFilters(globalObjectives.value, globalFilters.value)
      } else {
        return applyFilters(particularObjectives.value, particularFilters.value)
      }
    })

    // Methods
    const loadObjectives = (page = 1) => {
      objectiveStore.fetchObjectives({ page })
    }

    const clearGlobalFilters = () => {
      globalFilters.value = {
        period: '',
        status: ''
      }
    }

    const clearParticularFilters = () => {
      particularFilters.value = {
        user_id: '',
        period: '',
        status: ''
      }
    }

    const loadCommercials = () => {
      userStore.fetchCommercials()
    }

    const getUserInitials = (user) => {
      if (!user) return 'GL' // GL = Global
      return `${user.first_name?.charAt(0) || ''}${user.last_name?.charAt(0) || ''}`.toUpperCase()
    }

    const formatMonth = (month) => {
      const months = [
        'Janvier', 'Février', 'Mars', 'Avril', 'Mai', 'Juin',
        'Juillet', 'Août', 'Septembre', 'Octobre', 'Novembre', 'Décembre'
      ]
      return months[month - 1] || ''
    }

    const formatPeriod = (start, end) => {
      if (!start || !end) return ''
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

    const getTotalProgress = (objective) => {
      // For monthly objectives, total progress = monthly progress
      return getMonthlyProgress(objective)
    }

    const getMonthlyProgress = (objective) => {
      if (!objective.monthly_target) return 0
      // Use current_progress if available (set by backend)
      if (objective.current_progress !== undefined) {
        const progress = (objective.current_progress || 0) / objective.monthly_target * 100
        return Math.min(100, Math.round(progress))
      }
      return 0
    }

    const getObjectiveStatusClass = (objective) => {
      const progress = getMonthlyProgress(objective)
      const now = new Date()
      const targetMonth = objective.target_month
      const targetYear = objective.target_year
      const currentMonth = now.getMonth() + 1
      const currentYear = now.getFullYear()
      
      // Check if the objective month has passed
      const isPast = targetYear < currentYear || (targetYear === currentYear && targetMonth < currentMonth)
      
      if (isPast) {
        return progress >= 100 ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800'
      }
      
      // Current or future month
      if (progress >= 90) return 'bg-green-100 text-green-800'
      if (progress >= 70) return 'bg-blue-100 text-blue-800'
      if (progress >= 50) return 'bg-yellow-100 text-yellow-800'
      return 'bg-orange-100 text-orange-800'
    }

    const getObjectiveStatusIcon = (objective) => {
      const progress = getMonthlyProgress(objective)
      const now = new Date()
      const targetMonth = objective.target_month
      const targetYear = objective.target_year
      const currentMonth = now.getMonth() + 1
      const currentYear = now.getFullYear()
      
      const isPast = targetYear < currentYear || (targetYear === currentYear && targetMonth < currentMonth)
      
      if (isPast) {
        return progress >= 100 ? 'fas fa-check-circle' : 'fas fa-times-circle'
      }
      
      if (progress >= 90) return 'fas fa-check-circle'
      if (progress >= 70) return 'fas fa-thumbs-up'
      if (progress >= 50) return 'fas fa-clock'
      return 'fas fa-exclamation-triangle'
    }

    const getObjectiveStatusText = (objective) => {
      const progress = getMonthlyProgress(objective)
      const now = new Date()
      const targetMonth = objective.target_month
      const targetYear = objective.target_year
      const currentMonth = now.getMonth() + 1
      const currentYear = now.getFullYear()
      
      const isPast = targetYear < currentYear || (targetYear === currentYear && targetMonth < currentMonth)
      
      if (isPast) {
        return progress >= 100 ? 'Objectif atteint' : 'Objectif manqué'
      }
      
      if (progress >= 90) return 'Excellent'
      if (progress >= 70) return 'Bon'
      if (progress >= 50) return 'Moyen'
      return 'En retard'
    }

    const editObjective = (objective) => {
      selectedObjective.value = objective
      showCreateModal.value = true
    }

    const deleteObjective = (objective) => {
      selectedObjective.value = objective
      showDeleteModal.value = true
    }

    const handleDeleteConfirm = async () => {
      const objectiveToDelete = selectedObjective.value
      
      const result = await objectiveStore.deleteObjective(objectiveToDelete.id)
      
      if (result.success) {
        // Message adapté selon le type d'objectif
        const message = objectiveToDelete.user 
          ? `L'objectif de ${objectiveToDelete.user.first_name} ${objectiveToDelete.user.last_name} a été supprimé.`
          : "L'objectif global a été supprimé."
        
        notificationStore.success(
          'Objectif supprimé',
          message
        )
      } else {
        console.error('Erreur lors de la suppression:', result.error)
        notificationStore.error(
          'Erreur',
          result.error?.response?.data?.message || 'Une erreur est survenue lors de la suppression'
        )
      }
      
      // Toujours fermer la modale et réinitialiser
      showDeleteModal.value = false
      selectedObjective.value = null
    }

    const closeModal = () => {
      showCreateModal.value = false
      selectedObjective.value = null
    }

    const handleObjectiveSuccess = () => {
      showCreateModal.value = false
      selectedObjective.value = null
      // Le store recharge automatiquement la liste
    }

    // Lifecycle
    onMounted(() => {
      loadObjectives()
      loadCommercials()
    })

    return {
      // Data
      activeTab,
      globalObjectives,
      particularObjectives,
      displayedObjectives,
      pagination,
      isLoading,
      error,
      globalFilters,
      particularFilters,
      commercials,
      showCreateModal,
      showDeleteModal,
      selectedObjective,

      // Methods
      loadObjectives,
      clearGlobalFilters,
      clearParticularFilters,
      getUserInitials,
      formatMonth,
      formatPeriod,
      getTotalProgress,
      getMonthlyProgress,
      getObjectiveStatusClass,
      getObjectiveStatusIcon,
      getObjectiveStatusText,
      editObjective,
      deleteObjective,
      handleDeleteConfirm,
      closeModal,
      handleObjectiveSuccess
    }
  }
}
</script>