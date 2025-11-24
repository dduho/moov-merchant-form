import { defineStore } from 'pinia'
import { ref, computed } from 'vue'
import ObjectiveService from '../services/ObjectiveService'

export const useObjectiveStore = defineStore('objective', () => {
  const objectives = ref([])
  const progressStats = ref([])
  const summary = ref({})
  const pagination = ref({
    current_page: 1,
    last_page: 1,
    per_page: 15,
    total: 0
  })
  const isLoading = ref(false)
  const error = ref(null)
  const filters = ref({
    user_id: '',
    year: new Date().getFullYear(),
    month: null
    // active: true - Retiré pour charger TOUS les objectifs par défaut
  })

  // Getters
  const currentYearObjectives = computed(() => {
    return objectives.value.filter(obj => 
      obj.target_year === filters.value.year
    )
  })

  const monthlyObjectives = computed(() => {
    return objectives.value.filter(obj => 
      obj.target_year === filters.value.year && 
      obj.target_month !== null
    )
  })

  const yearlyObjectives = computed(() => {
    return objectives.value.filter(obj => 
      obj.target_year === filters.value.year && 
      obj.target_month === null
    )
  })

  // Actions
  const fetchObjectives = async (params = {}) => {
    isLoading.value = true
    error.value = null
    
    try {
      // Nettoyer les filtres vides
      const cleanFilters = Object.entries({...params, ...filters.value})
        .filter(([_, value]) => value !== '' && value !== null && value !== undefined)
        .reduce((acc, [key, value]) => ({ ...acc, [key]: value }), {})
      
      const response = await ObjectiveService.getObjectives(cleanFilters)
      
      objectives.value = response.data || []
      pagination.value = response.pagination || {
        current_page: 1,
        last_page: 1,
        per_page: 15,
        total: 0
      }
    } catch (err) {
      error.value = err.message || 'Erreur lors du chargement des objectifs'
      objectives.value = []
    } finally {
      isLoading.value = false
    }
  }

  const createObjective = async (objectiveData) => {
    try {
      const response = await ObjectiveService.createObjective(objectiveData)
      
      // Recharger la liste complète pour avoir toutes les relations
      await fetchObjectives()
      
      return response
    } catch (err) {
      error.value = err.message || 'Erreur lors de la création de l\'objectif'
      throw err
    }
  }

  const updateObjective = async (objectiveId, objectiveData) => {
    try {
      const response = await ObjectiveService.updateObjective(objectiveId, objectiveData)
      
      // Recharger la liste complète pour avoir toutes les relations
      await fetchObjectives()
      
      return response
    } catch (err) {
      error.value = err.message || 'Erreur lors de la mise à jour de l\'objectif'
      throw err
    }
  }

  const deleteObjective = async (objectiveId) => {
    try {
      const response = await ObjectiveService.deleteObjective(objectiveId)
      
      // Supprimer l'objectif de la liste
      const index = objectives.value.findIndex(obj => obj.id === objectiveId)
      if (index !== -1) {
        objectives.value.splice(index, 1)
      }
      
      // Retourner la réponse, pas d'erreur
      return { success: true, data: response }
    } catch (err) {
      error.value = err.message || 'Erreur lors de la suppression de l\'objectif'
      // Retourner l'erreur sans lancer d'exception
      return { success: false, error: err }
    }
  }

  const setBulkObjectives = async (objectiveData) => {
    isLoading.value = true
    try {
      const response = await ObjectiveService.setBulkObjectives(objectiveData)
      
      // Recharger les objectifs après la définition en masse
      await fetchObjectives()
      
      return response
    } catch (err) {
      error.value = err.message || 'Erreur lors de la définition des objectifs'
      throw err
    } finally {
      isLoading.value = false
    }
  }

  const fetchProgressStats = async (params = {}) => {
    isLoading.value = true
    error.value = null
    
    try {
      const response = await ObjectiveService.getProgressStats({
        ...params,
        year: filters.value.year,
        month: filters.value.month
      })
      
      progressStats.value = response.data || []
      summary.value = response.summary || {}
      
      return response
    } catch (err) {
      error.value = err.message || 'Erreur lors du chargement des statistiques'
      progressStats.value = []
      summary.value = {}
    } finally {
      isLoading.value = false
    }
  }

  const updateFilters = (newFilters) => {
    filters.value = { ...filters.value, ...newFilters }
  }

  const setYear = (year) => {
    filters.value.year = year
  }

  const setMonth = (month) => {
    filters.value.month = month
  }

  const clearFilters = () => {
    filters.value = {
      user_id: '',
      year: new Date().getFullYear(),
      month: null,
      active: true
    }
  }

  const clearError = () => {
    error.value = null
  }

  // Utilities
  const getProgressColor = (percentage) => {
    if (percentage >= 100) return 'text-green-600 bg-green-100'
    if (percentage >= 80) return 'text-blue-600 bg-blue-100'
    if (percentage >= 60) return 'text-yellow-600 bg-yellow-100'
    if (percentage >= 40) return 'text-orange-600 bg-orange-100'
    return 'text-red-600 bg-red-100'
  }

  const getProgressStatus = (percentage) => {
    if (percentage >= 100) return 'completed'
    if (percentage >= 80) return 'on_track'
    return 'behind'
  }

  const getProgressStatusText = (percentage) => {
    const status = getProgressStatus(percentage)
    switch (status) {
      case 'completed': return 'Objectif atteint'
      case 'on_track': return 'En bonne voie'
      case 'behind': return 'En retard'
      default: return 'En cours'
    }
  }

  return {
    // State
    objectives,
    progressStats,
    summary,
    pagination,
    isLoading,
    error,
    filters,
    
    // Getters
    currentYearObjectives,
    monthlyObjectives,
    yearlyObjectives,
    
    // Actions
    fetchObjectives,
    createObjective,
    updateObjective,
    deleteObjective,
    setBulkObjectives,
    fetchProgressStats,
    getCurrentUserObjective: async () => {
      try {
        const response = await ObjectiveService.getCurrentUserObjective()
        return response
      } catch (err) {
        console.error('Store: Error in getCurrentUserObjective:', err)
        console.error('Store: Error response:', err.response?.data)
        throw err
      }
    },
    updateFilters,
    setYear,
    setMonth,
    clearFilters,
    clearError,
    
    // Utilities
    getProgressColor,
    getProgressStatus,
    getProgressStatusText
  }
})