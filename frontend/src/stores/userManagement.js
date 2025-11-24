import { defineStore } from 'pinia'
import { ref, computed } from 'vue'
import UserService from '../services/UserService'

export const useUserManagementStore = defineStore('userManagement', () => {
  const users = ref([])
  const commercials = ref([])
  const selectedUser = ref(null)
  const pagination = ref({
    current_page: 1,
    last_page: 1,
    per_page: 10,
    total: 0
  })
  const isLoading = ref(false)
  const error = ref(null)
  const filters = ref({
    role: '',
    status: '',
    search: ''
  })

  // Getters
  const filteredUsers = computed(() => {
    return users.value.filter(user => {
      const matchesRole = !filters.value.role || user.roles.some(role => role.slug === filters.value.role)
      const matchesStatus = !filters.value.status || getUserStatus(user) === filters.value.status
      const matchesSearch = !filters.value.search || 
        user.full_name.toLowerCase().includes(filters.value.search.toLowerCase()) ||
        user.email.toLowerCase().includes(filters.value.search.toLowerCase())
      
      return matchesRole && matchesStatus && matchesSearch
    })
  })

  const getUserStatus = (user) => {
    if (user.is_blocked) return 'blocked'
    if (!user.is_active) return 'inactive'
    return 'active'
  }

  const getUserStatusText = (user) => {
    const status = getUserStatus(user)
    switch (status) {
      case 'blocked': return 'Bloqué'
      case 'inactive': return 'Inactif'
      case 'active': return 'Actif'
      default: return 'Inconnu'
    }
  }

  const getUserStatusColor = (user) => {
    const status = getUserStatus(user)
    switch (status) {
      case 'blocked': return 'text-red-600 bg-red-100'
      case 'inactive': return 'text-gray-600 bg-gray-100'
      case 'active': return 'text-green-600 bg-green-100'
      default: return 'text-gray-600 bg-gray-100'
    }
  }

  // Actions
  const fetchUsers = async (params = {}) => {
    isLoading.value = true
    error.value = null
    
    try {
      const response = await UserService.getUsers({
        ...params,
        ...filters.value
      })
      
      users.value = response.data || []
      pagination.value = response.pagination || {}
    } catch (err) {
      error.value = err.message || 'Erreur lors du chargement des utilisateurs'
      users.value = []
    } finally {
      isLoading.value = false
    }
  }

  const resetUserPassword = async (userId, newPassword = 'password') => {
    try {
      const response = await UserService.resetUserPassword(userId, newPassword)
      
      // Mettre à jour l'utilisateur dans la liste
      const userIndex = users.value.findIndex(u => u.id === userId)
      if (userIndex !== -1) {
        users.value[userIndex].must_change_password = true
      }
      
      return response
    } catch (err) {
      error.value = err.message || 'Erreur lors de la réinitialisation du mot de passe'
      throw err
    }
  }

  const toggleUserBlock = async (userId) => {
    try {
      const response = await UserService.toggleUserBlock(userId)
      
      // Mettre à jour l'utilisateur dans la liste
      const userIndex = users.value.findIndex(u => u.id === userId)
      if (userIndex !== -1) {
        users.value[userIndex].is_blocked = response.data.is_blocked
      }
      
      return response
    } catch (err) {
      error.value = err.message || 'Erreur lors du changement de statut'
      throw err
    }
  }

  const toggleUserActive = async (userId) => {
    try {
      const response = await UserService.toggleUserActive(userId)
      
      // Mettre à jour l'utilisateur dans la liste
      const userIndex = users.value.findIndex(u => u.id === userId)
      if (userIndex !== -1) {
        users.value[userIndex].is_active = response.data.is_active
      }
      
      return response
    } catch (err) {
      error.value = err.message || 'Erreur lors du changement de statut'
      throw err
    }
  }

  const fetchUserStats = async (userId) => {
    try {
      const response = await UserService.getUserStats(userId)
      selectedUser.value = response.user
      return response
    } catch (err) {
      error.value = err.message || 'Erreur lors du chargement des statistiques'
      throw err
    }
  }

  const updateUser = async (userId, userData) => {
    try {
      const response = await UserService.updateUser(userId, userData)
      
      // Mettre à jour l'utilisateur dans la liste
      const userIndex = users.value.findIndex(u => u.id === userId)
      if (userIndex !== -1) {
        users.value[userIndex] = { ...users.value[userIndex], ...response.data }
      }
      
      return response
    } catch (err) {
      error.value = err.message || 'Erreur lors de la mise à jour de l\'utilisateur'
      throw err
    }
  }

  const getUserStats = async (userId) => {
    try {
      return await UserService.getUserStats(userId)
    } catch (err) {
      error.value = err.message || 'Erreur lors du chargement des statistiques'
      throw err
    }
  }

  const fetchCommercials = async () => {
    try {
      const response = await UserService.getCommercials()
      commercials.value = response.data || []
      return response
    } catch (err) {
      error.value = err.message || 'Erreur lors du chargement des commerciaux'
      commercials.value = []
      throw err
    }
  }

  const updateFilters = (newFilters) => {
    filters.value = { ...filters.value, ...newFilters }
  }

  const clearFilters = () => {
    filters.value = {
      role: '',
      status: '',
      search: ''
    }
  }

  const clearError = () => {
    error.value = null
  }

  return {
    // State
    users,
    commercials,
    selectedUser,
    pagination,
    isLoading,
    error,
    filters,
    
    // Getters
    filteredUsers,
    getUserStatus,
    getUserStatusText,
    getUserStatusColor,
    
    // Actions
    fetchUsers,
    resetUserPassword,
    toggleUserBlock,
    toggleUserActive,
    fetchUserStats,
    updateUser,
    getUserStats,
    fetchCommercials,
    updateFilters,
    clearFilters,
    clearError
  }
})