import { ref } from 'vue'

const notifications = ref([])
let notificationId = 0

export const useNotification = () => {
  const addNotification = (message, type = 'info', duration = 4000) => {
    const id = ++notificationId
    
    notifications.value.push({
      id,
      message,
      type, // 'success', 'error', 'warning', 'info'
      visible: false
    })

    // Animation d'entrée
    setTimeout(() => {
      const notification = notifications.value.find(n => n.id === id)
      if (notification) {
        notification.visible = true
      }
    }, 10)

    // Auto-remove après duration
    if (duration > 0) {
      setTimeout(() => {
        removeNotification(id)
      }, duration)
    }

    return id
  }

  const removeNotification = (id) => {
    const notification = notifications.value.find(n => n.id === id)
    if (notification) {
      notification.visible = false
      // Attendre la fin de l'animation avant de retirer du DOM
      setTimeout(() => {
        const index = notifications.value.findIndex(n => n.id === id)
        if (index > -1) {
          notifications.value.splice(index, 1)
        }
      }, 300)
    }
  }

  const success = (message, duration) => {
    return addNotification(message, 'success', duration)
  }

  const error = (message, duration) => {
    return addNotification(message, 'error', duration)
  }

  const warning = (message, duration) => {
    return addNotification(message, 'warning', duration)
  }

  const info = (message, duration) => {
    return addNotification(message, 'info', duration)
  }

  const clear = () => {
    notifications.value = []
  }

  return {
    notifications,
    addNotification,
    removeNotification,
    success,
    error,
    warning,
    info,
    clear
  }
}
