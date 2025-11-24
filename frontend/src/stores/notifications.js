import { defineStore } from 'pinia'

export const useNotificationStore = defineStore('toastMessages', {
  state: () => ({
    notifications: []
  }),

  actions: {
    addNotification(notification) {
      const id = Date.now() + Math.random()
      const newNotification = {
        id,
        type: notification.type || 'success',
        title: notification.title,
        message: notification.message || '',
        duration: notification.duration || 5000,
        persistent: notification.persistent || false,
        show: true
      }
      
      this.notifications.push(newNotification)
      return id
    },

    removeNotification(id) {
      const index = this.notifications.findIndex(n => n.id === id)
      if (index > -1) {
        this.notifications.splice(index, 1)
      }
    },

    // Méthodes de convenance pour différents types de notifications
    success(title, message, options = {}) {
      return this.addNotification({
        type: 'success',
        title,
        message,
        ...options
      })
    },

    error(title, message, options = {}) {
      return this.addNotification({
        type: 'error',
        title,
        message,
        duration: 8000, // Plus long pour les erreurs
        ...options
      })
    },

    warning(title, message, options = {}) {
      return this.addNotification({
        type: 'warning',
        title,
        message,
        ...options
      })
    },

    info(title, message, options = {}) {
      return this.addNotification({
        type: 'info',
        title,
        message,
        ...options
      })
    },

    clear() {
      this.notifications = []
    }
  }
})