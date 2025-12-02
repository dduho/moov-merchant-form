import ApiService from './ApiService'

const OrganizationService = {
  /**
   * Get paginated list of organizations
   */
  async getAll(params = {}) {
    const response = await ApiService.get('/organizations', { params })
    return response.data
  },

  /**
   * Get simple list of organizations (for dropdowns)
   */
  async getList() {
    const response = await ApiService.get('/organizations/list')
    return response.data
  },

  /**
   * Get a single organization by ID
   */
  async getById(id) {
    const response = await ApiService.get(`/organizations/${id}`)
    return response.data
  },

  /**
   * Create a new organization
   */
  async create(data) {
    const response = await ApiService.post('/organizations', data)
    return response.data
  },

  /**
   * Update an organization
   */
  async update(id, data) {
    const response = await ApiService.put(`/organizations/${id}`, data)
    return response.data
  },

  /**
   * Delete an organization
   */
  async delete(id) {
    const response = await ApiService.delete(`/organizations/${id}`)
    return response.data
  },

  /**
   * Toggle organization active status
   */
  async toggleActive(id) {
    const response = await ApiService.post(`/organizations/${id}/toggle-active`)
    return response.data
  },

  /**
   * Get organization users
   */
  async getUsers(id) {
    const response = await ApiService.get(`/organizations/${id}/users`)
    return response.data
  },

  /**
   * Get organization PDVs
   */
  async getPdvs(id, params = {}) {
    const response = await ApiService.get(`/organizations/${id}/pdvs`, { params })
    return response.data
  }
}

export default OrganizationService
