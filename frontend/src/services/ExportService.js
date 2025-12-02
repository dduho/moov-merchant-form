import ApiService from './ApiService'

const ExportService = {
  /**
   * Export all PDVs to XML
   */
  async exportXml() {
    const response = await ApiService.get('/export/xml', {
      responseType: 'blob'
    })
    return response.data
  },

  /**
   * Export PDVs by organization to XML
   */
  async exportByOrganization(organizationId) {
    const response = await ApiService.get(`/export/xml/organization/${organizationId}`, {
      responseType: 'blob'
    })
    return response.data
  },

  /**
   * Export PDVs by region to XML
   */
  async exportByRegion(region) {
    const response = await ApiService.get(`/export/xml/region/${region}`, {
      responseType: 'blob'
    })
    return response.data
  },

  /**
   * Export PDVs to CSV
   */
  async exportCsv() {
    const response = await ApiService.get('/export/csv', {
      responseType: 'blob'
    })
    return response.data
  },

  /**
   * Get statistics
   */
  async getStatistics() {
    const response = await ApiService.get('/export/statistics')
    return response.data
  },

  /**
   * Get heat map data
   */
  async getHeatMapData() {
    const response = await ApiService.get('/export/heatmap')
    return response.data
  },

  /**
   * Get commercial stats
   */
  async getCommercialStats(organizationId = null) {
    const params = organizationId ? { organization_id: organizationId } : {}
    const response = await ApiService.get('/export/commercial-stats', { params })
    return response.data
  },

  /**
   * Download blob as file
   */
  downloadBlob(blob, filename) {
    const url = window.URL.createObjectURL(blob)
    const link = document.createElement('a')
    link.href = url
    link.setAttribute('download', filename)
    document.body.appendChild(link)
    link.click()
    link.remove()
    window.URL.revokeObjectURL(url)
  }
}

export default ExportService
