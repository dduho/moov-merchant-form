import axios from 'axios'

const API_URL = import.meta.env.VITE_API_URL || ''

class ApiService {
    constructor() {
        this.client = axios.create({
            baseURL: API_URL + '/api',
            withCredentials: true, // Important pour les cookies de session
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json'
            }
        })

        // Interceptor pour gérer les erreurs d'authentification
        this.client.interceptors.response.use(
            (response) => response,
            (error) => {
                if (error.response?.status === 401) {
                    // Optionnel: rediriger vers la page de connexion
                    // window.location.href = '/login';
                }
                return Promise.reject(error);
            }
        );
    }
    
    // ============================================================
    // MERCHANT APPLICATIONS
    // ============================================================
    
    // Liste des candidatures
    async getApplications(params = {}) {
        return this.client.get('/merchant-applications', { params })
    }
    
    // Créer candidature
    async createApplication(data) {
        return this.client.post('/merchant-applications', data, {
            headers: { 'Content-Type': 'multipart/form-data' }
        })
    }
    
    // Voir candidature
    async getApplication(id) {
        return this.client.get(`/merchant-applications/${id}`)
    }
    
    // Voir par référence
    async getApplicationByReference(reference) {
        return this.client.get(`/merchant-applications/reference/${reference}`)
    }
    
    // Mettre à jour
    async updateApplication(id, data) {
        return this.client.put(`/merchant-applications/${id}`, data)
    }
    
    // Changer statut
    async updateApplicationStatus(id, status) {
        return this.client.post(`/merchant-applications/${id}/status`, { status })
    }

    // Ajouter une note
    async addApplicationNote(id, note) {
        return this.client.put(`/merchant-applications/${id}`, { admin_notes: note })
    }

    // Mettre à jour une note
    async updateApplicationNote(id, note) {
        return this.client.put(`/merchant-applications/${id}`, { admin_notes: note })
    }
    
    // Supprimer
    async deleteApplication(id) {
        return this.client.delete(`/merchant-applications/${id}`)
    }
    
    // Restaurer
    async restoreApplication(id) {
        return this.client.post(`/merchant-applications/${id}/restore`)
    }
    
    // Statistiques
    async getApplicationStatistics(params = {}) {
        return this.client.get('/merchant-applications/statistics/all', { params })
    }

    // Approuver candidature (Admin seulement)
    async approveApplication(id) {
        return this.client.post(`/merchant-applications/${id}/approve`)
    }

    // Rejeter candidature (Admin seulement)
    async rejectApplication(id, reason = null) {
        return this.client.post(`/merchant-applications/${id}/reject`, { reason })
    }

    // Supprimer définitivement (Admin seulement)
    async forceDeleteApplication(id) {
        return this.client.delete(`/merchant-applications/${id}/force`)
    }
    
    // ============================================================
    // DOCUMENTS
    // ============================================================
    
    // Liste documents
    async getDocuments(params = {}) {
        return this.client.get('/documents', { params })
    }
    
    // Upload document
    async uploadDocument(file, type, description = null, applicationId = null) {
        const formData = new FormData()
        formData.append('file', file)
        formData.append('type', type)
        if (description) formData.append('description', description)
        if (applicationId) formData.append('merchant_application_id', applicationId)
        
        return this.client.post('/documents/upload', formData, {
            headers: { 'Content-Type': 'multipart/form-data' }
        })
    }
    
    // Voir document
    getDocumentUrl(id) {
        return `${API_URL}/documents/${id}`
    }
    
    // Télécharger document
    getDocumentDownloadUrl(id) {
        return `${API_URL}/documents/${id}/download`
    }
    
    // Supprimer document
    async deleteDocument(id) {
        return this.client.delete(`/documents/${id}`)
    }
    
    // Vérifier document
    async verifyDocument(id, notes = null) {
        return this.client.post(`/documents/${id}/verify`, { notes })
    }
    
    // Annuler vérification
    async unverifyDocument(id) {
        return this.client.post(`/documents/${id}/unverify`)
    }
    
    // Vérifier intégrité
    async checkDocumentIntegrity(id) {
        return this.client.get(`/documents/${id}/integrity`)
    }
    
    // Métadonnées
    async getDocumentMetadata(id) {
        return this.client.get(`/documents/${id}/metadata`)
    }
    
    // ============================================================
    // DASHBOARD
    // ============================================================
    
    // Statistiques
    async getDashboardStats(period = 'all', refresh = false) {
        return this.client.get('/dashboard/stats', { params: { period, refresh } })
    }
    
    // Récentes avec pagination et filtres
    async getDashboardRecent(params = {}) {
        // Support à la fois de l'ancienne signature et de la nouvelle
        if (typeof params === 'number') {
            // Ancienne signature: getDashboardRecent(limit, period, status)
            const limit = arguments[0] || 10
            const period = arguments[1] || 'all'
            const status = arguments[2] || null
            return this.client.get('/dashboard/recent', { params: { limit, period, status } })
        } else {
            // Nouvelle signature avec objet de paramètres
            return this.client.get('/dashboard/recent', { params })
        }
    }
    
    // Tendances
    async getDashboardTrends(period = 'month', groupBy = 'day') {
        return this.client.get('/dashboard/trends', { params: { period, group_by: groupBy } })
    }
    
    // KPIs
    async getDashboardKpis(period = 'all') {
        return this.client.get('/dashboard/kpis', { params: { period } })
    }
    
    // Alertes
    async getDashboardAlerts(period = 'all') {
        return this.client.get('/dashboard/alerts', { params: { period } })
    }
    
    // Comparaison
    async getDashboardCompare(period1 = 'this_month', period2 = 'last_month') {
        return this.client.get('/dashboard/compare', { params: { period1, period2 } })
    }
    
    // Export
    async getDashboardExport(format = 'json', period = 'all') {
        return this.client.get('/dashboard/export', { params: { format, period } })
    }
    
    // Résumé
    async getDashboardSummary() {
        return this.client.get('/dashboard/summary')
    }
    
    // Graphiques
    async getDashboardCharts(period = 'month') {
        return this.client.get('/dashboard/charts', { params: { period } })
    }
    
    // Statistiques utilisateur (Admin uniquement)
    async getDashboardUserStats(params = {}) {
        // Support pour l'ancien format (période simple)
        if (typeof params === 'string') {
            params = { period: params }
        }
        
        // Paramètres par défaut
        const defaultParams = {
            period: 'all',
            page: 1,
            per_page: 10,
            search: null
        }
        
        // Fusionner avec les paramètres fournis
        const finalParams = { ...defaultParams, ...params }
        
        // Supprimer les paramètres null ou undefined
        Object.keys(finalParams).forEach(key => {
            if (finalParams[key] === null || finalParams[key] === undefined) {
                delete finalParams[key]
            }
        })
        
        return this.client.get('/dashboard/user-stats', { params: finalParams })
    }
    
    // Candidatures récentes avec filtres (pour la section "Dernières candidatures")
    async getDashboardRecent(params = {}) {
        // Paramètres par défaut
        const defaultParams = {
            page: 1,
            per_page: 6,
            status: null,
            search: null,
            user_id: null
        }
        
        // Fusionner avec les paramètres fournis
        const finalParams = { ...defaultParams, ...params }
        
        // Supprimer les paramètres null ou undefined
        Object.keys(finalParams).forEach(key => {
            if (finalParams[key] === null || finalParams[key] === undefined) {
                delete finalParams[key]
            }
        })
        
        return this.client.get('/dashboard/recent', { params: finalParams })
    }
    
    // ============================================================
    // NOTIFICATIONS
    // ============================================================
    
    async getNotifications(params = {}) {
        return this.client.get('/notifications', { params })
    }
    
    async getUnreadNotificationsCount() {
        return this.client.get('/notifications/unread-count')
    }
    
    async markNotificationAsRead(notificationId) {
        return this.client.patch(`/notifications/${notificationId}/read`)
    }
    
    async markAllNotificationsAsRead() {
        return this.client.patch('/notifications/mark-all-read')
    }
    
    async deleteNotification(notificationId) {
        return this.client.delete(`/notifications/${notificationId}`)
    }

    // Temporary test method without authentication
    async getNotificationsTest() {
        return this.client.get('/test-notifications-api')
    }

    // ============================================================
    // HEALTH CHECK
    // ============================================================
    
    async healthCheck() {
        return this.client.get('/health')
    }
}

export default new ApiService()