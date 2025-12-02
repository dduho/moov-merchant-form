import { defineStore } from 'pinia'
import { ref, computed } from 'vue'
import PointOfSaleService from '../services/PointOfSaleService'

export const usePointOfSaleStore = defineStore('pointOfSale', () => {
  // State
  const pdvs = ref([])
  const currentPdv = ref(null)
  const loading = ref(false)
  const error = ref(null)
  const pagination = ref({
    currentPage: 1,
    lastPage: 1,
    perPage: 15,
    total: 0
  })
  const filters = ref({
    status: '',
    region: '',
    prefecture: '',
    organization_id: '',
    search: ''
  })
  const mapData = ref([])
  const alertDistance = ref(300)
  const validationQueue = ref([])

  // Getters
  const hasPdvs = computed(() => pdvs.value.length > 0)
  const pendingCount = computed(() => 
    pdvs.value.filter(p => p.status === 'pending').length
  )
  const validatedCount = computed(() => 
    pdvs.value.filter(p => p.status === 'validated').length
  )

  // Actions
  async function fetchPdvs(page = 1) {
    loading.value = true
    error.value = null
    try {
      const params = {
        page,
        per_page: pagination.value.perPage,
        ...Object.fromEntries(
          Object.entries(filters.value).filter(([_, v]) => v !== '')
        )
      }
      const response = await PointOfSaleService.getAll(params)
      pdvs.value = response.data
      pagination.value = {
        currentPage: response.current_page,
        lastPage: response.last_page,
        perPage: response.per_page,
        total: response.total
      }
    } catch (err) {
      error.value = err.response?.data?.error || 'Erreur lors du chargement des PDV'
      throw err
    } finally {
      loading.value = false
    }
  }

  async function fetchPdv(id) {
    loading.value = true
    error.value = null
    try {
      const response = await PointOfSaleService.getById(id)
      currentPdv.value = response.data
      return currentPdv.value
    } catch (err) {
      error.value = err.response?.data?.error || 'Erreur lors du chargement du PDV'
      throw err
    } finally {
      loading.value = false
    }
  }

  async function createPdv(data) {
    loading.value = true
    error.value = null
    try {
      const response = await PointOfSaleService.create(data)
      pdvs.value.unshift(response.data)
      return response
    } catch (err) {
      error.value = err.response?.data?.error || 'Erreur lors de la création du PDV'
      throw err
    } finally {
      loading.value = false
    }
  }

  async function updatePdv(id, data) {
    loading.value = true
    error.value = null
    try {
      const response = await PointOfSaleService.update(id, data)
      const index = pdvs.value.findIndex(p => p.id === id)
      if (index !== -1) {
        pdvs.value[index] = response.data
      }
      if (currentPdv.value?.id === id) {
        currentPdv.value = response.data
      }
      return response
    } catch (err) {
      error.value = err.response?.data?.error || 'Erreur lors de la mise à jour du PDV'
      throw err
    } finally {
      loading.value = false
    }
  }

  async function deletePdv(id) {
    loading.value = true
    error.value = null
    try {
      await PointOfSaleService.delete(id)
      pdvs.value = pdvs.value.filter(p => p.id !== id)
      if (currentPdv.value?.id === id) {
        currentPdv.value = null
      }
    } catch (err) {
      error.value = err.response?.data?.error || 'Erreur lors de la suppression du PDV'
      throw err
    } finally {
      loading.value = false
    }
  }

  async function validatePdv(id) {
    loading.value = true
    error.value = null
    try {
      const response = await PointOfSaleService.validate(id)
      const index = pdvs.value.findIndex(p => p.id === id)
      if (index !== -1) {
        pdvs.value[index] = response.data
      }
      // Remove from validation queue
      validationQueue.value = validationQueue.value.filter(p => p.id !== id)
      return response
    } catch (err) {
      error.value = err.response?.data?.error || 'Erreur lors de la validation du PDV'
      throw err
    } finally {
      loading.value = false
    }
  }

  async function rejectPdv(id, reason) {
    loading.value = true
    error.value = null
    try {
      const response = await PointOfSaleService.reject(id, reason)
      const index = pdvs.value.findIndex(p => p.id === id)
      if (index !== -1) {
        pdvs.value[index] = response.data
      }
      // Remove from validation queue
      validationQueue.value = validationQueue.value.filter(p => p.id !== id)
      return response
    } catch (err) {
      error.value = err.response?.data?.error || 'Erreur lors du rejet du PDV'
      throw err
    } finally {
      loading.value = false
    }
  }

  async function checkProximity(latitude, longitude, excludeId = null) {
    try {
      const response = await PointOfSaleService.checkProximity(latitude, longitude, excludeId)
      return response
    } catch (err) {
      console.error('Error checking proximity:', err)
      return { has_nearby: false, nearby_pdvs: [] }
    }
  }

  async function fetchMapData(params = {}) {
    try {
      const response = await PointOfSaleService.getMapData(params)
      mapData.value = response.data
      alertDistance.value = response.alert_distance
      return response
    } catch (err) {
      error.value = err.response?.data?.error || 'Erreur lors du chargement des données de carte'
      throw err
    }
  }

  async function fetchValidationQueue(page = 1) {
    loading.value = true
    error.value = null
    try {
      const response = await PointOfSaleService.getValidationQueue({
        page,
        per_page: pagination.value.perPage
      })
      validationQueue.value = response.data
      pagination.value = {
        currentPage: response.current_page,
        lastPage: response.last_page,
        perPage: response.per_page,
        total: response.total
      }
    } catch (err) {
      error.value = err.response?.data?.error || 'Erreur lors du chargement de la file de validation'
      throw err
    } finally {
      loading.value = false
    }
  }

  function setFilter(key, value) {
    filters.value[key] = value
  }

  function resetFilters() {
    filters.value = {
      status: '',
      region: '',
      prefecture: '',
      organization_id: '',
      search: ''
    }
  }

  function clearError() {
    error.value = null
  }

  return {
    // State
    pdvs,
    currentPdv,
    loading,
    error,
    pagination,
    filters,
    mapData,
    alertDistance,
    validationQueue,
    // Getters
    hasPdvs,
    pendingCount,
    validatedCount,
    // Actions
    fetchPdvs,
    fetchPdv,
    createPdv,
    updatePdv,
    deletePdv,
    validatePdv,
    rejectPdv,
    checkProximity,
    fetchMapData,
    fetchValidationQueue,
    setFilter,
    resetFilters,
    clearError
  }
})
