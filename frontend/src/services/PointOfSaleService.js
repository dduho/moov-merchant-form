import ApiService from './ApiService'

const PointOfSaleService = {
  /**
   * Get paginated list of PDVs with filters
   */
  async getAll(params = {}) {
    const response = await ApiService.get('/point-of-sales', { params })
    return response.data
  },

  /**
   * Get a single PDV by ID
   */
  async getById(id) {
    const response = await ApiService.get(`/point-of-sales/${id}`)
    return response.data
  },

  /**
   * Create a new PDV
   */
  async create(data) {
    const response = await ApiService.post('/point-of-sales', data)
    return response.data
  },

  /**
   * Update an existing PDV
   */
  async update(id, data) {
    const response = await ApiService.put(`/point-of-sales/${id}`, data)
    return response.data
  },

  /**
   * Delete a PDV
   */
  async delete(id) {
    const response = await ApiService.delete(`/point-of-sales/${id}`)
    return response.data
  },

  /**
   * Validate a PDV (admin only)
   */
  async validate(id) {
    const response = await ApiService.post(`/point-of-sales/${id}/validate`)
    return response.data
  },

  /**
   * Reject a PDV (admin only)
   */
  async reject(id, reason) {
    const response = await ApiService.post(`/point-of-sales/${id}/reject`, { reason })
    return response.data
  },

  /**
   * Check proximity for a location
   */
  async checkProximity(latitude, longitude, excludeId = null) {
    const params = { latitude, longitude }
    if (excludeId) params.exclude_id = excludeId
    const response = await ApiService.post('/point-of-sales/check-proximity', params)
    return response.data
  },

  /**
   * Get map data for all PDVs
   */
  async getMapData(params = {}) {
    const response = await ApiService.get('/point-of-sales/map-data', { params })
    return response.data
  },

  /**
   * Get validation queue (pending PDVs)
   */
  async getValidationQueue(params = {}) {
    const response = await ApiService.get('/point-of-sales/validation-queue', { params })
    return response.data
  },

  /**
   * Get regions list
   */
  async getRegions() {
    const response = await ApiService.get('/point-of-sales/regions')
    return response.data
  }
}

export default PointOfSaleService
