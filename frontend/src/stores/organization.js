import { defineStore } from 'pinia'
import { ref, computed } from 'vue'
import OrganizationService from '../services/OrganizationService'

export const useOrganizationStore = defineStore('organization', () => {
  // State
  const organizations = ref([])
  const organizationsList = ref([])
  const currentOrganization = ref(null)
  const loading = ref(false)
  const error = ref(null)
  const pagination = ref({
    currentPage: 1,
    lastPage: 1,
    perPage: 15,
    total: 0
  })

  // Getters
  const hasOrganizations = computed(() => organizations.value.length > 0)
  const activeOrganizations = computed(() => 
    organizationsList.value.filter(o => o.is_active !== false)
  )

  // Actions
  async function fetchOrganizations(page = 1, params = {}) {
    loading.value = true
    error.value = null
    try {
      const response = await OrganizationService.getAll({
        page,
        per_page: pagination.value.perPage,
        ...params
      })
      organizations.value = response.data
      pagination.value = {
        currentPage: response.current_page,
        lastPage: response.last_page,
        perPage: response.per_page,
        total: response.total
      }
    } catch (err) {
      error.value = err.response?.data?.error || 'Erreur lors du chargement des organisations'
      throw err
    } finally {
      loading.value = false
    }
  }

  async function fetchOrganizationsList() {
    try {
      const response = await OrganizationService.getList()
      organizationsList.value = response.data
      return organizationsList.value
    } catch (err) {
      console.error('Error fetching organizations list:', err)
      return []
    }
  }

  async function fetchOrganization(id) {
    loading.value = true
    error.value = null
    try {
      const response = await OrganizationService.getById(id)
      currentOrganization.value = response.data
      return currentOrganization.value
    } catch (err) {
      error.value = err.response?.data?.error || 'Erreur lors du chargement de l\'organisation'
      throw err
    } finally {
      loading.value = false
    }
  }

  async function createOrganization(data) {
    loading.value = true
    error.value = null
    try {
      const response = await OrganizationService.create(data)
      organizations.value.unshift(response.data)
      organizationsList.value.push({
        id: response.data.id,
        name: response.data.name,
        code: response.data.code
      })
      return response
    } catch (err) {
      error.value = err.response?.data?.error || 'Erreur lors de la création de l\'organisation'
      throw err
    } finally {
      loading.value = false
    }
  }

  async function updateOrganization(id, data) {
    loading.value = true
    error.value = null
    try {
      const response = await OrganizationService.update(id, data)
      const index = organizations.value.findIndex(o => o.id === id)
      if (index !== -1) {
        organizations.value[index] = response.data
      }
      // Update list too
      const listIndex = organizationsList.value.findIndex(o => o.id === id)
      if (listIndex !== -1) {
        organizationsList.value[listIndex] = {
          id: response.data.id,
          name: response.data.name,
          code: response.data.code
        }
      }
      if (currentOrganization.value?.id === id) {
        currentOrganization.value = response.data
      }
      return response
    } catch (err) {
      error.value = err.response?.data?.error || 'Erreur lors de la mise à jour de l\'organisation'
      throw err
    } finally {
      loading.value = false
    }
  }

  async function deleteOrganization(id) {
    loading.value = true
    error.value = null
    try {
      await OrganizationService.delete(id)
      organizations.value = organizations.value.filter(o => o.id !== id)
      organizationsList.value = organizationsList.value.filter(o => o.id !== id)
      if (currentOrganization.value?.id === id) {
        currentOrganization.value = null
      }
    } catch (err) {
      error.value = err.response?.data?.error || 'Erreur lors de la suppression de l\'organisation'
      throw err
    } finally {
      loading.value = false
    }
  }

  async function toggleOrganizationActive(id) {
    loading.value = true
    error.value = null
    try {
      const response = await OrganizationService.toggleActive(id)
      const index = organizations.value.findIndex(o => o.id === id)
      if (index !== -1) {
        organizations.value[index] = response.data
      }
      return response
    } catch (err) {
      error.value = err.response?.data?.error || 'Erreur lors du changement de statut'
      throw err
    } finally {
      loading.value = false
    }
  }

  function clearError() {
    error.value = null
  }

  return {
    // State
    organizations,
    organizationsList,
    currentOrganization,
    loading,
    error,
    pagination,
    // Getters
    hasOrganizations,
    activeOrganizations,
    // Actions
    fetchOrganizations,
    fetchOrganizationsList,
    fetchOrganization,
    createOrganization,
    updateOrganization,
    deleteOrganization,
    toggleOrganizationActive,
    clearError
  }
})
