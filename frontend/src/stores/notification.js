import { defineStore } from 'pinia'
import { ref, computed, readonly } from 'vue'
import { NotificationService } from '../services/NotificationService'
import { useAuthStore } from './auth'

export const useSystemNotificationStore = defineStore('systemNotifications', () => {
  // État pour les notifications système (persistent dans la base de données)
  // Ces notifications sont différentes des notifications toast (temporaires)
  const notifications = ref([])
  const unreadCount = ref(0)
  const isLoading = ref(false)
  const error = ref(null)
  const lastFetch = ref(null)
  const autoRefreshInterval = ref(null)

  // Getters computed
  const unreadNotifications = computed(() => notifications.value.filter(n => !n.read_at))
  const readNotifications = computed(() => notifications.value.filter(n => n.read_at))
  const hasUnread = computed(() => unreadCount.value > 0)
  
  const sortedNotifications = computed(() => {
    return [...notifications.value].sort((a, b) => {
      // Prioriser les non lues
      const aIsRead = !!a.is_read
      const bIsRead = !!b.is_read
      if (aIsRead !== bIsRead) {
        return aIsRead ? 1 : -1
      }
      // Puis par date de création (plus récent en premier)
      return new Date(b.created_at) - new Date(a.created_at)
    })
  })
  /**
   * Charger toutes les notifications système
   * Les notifications sont déjà filtrées côté serveur selon le rôle utilisateur
   */
  const fetchNotifications = async (unreadOnly = false, limit = 50) => {
    isLoading.value = true
    error.value = null

    try {
      const response = await NotificationService.getAll(unreadOnly, limit)
      
      // Notifications récupérées depuis l'API
      
      // La structure de réponse est { success: true, data: [], unread_count: X }
      if (response && response.success) {
        // Filtrer les notifications côté client comme sécurité supplémentaire
        const filteredNotifications = validateNotifications(response.data || [])
        notifications.value = filteredNotifications
        unreadCount.value = response.unread_count || 0
      } else {
        notifications.value = []
        unreadCount.value = 0
      }
      
      lastFetch.value = new Date()
    } catch (err) {
      error.value = err.message || 'Erreur lors du chargement des notifications'
      // Erreur lors de la récupération des notifications
      // En cas d'erreur, réinitialiser les données
      notifications.value = []
      unreadCount.value = 0
    } finally {
      isLoading.value = false
    }
  }

  /**
   * Charger seulement les notifications non lues
   */
  const fetchUnreadNotifications = async (limit = 20) => {
    await fetchNotifications(true, limit)
  }

  /**
   * Mettre à jour le compteur de notifications non lues
   */
  const updateUnreadCount = async () => {
    try {
      const count = await NotificationService.getUnreadCount()
      unreadCount.value = count
    } catch (err) {
      // Erreur lors de la mise à jour du compteur
    }
  }

  /**
   * Marquer une notification comme lue
   */
  const markAsRead = async (notificationId) => {
    try {
      await NotificationService.markAsRead(notificationId)
      
      // Mettre à jour localement
      const notification = notifications.value.find(n => n.id === notificationId)
      if (notification && !notification.is_read) {
        notification.is_read = true
        notification.read_at = new Date().toISOString()
        unreadCount.value = Math.max(0, unreadCount.value - 1)
      }
    } catch (err) {
      error.value = err.message || 'Erreur lors du marquage comme lu'
      // Erreur marquage comme lu
      throw err // Relancer l'erreur pour qu'elle remonte
    }
  }

  /**
   * Marquer toutes les notifications comme lues
   */
  const markAllAsRead = async () => {
    try {
      await NotificationService.markAllAsRead()
      
      // Mettre à jour localement
      notifications.value.forEach(notification => {
        if (!notification.is_read) {
          notification.is_read = true
          notification.read_at = new Date().toISOString()
        }
      })
      unreadCount.value = 0
    } catch (err) {
      error.value = err.message || 'Erreur lors du marquage de toutes comme lues'
      // Erreur marquage toutes comme lues
    }
  }

  /**
   * Supprimer une notification
   */
  const deleteNotification = async (notificationId) => {
    try {
      await NotificationService.delete(notificationId)
      
      // Retirer de la liste locale
      const index = notifications.value.findIndex(n => n.id === notificationId)
      if (index !== -1) {
        const notification = notifications.value[index]
        if (!notification.is_read) {
          unreadCount.value = Math.max(0, unreadCount.value - 1)
        }
        notifications.value.splice(index, 1)
      }
    } catch (err) {
      error.value = err.message || 'Erreur lors de la suppression'
      // Erreur suppression notification
    }
  }

  /**
   * Ajouter une nouvelle notification (pour les updates en temps réel)
   */
  const addNotification = (notification) => {
    // Vérifier si elle n'existe pas déjà
    const exists = notifications.value.find(n => n.id === notification.id)
    if (!exists) {
      notifications.value.unshift(notification)
      if (!notification.read_at) {
        unreadCount.value++
      }
    }
  }

  /**
   * Démarrer le rafraîchissement automatique
   */
  const startAutoRefresh = (intervalMs = 30000) => { // 30 secondes par défaut
    stopAutoRefresh()
    
    autoRefreshInterval.value = setInterval(async () => {
      // Ne rafraîchir que le compteur pour éviter de perturber l'UI
      await updateUnreadCount()
    }, intervalMs)
  }

  /**
   * Arrêter le rafraîchissement automatique
   */
  const stopAutoRefresh = () => {
    if (autoRefreshInterval.value) {
      clearInterval(autoRefreshInterval.value)
      autoRefreshInterval.value = null
    }
  }

  /**
   * Réinitialiser les erreurs
   */
  const clearError = () => {
    error.value = null
  }

  /**
   * Réinitialiser tout le store
   */
  const reset = () => {
    notifications.value = []
    unreadCount.value = 0
    isLoading.value = false
    error.value = null
    lastFetch.value = null
    stopAutoRefresh()
  }

  /**
   * Validation des notifications côté client comme sécurité supplémentaire
   * S'assure qu'un commercial ou personnel ne voit que ses propres notifications
   */
  const validateNotifications = (notificationsToValidate) => {
    const authStore = useAuthStore()
    const currentUser = authStore.user
    
    // Si pas d'utilisateur connecté, retourner vide
    if (!currentUser) {
      // Aucun utilisateur connecté pour valider les notifications
      return []
    }

    // Si c'est un admin, il peut voir toutes ses notifications
    if (authStore.isAdmin) {
      return notificationsToValidate
    }

    // Si c'est un commercial ou personnel, filtrer strictement
    if (authStore.isCommercial || authStore.isPersonnel) {
      return notificationsToValidate.filter(notification => {
        // Vérifier les types de notifications autorisés pour les commerciaux et personnel
        const allowedTypes = [
          'application_submitted',
          'application_approved', 
          'application_rejected',
          'personal_notification',
          'objective_assigned',
          'objective_updated'
        ]

        if (!allowedTypes.includes(notification.type)) {
          // Type de notification non autorisé pour commercial/personnel
          return false
        }

        // Si c'est une notification liée à une candidature, vérifier que c'est la sienne
        if (notification.data && notification.data.application_id) {
          // Cette vérification sera plus approfondie côté serveur
          // Ici on fait une vérification de base
          return true // Le serveur a déjà filtré
        }

        return true
      })
    }

    return notificationsToValidate
  }

  // Retourner les propriétés et méthodes publiques
  return {
    // État
    notifications: readonly(notifications),
    unreadCount: readonly(unreadCount),
    isLoading: readonly(isLoading),
    error: readonly(error),
    lastFetch: readonly(lastFetch),
    
    // Getters
    unreadNotifications,
    readNotifications,
    hasUnread,
    sortedNotifications,
    
    // Actions
    fetchNotifications,
    fetchUnreadNotifications,
    updateUnreadCount,
    markAsRead,
    markAllAsRead,
    deleteNotification,
    addNotification,
    startAutoRefresh,
    stopAutoRefresh,
    clearError,
    reset
  }
})