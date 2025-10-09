import ApiService from './ApiService'

export class NotificationService {
  /**
   * Obtenir toutes les notifications de l'utilisateur
   */
  static async getAll(unreadOnly = false, limit = 20) {
    try {
      // Temporarily use test endpoint for development
      const response = await ApiService.getNotificationsTest()
      return response.data
    } catch (error) {
      console.error('Erreur lors de la récupération des notifications:', error)
      throw error
    }
  }

  /**
   * Obtenir le nombre de notifications non lues
   */
  static async getUnreadCount() {
    try {
      // Temporarily use test endpoint for development
      const response = await ApiService.getNotificationsTest()
      return response.data.meta.unread_count
    } catch (error) {
      console.error('Erreur lors de la récupération du nombre de notifications non lues:', error)
      throw error
    }
  }

  /**
   * Marquer une notification comme lue
   */
  static async markAsRead(notificationId) {
    try {
      const response = await ApiService.markNotificationAsRead(notificationId)
      return response.data
    } catch (error) {
      console.error('Erreur lors du marquage de la notification comme lue:', error)
      throw error
    }
  }

  /**
   * Marquer toutes les notifications comme lues
   */
  static async markAllAsRead() {
    try {
      const response = await ApiService.markAllNotificationsAsRead()
      return response.data
    } catch (error) {
      console.error('Erreur lors du marquage de toutes les notifications comme lues:', error)
      throw error
    }
  }

  /**
   * Supprimer une notification
   */
  static async delete(notificationId) {
    try {
      const response = await ApiService.deleteNotification(notificationId)
      return response.data
    } catch (error) {
      console.error('Erreur lors de la suppression de la notification:', error)
      throw error
    }
  }

  /**
   * Obtenir les notifications non lues seulement
   */
  static async getUnread(limit = 20) {
    return this.getAll(true, limit)
  }

  /**
   * Formatage du temps relatif pour l'affichage
   */
  static formatTimeAgo(dateString) {
    const date = new Date(dateString)
    const now = new Date()
    const diffInMs = now - date
    const diffInMinutes = Math.floor(diffInMs / (1000 * 60))
    const diffInHours = Math.floor(diffInMs / (1000 * 60 * 60))
    const diffInDays = Math.floor(diffInMs / (1000 * 60 * 60 * 24))

    if (diffInMinutes < 1) {
      return 'À l\'instant'
    } else if (diffInMinutes < 60) {
      return `Il y a ${diffInMinutes} min`
    } else if (diffInHours < 24) {
      return `Il y a ${diffInHours}h`
    } else if (diffInDays < 7) {
      return `Il y a ${diffInDays} jour${diffInDays > 1 ? 's' : ''}`
    } else {
      return date.toLocaleDateString('fr-FR')
    }
  }

  /**
   * Obtenir l'icône pour un type de notification
   */
  static getIcon(type) {
    const icons = {
      application_approved: '✅',
      application_rejected: '❌',
      document_verified: '🔍',
      system: 'ℹ️',
      reminder: '🔔'
    }
    return icons[type] || '🔔'
  }

  /**
   * Obtenir la couleur pour un type de notification
   */
  static getColor(type, priority) {
    if (priority === 'high') return '#EF4444' // red-500
    
    const colors = {
      application_approved: '#10B981', // green-500
      application_rejected: '#EF4444', // red-500
      document_verified: '#3B82F6',   // blue-500
      system: '#6B7280',              // gray-500
      reminder: '#F59E0B'             // amber-500
    }
    return colors[type] || '#3B82F6'
  }
}