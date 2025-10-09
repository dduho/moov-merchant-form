import { defineStore } from 'pinia'
import { NotificationService } from '../services/NotificationService'

export const useNotificationStore = defineStore('notifications', {
  state: () => ({
    notifications: [],
    unreadCount: 0,
    isLoading: false,
    error: null,
    lastFetch: null,
    autoRefreshInterval: null
  }),

  getters: {
    unreadNotifications: (state) => state.notifications.filter(n => !n.read_at),
    readNotifications: (state) => state.notifications.filter(n => n.read_at),
    hasUnread: (state) => state.unreadCount > 0,
    
    sortedNotifications: (state) => {
      return [...state.notifications].sort((a, b) => {
        // Prioriser les non lues
        const aIsRead = !!a.read_at
        const bIsRead = !!b.read_at
        if (aIsRead !== bIsRead) {
          return aIsRead ? 1 : -1
        }
        // Puis par date de création (plus récent en premier)
        return new Date(b.created_at) - new Date(a.created_at)
      })
    }
  },

  actions: {
    /**
     * Charger toutes les notifications
     */
    async fetchNotifications(unreadOnly = false, limit = 50) {
      this.isLoading = true
      this.error = null

      try {
        const response = await NotificationService.getAll(unreadOnly, limit)
        this.notifications = response.data
        this.unreadCount = response.meta?.unread_count || this.notifications.filter(n => !n.read_at).length
        this.lastFetch = new Date()
      } catch (error) {
        this.error = error.message || 'Erreur lors du chargement des notifications'
        console.error('Erreur fetchNotifications:', error)
      } finally {
        this.isLoading = false
      }
    },

    /**
     * Charger seulement les notifications non lues
     */
    async fetchUnreadNotifications(limit = 20) {
      await this.fetchNotifications(true, limit)
    },

    /**
     * Mettre à jour le compteur de notifications non lues
     */
    async updateUnreadCount() {
      try {
        this.unreadCount = await NotificationService.getUnreadCount()
      } catch (error) {
        console.error('Erreur updateUnreadCount:', error)
      }
    },

    /**
     * Marquer une notification comme lue
     */
    async markAsRead(notificationId) {
      try {
        await NotificationService.markAsRead(notificationId)
        
        // Mettre à jour localement
        const notification = this.notifications.find(n => n.id === notificationId)
        if (notification && !notification.read_at) {
          notification.read_at = new Date().toISOString()
          this.unreadCount = Math.max(0, this.unreadCount - 1)
        }
      } catch (error) {
        this.error = error.message || 'Erreur lors du marquage comme lu'
        console.error('Erreur markAsRead:', error)
      }
    },

    /**
     * Marquer toutes les notifications comme lues
     */
    async markAllAsRead() {
      try {
        const response = await NotificationService.markAllAsRead()
        
        // Mettre à jour localement
        this.notifications.forEach(notification => {
          if (!notification.read_at) {
            notification.read_at = new Date().toISOString()
          }
        })
        this.unreadCount = 0
      } catch (error) {
        this.error = error.message || 'Erreur lors du marquage de toutes comme lues'
        console.error('Erreur markAllAsRead:', error)
      }
    },

    /**
     * Supprimer une notification
     */
    async deleteNotification(notificationId) {
      try {
        await NotificationService.delete(notificationId)
        
        // Retirer de la liste locale
        const index = this.notifications.findIndex(n => n.id === notificationId)
        if (index !== -1) {
          const notification = this.notifications[index]
          if (!notification.read_at) {
            this.unreadCount = Math.max(0, this.unreadCount - 1)
          }
          this.notifications.splice(index, 1)
        }
      } catch (error) {
        this.error = error.message || 'Erreur lors de la suppression'
        console.error('Erreur deleteNotification:', error)
      }
    },

    /**
     * Ajouter une nouvelle notification (pour les updates en temps réel)
     */
    addNotification(notification) {
      // Vérifier si elle n'existe pas déjà
      const exists = this.notifications.find(n => n.id === notification.id)
      if (!exists) {
        this.notifications.unshift(notification)
        if (!notification.read_at) {
          this.unreadCount++
        }
      }
    },

    /**
     * Démarrer le rafraîchissement automatique
     */
    startAutoRefresh(intervalMs = 30000) { // 30 secondes par défaut
      this.stopAutoRefresh()
      
      this.autoRefreshInterval = setInterval(async () => {
        // Ne rafraîchir que le compteur pour éviter de perturber l'UI
        await this.updateUnreadCount()
      }, intervalMs)
    },

    /**
     * Arrêter le rafraîchissement automatique
     */
    stopAutoRefresh() {
      if (this.autoRefreshInterval) {
        clearInterval(this.autoRefreshInterval)
        this.autoRefreshInterval = null
      }
    },

    /**
     * Réinitialiser les erreurs
     */
    clearError() {
      this.error = null
    },

    /**
     * Réinitialiser tout le store
     */
    reset() {
      this.notifications = []
      this.unreadCount = 0
      this.isLoading = false
      this.error = null
      this.lastFetch = null
      this.stopAutoRefresh()
    }
  }
})