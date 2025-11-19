import axios from 'axios'

class ObjectiveService {
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
        console.error('Objective Service Error:', error)
        return Promise.reject(error)
      }
    )
  }
  
  // Lister les objectifs avec filtres
  async getObjectives(params = {}) {
    const response = await this.client.get('/objectives', { params })
    return response.data
  }
  
  // Créer un objectif
  async createObjective(objectiveData) {
    const response = await this.client.post('/objectives', objectiveData)
    return response.data
  }
  
  // Obtenir un objectif
  async getObjective(objectiveId) {
    const response = await this.client.get(`/objectives/${objectiveId}`)
    return response.data
  }
  
  // Mettre à jour un objectif
  async updateObjective(objectiveId, objectiveData) {
    const response = await this.client.put(`/objectives/${objectiveId}`, objectiveData)
    return response.data
  }
  
  // Supprimer un objectif
  async deleteObjective(objectiveId) {
    const response = await this.client.delete(`/objectives/${objectiveId}`)
    return response.data
  }
  
  // Définir des objectifs pour plusieurs utilisateurs
  async setBulkObjectives(objectiveData) {
    const response = await this.client.post('/objectives/bulk-set', objectiveData)
    return response.data
  }
  
  // Obtenir les statistiques de progression
  async getProgressStats(params = {}) {
    const response = await this.client.get('/objectives/progress-stats', { params })
    return response.data
  }
  
  // Obtenir l'objectif de l'utilisateur actuel
  async getCurrentUserObjective() {
    try {
      const response = await this.client.get('/objectives/current')
      return response.data
    } catch (error) {
      console.error('ObjectiveService: Error calling /objectives/current:', error)
      console.error('ObjectiveService: Error response:', error.response?.data)
      throw error
    }
  }
}

export default new ObjectiveService()