<template>
  <div class="bg-white rounded-2xl shadow-sm p-6">
    <!-- Header -->
    <div class="flex items-center justify-between mb-6">
      <div class="flex items-center">
        <div class="w-12 h-12 bg-orange-100 rounded-xl flex items-center justify-center">
          <i class="fas fa-bullseye text-orange-600 text-xl"></i>
        </div>
        <div class="ml-4">
          <h3 class="text-lg font-semibold text-gray-900">Mes Objectifs</h3>
          <p class="text-sm text-gray-500">Progression actuelle</p>
        </div>
      </div>
      <button
        v-if="!isLoading && !error"
        @click="refreshObjectives"
        class="p-2 text-gray-400 hover:text-gray-600 rounded-lg hover:bg-gray-50 transition-colors"
        title="Actualiser"
      >
        <i class="fas fa-redo text-sm" :class="{ 'animate-spin': refreshing }"></i>
      </button>
    </div>

    <!-- Loading State -->
    <div v-if="isLoading" class="space-y-4">
      <div v-for="i in 2" :key="i" class="animate-pulse">
        <div class="h-4 bg-gray-200 rounded w-1/4 mb-2"></div>
        <div class="h-2 bg-gray-200 rounded mb-1"></div>
        <div class="h-3 bg-gray-200 rounded w-1/3"></div>
      </div>
    </div>

    <!-- Error State -->
    <div v-else-if="error" class="text-center py-8">
      <i class="fas fa-exclamation-circle text-3xl text-red-400 mb-4"></i>
      <p class="text-red-600 text-sm mb-4">{{ error }}</p>
      <button
        @click="loadObjectives"
        class="px-4 py-2 bg-red-600 text-white text-sm rounded-lg hover:bg-red-700 transition-colors"
      >
        <i class="fas fa-redo mr-2"></i>
        Réessayer
      </button>
    </div>

    <!-- No Objectives -->
    <div v-else-if="!currentObjective" class="text-center py-8">
      <i class="fas fa-target text-3xl text-gray-400 mb-4"></i>
      <h4 class="text-lg font-medium text-gray-700 mb-2">Aucun objectif défini</h4>
      <p class="text-gray-500 text-sm">
        Contactez votre administrateur pour définir vos objectifs.
      </p>
    </div>

    <!-- Current Objective -->
    <div v-else class="space-y-6">
      <!-- Period Info -->
      <div class="bg-blue-50 rounded-lg p-4">
        <div class="flex items-center justify-between mb-2">
          <span class="text-sm font-medium text-blue-800">{{ formatMonth(currentObjective.target_month) }} {{ currentObjective.target_year }}</span>
          <span
            class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium"
            :class="getObjectiveStatusClass()"
          >
            <i :class="getObjectiveStatusIcon()" class="mr-1"></i>
            {{ getObjectiveStatusText() }}
          </span>
        </div>
        <p class="text-sm text-blue-700">
          {{ objectiveTypeText }}
        </p>
      </div>

      <!-- Progress Bars -->
      <div class="space-y-4">
        <!-- Monthly Progress -->
        <div>
          <div class="flex items-center justify-between mb-2">
            <span class="text-sm font-medium text-gray-700">Objectif du mois</span>
            <span class="text-sm text-gray-600">
              {{ currentProgress.monthly || 0 }} / {{ currentObjective.monthly_target }}
            </span>
          </div>
          <div class="w-full bg-gray-200 rounded-full h-3">
            <div
              class="bg-gradient-to-r from-orange-500 to-orange-600 h-3 rounded-full transition-all duration-500"
              :style="{ width: monthlyProgressPercentage + '%' }"
            ></div>
          </div>
          <div class="flex items-center justify-between mt-1">
            <span class="text-xs text-gray-500">{{ monthlyProgressPercentage }}% complété</span>
            <span v-if="monthlyTarget > 0" class="text-xs text-gray-500">
              {{ monthlyRemaining }} restant{{ monthlyRemaining > 1 ? 's' : '' }}
            </span>
          </div>
        </div>
      </div>

      <!-- Performance Indicators -->
      <div class="grid grid-cols-2 gap-4">
        <div class="text-center p-3 bg-gray-50 rounded-lg">
          <div class="text-2xl font-bold text-gray-900">{{ currentProgress.today || 0 }}</div>
          <div class="text-xs text-gray-600 uppercase tracking-wide">Aujourd'hui</div>
        </div>
        <div class="text-center p-3 bg-gray-50 rounded-lg">
          <div class="text-2xl font-bold text-gray-900">{{ currentProgress.week || 0 }}</div>
          <div class="text-xs text-gray-600 uppercase tracking-wide">Cette semaine</div>
        </div>
      </div>

      <!-- Motivation Message -->
      <div v-if="motivationMessage" class="p-4 rounded-lg" :class="motivationClass">
        <div class="flex items-start">
          <div class="flex-shrink-0">
            <i :class="motivationIcon" class="text-lg"></i>
          </div>
          <div class="ml-3">
            <p class="text-sm font-medium">{{ motivationMessage.title }}</p>
            <p class="text-xs mt-1">{{ motivationMessage.text }}</p>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
