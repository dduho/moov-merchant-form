import ApiService from './ApiService'

const GeographyService = {
  /**
   * Get all regions
   */
  async getRegions() {
    const response = await ApiService.get('/geography/regions')
    return response.data
  },

  /**
   * Get prefectures for a region
   */
  async getPrefectures(regionId) {
    const response = await ApiService.get('/geography/prefectures', {
      params: { region_id: regionId }
    })
    return response.data
  },

  /**
   * Get communes for a prefecture
   */
  async getCommunes(prefectureId) {
    const response = await ApiService.get('/geography/communes', {
      params: { prefecture_id: prefectureId }
    })
    return response.data
  },

  /**
   * Get cantons for a commune
   */
  async getCantons(communeId) {
    const response = await ApiService.get('/geography/cantons', {
      params: { commune_id: communeId }
    })
    return response.data
  },

  /**
   * Get villes for a canton
   */
  async getVilles(cantonId) {
    const response = await ApiService.get('/geography/villes', {
      params: { canton_id: cantonId }
    })
    return response.data
  },

  /**
   * Get children of a location
   */
  async getChildren(parentId) {
    const response = await ApiService.get('/geography/children', {
      params: { parent_id: parentId }
    })
    return response.data
  },

  /**
   * Get the full hierarchy
   */
  async getFullHierarchy() {
    const response = await ApiService.get('/geography/full-hierarchy')
    return response.data
  },

  /**
   * Search locations
   */
  async search(query, type = null) {
    const params = { query }
    if (type) params.type = type
    const response = await ApiService.get('/geography/search', { params })
    return response.data
  },

  /**
   * Get a location with ancestors
   */
  async getLocation(id) {
    const response = await ApiService.get(`/geography/${id}`)
    return response.data
  }
}

export default GeographyService
