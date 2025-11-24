import axios from 'axios'

class UserService {
  constructor() {
    const apiBaseUrl = import.meta.env.VITE_API_URL || ''
    this.baseURL = apiBaseUrl ? `${apiBaseUrl}/api` : '/api'
    
    this.client = axios.create({
      baseURL: this.baseURL,
      withCredentials: true,
      headers: {
        'Content-Type': 'application/json',
        'Accept': 'application/json',
        'X-Requested-With': 'XMLHttpRequest'
      }
    })
    
    this.client.interceptors.response.use(
      response => response,
      error => {
        console.error('User Service Error:', error)
        return Promise.reject(error)
      }
    )
  }
  
  // Lister tous les utilisateurs avec filtres
  async getUsers(params = {}) {
    const response = await this.client.get('/users', { params })
    return response.data
  }
  
  // Réinitialiser le mot de passe d'un utilisateur
  async resetUserPassword(userId, newPassword = 'password') {
    const response = await this.client.post(`/users/${userId}/reset-password`, {
      new_password: newPassword
    })
    return response.data
  }
  
  // Bloquer/débloquer un utilisateur
  async toggleUserBlock(userId) {
    const response = await this.client.post(`/users/${userId}/toggle-block`)
    return response.data
  }
  
  // Activer/désactiver un utilisateur
  async toggleUserActive(userId) {
    const response = await this.client.post(`/users/${userId}/toggle-active`)
    return response.data
  }
  
  // Obtenir les statistiques détaillées d'un utilisateur
  async getUserStats(userId) {
    const response = await this.client.get(`/users/${userId}/stats`)
    return response.data
  }

  // Obtenir la liste des commerciaux pour attribution d'objectifs
  async getCommercials() {
    const response = await this.client.get('/users/commercials')
    return response.data
  }
  
  // Changer son propre mot de passe
  async changePassword(currentPassword, newPassword, confirmPassword) {
    const response = await this.client.post('/auth/change-password', {
      current_password: currentPassword,
      new_password: newPassword,
      new_password_confirmation: confirmPassword
    })
    return response.data
  }

  // Mettre à jour un utilisateur (admin uniquement)
  async updateUser(userId, userData) {
    const response = await this.client.put(`/users/${userId}`, userData)
    return response.data
  }
}

export default new UserService()