import { ref, computed, watch, onMounted, onUnmounted } from 'vue'
import { useObjectiveStore } from '../stores/objective'
import { useAuthStore } from '../stores/auth'

export default {
  name: 'ObjectiveWidget',
  setup() {
    const objectiveStore = useObjectiveStore()
    const authStore = useAuthStore()

    // State
    const isLoading = ref(false)
    const refreshing = ref(false)
    const error = ref(null)
    const currentObjective = ref(null)
    const currentProgress = ref({
      today: 0,
      week: 0,
      monthly: 0,
      total: 0
    })

    let refreshInterval = null

    // Computed
    const isCommercial = computed(() => {
      // Utiliser directement le getter du store auth pour éviter les incohérences
      return authStore.isCommercial
    })

    const monthlyTarget = computed(() => currentObjective.value?.monthly_target || 0)

    const monthlyProgressPercentage = computed(() => {
      if (!monthlyTarget.value) return 0
      const progress = currentProgress.value.monthly || 0
      return Math.min(100, Math.round((progress / monthlyTarget.value) * 100))
    })

    const monthlyRemaining = computed(() => {
      return Math.max(0, monthlyTarget.value - (currentProgress.value.monthly || 0))
    })
    
    const objectiveTypeText = computed(() => {
      if (!currentObjective.value) return ''
      return currentObjective.value.user_id 
        ? 'Objectif particulier' 
        : 'Objectif global par défaut'
    })

    const motivationMessage = computed(() => {
      if (!currentObjective.value) return null

      const monthlyPercent = monthlyProgressPercentage.value

      if (monthlyPercent >= 100) {
        return {
          title: '🎉 Objectif mensuel atteint !',
          text: 'Félicitations ! Continuez sur cette lancée.',
          type: 'success'
        }
      }

      if (monthlyPercent >= 80) {
        return {
          title: '🔥 Excellent travail !',
          text: `Plus que ${monthlyRemaining.value} candidature${monthlyRemaining.value > 1 ? 's' : ''} pour atteindre votre objectif mensuel.`,
          type: 'success'
        }
      }

      if (monthlyPercent >= 60) {
        return {
          title: '💪 Bon rythme !',
          text: 'Vous êtes sur la bonne voie pour atteindre vos objectifs.',
          type: 'info'
        }
      }

      if (monthlyPercent >= 40) {
        return {
          title: '⚡ Accélérez !',
          text: 'Il est temps d\'intensifier vos efforts pour rattraper le retard.',
          type: 'warning'
        }
      }

      return {
        title: '🎯 Action requise',
        text: 'Vous êtes en retard sur vos objectifs. Contactez votre manager si besoin.',
        type: 'danger'
      }
    })

    const motivationClass = computed(() => {
      if (!motivationMessage.value) return ''
      
      switch (motivationMessage.value.type) {
        case 'success':
          return 'bg-green-50 border border-green-200 text-green-800'
        case 'info':
          return 'bg-blue-50 border border-blue-200 text-blue-800'
        case 'warning':
          return 'bg-yellow-50 border border-yellow-200 text-yellow-800'
        case 'danger':
          return 'bg-red-50 border border-red-200 text-red-800'
        default:
          return 'bg-gray-50 border border-gray-200 text-gray-800'
      }
    })

    const motivationIcon = computed(() => {
      if (!motivationMessage.value) return 'fas fa-info-circle'
      
      switch (motivationMessage.value.type) {
        case 'success':
          return 'fas fa-check-circle text-green-600'
        case 'info':
          return 'fas fa-info-circle text-blue-600'
        case 'warning':
          return 'fas fa-exclamation-triangle text-yellow-600'
        case 'danger':
          return 'fas fa-times-circle text-red-600'
        default:
          return 'fas fa-info-circle text-gray-600'
      }
    })

    // Methods
    const formatMonth = (month) => {
      const months = [
        'Janvier', 'Février', 'Mars', 'Avril', 'Mai', 'Juin',
        'Juillet', 'Août', 'Septembre', 'Octobre', 'Novembre', 'Décembre'
      ]
      return months[month - 1] || ''
    }

    const getObjectiveStatusClass = () => {
      const monthlyPercent = monthlyProgressPercentage.value
      
      if (monthlyPercent >= 100) return 'bg-green-100 text-green-800'
      if (monthlyPercent >= 80) return 'bg-blue-100 text-blue-800'
      if (monthlyPercent >= 60) return 'bg-yellow-100 text-yellow-800'
      return 'bg-red-100 text-red-800'
    }

    const getObjectiveStatusIcon = () => {
      const monthlyPercent = monthlyProgressPercentage.value
      
      if (monthlyPercent >= 100) return 'fas fa-check-circle'
      if (monthlyPercent >= 80) return 'fas fa-thumbs-up'
      if (monthlyPercent >= 60) return 'fas fa-clock'
      return 'fas fa-exclamation-triangle'
    }

    const getObjectiveStatusText = () => {
      const monthlyPercent = monthlyProgressPercentage.value
      
      if (monthlyPercent >= 100) return 'Objectif atteint'
      if (monthlyPercent >= 80) return 'Excellent'
      if (monthlyPercent >= 60) return 'Bon'
      if (monthlyPercent >= 40) return 'Moyen'
      return 'En retard'
    }

    const loadObjectives = async () => {
      console.log('loadObjectives called, isCommercial:', isCommercial.value)
      
      if (!isCommercial.value) {
        console.log('User is not commercial, skipping load')
        return
      }

      try {
        isLoading.value = true
        error.value = null

        console.log('Calling getCurrentUserObjective...')
        const response = await objectiveStore.getCurrentUserObjective()
        console.log('getCurrentUserObjective response:', response)
        
        if (response.data) {
          currentObjective.value = response.data.objective
          currentProgress.value = response.data.progress
          console.log('Objectives loaded successfully:', {
            objective: currentObjective.value,
            progress: currentProgress.value
          })
        } else {
          console.log('No data in response:', response)
        }
      } catch (err) {
        console.error('Error loading objectives:', err)
        console.error('Error details:', err.response?.data)
        error.value = err.response?.data?.message || 'Erreur lors du chargement des objectifs'
      } finally {
        isLoading.value = false
      }
    }

    const refreshObjectives = async () => {
      console.log('refreshObjectives called, isCommercial:', isCommercial.value, 'refreshing:', refreshing.value)
      
      if (!isCommercial.value || refreshing.value) {
        console.log('Skipping refresh')
        return
      }

      try {
        refreshing.value = true
        console.log('Refreshing objectives...')
        const response = await objectiveStore.getCurrentUserObjective()
        console.log('Refresh response:', response)
        
        if (response.data) {
          currentObjective.value = response.data.objective
          currentProgress.value = response.data.progress
          console.log('Objectives refreshed successfully')
        }
      } catch (err) {
        console.error('Error refreshing objectives:', err)
        console.error('Error details:', err.response?.data)
      } finally {
        refreshing.value = false
      }
    }

    // Lifecycle
    onMounted(() => {
      console.log('ObjectiveWidget mounted')
      console.log('User:', authStore.user)
      console.log('authStore.isCommercial:', authStore.isCommercial)
      console.log('isCommercial.value:', isCommercial.value)
      
      // Charger les objectifs si l'utilisateur est déjà authentifié
      if (isCommercial.value) {
        console.log('User is commercial on mount, loading objectives...')
        loadObjectives()
        
        // Auto refresh every 5 minutes
        refreshInterval = setInterval(() => {
          refreshObjectives()
        }, 5 * 60 * 1000)
      } else {
        console.log('User is NOT commercial on mount, will wait for watch trigger')
      }
    })

    // Watch pour détecter quand l'utilisateur devient commercial
    watch(() => isCommercial.value, (newValue, oldValue) => {
      console.log('isCommercial changed:', { old: oldValue, new: newValue })
      
      if (newValue && !oldValue) {
        console.log('User is now commercial, loading objectives...')
        loadObjectives()
        
        // Démarrer le refresh automatique
        if (!refreshInterval) {
          refreshInterval = setInterval(() => {
            refreshObjectives()
          }, 5 * 60 * 1000)
        }
      }
    }, { immediate: false })

    onUnmounted(() => {
      if (refreshInterval) {
        clearInterval(refreshInterval)
      }
    })

    return {
      isLoading,
      refreshing,
      error,
      currentObjective,
      currentProgress,
      isCommercial,
      monthlyTarget,
      monthlyProgressPercentage,
      monthlyRemaining,
      objectiveTypeText,
      motivationMessage,
      motivationClass,
      motivationIcon,
      formatMonth,
      getObjectiveStatusClass,
      getObjectiveStatusIcon,
      getObjectiveStatusText,
      loadObjectives,
      refreshObjectives
    }
  }
}
</script>