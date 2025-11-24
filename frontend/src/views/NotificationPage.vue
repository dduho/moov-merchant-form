<template>
  <div class="max-w-4xl mx-auto px-4 py-8">
    <!-- En-tête -->
    <div class="mb-8">
      <h1 class="text-3xl font-bold text-gray-900">Notifications</h1>
      <p class="text-gray-600 mt-2">Gérez vos notifications et restez informé des dernières mises à jour.</p>
    </div>

    <!-- Statistiques -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
      <div class="bg-white p-6 rounded-lg shadow-sm border">
        <div class="flex items-center">
          <div class="flex-shrink-0">
            <div class="w-8 h-8 bg-blue-100 rounded-lg flex items-center justify-center">
              <svg class="w-5 h-5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9" />
              </svg>
            </div>
          </div>
          <div class="ml-4">
            <p class="text-sm font-medium text-gray-600">Total</p>
            <p class="text-2xl font-semibold text-gray-900">{{ notifications.length }}</p>
          </div>
        </div>
      </div>

      <div class="bg-white p-6 rounded-lg shadow-sm border">
        <div class="flex items-center">
          <div class="flex-shrink-0">
            <div class="w-8 h-8 bg-red-100 rounded-lg flex items-center justify-center">
              <svg class="w-5 h-5 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L4.082 16.5c-.77.833.192 2.5 1.732 2.5z" />
              </svg>
            </div>
          </div>
          <div class="ml-4">
            <p class="text-sm font-medium text-gray-600">Non lues</p>
            <p class="text-2xl font-semibold text-gray-900">{{ unreadCount }}</p>
          </div>
        </div>
      </div>

      <div class="bg-white p-6 rounded-lg shadow-sm border">
        <div class="flex items-center">
          <div class="flex-shrink-0">
            <div class="w-8 h-8 bg-green-100 rounded-lg flex items-center justify-center">
              <svg class="w-5 h-5 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
              </svg>
            </div>
          </div>
          <div class="ml-4">
            <p class="text-sm font-medium text-gray-600">Lues</p>
            <p class="text-2xl font-semibold text-gray-900">{{ readCount }}</p>
          </div>
        </div>
      </div>
    </div>

    <!-- Actions et filtres -->
    <div class="bg-white p-6 rounded-lg shadow-sm border mb-8">
      <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div class="flex flex-wrap items-center gap-3">
          <!-- Filtre par statut -->
          <select
            v-model="statusFilter"
            class="rounded-lg border-gray-300 text-sm"
          >
            <option value="all">Toutes les notifications</option>
            <option value="unread">Non lues seulement</option>
            <option value="read">Lues seulement</option>
          </select>

          <!-- Filtre par type -->
          <select
            v-model="typeFilter"
            class="rounded-lg border-gray-300 text-sm"
          >
            <option value="all">Tous les types</option>
            <option value="application_approved">Candidatures approuvées</option>
            <option value="application_rejected">Candidatures rejetées</option>
            <option value="document_verified">Documents vérifiés</option>
            <option value="system">Système</option>
            <option value="reminder">Rappels</option>
          </select>

          <!-- Rafraîchir -->
          <button
            @click="refreshNotifications"
            :disabled="isLoading"
            class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 disabled:opacity-50 disabled:cursor-not-allowed flex items-center space-x-2"
          >
            <svg
              class="w-4 h-4"
              :class="{ 'animate-spin': isLoading }"
              fill="none"
              stroke="currentColor"
              viewBox="0 0 24 24"
            >
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
            </svg>
            <span>Actualiser</span>
          </button>
        </div>

        <!-- Actions groupées -->
        <div class="flex items-center space-x-3">
          <button
            v-if="unreadCount > 0"
            @click="markAllAsRead"
            :disabled="isMarkingAllAsRead"
            class="px-4 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700 disabled:opacity-50 disabled:cursor-not-allowed"
          >
            {{ isMarkingAllAsRead ? 'En cours...' : 'Tout marquer comme lu' }}
          </button>

          <button
            @click="deleteAllRead"
            :disabled="readCount === 0 || isDeletingAllRead"
            class="px-4 py-2 bg-red-600 text-white rounded-lg hover:bg-red-700 disabled:opacity-50 disabled:cursor-not-allowed"
          >
            {{ isDeletingAllRead ? 'En cours...' : 'Supprimer les lues' }}
          </button>
        </div>
      </div>
    </div>

    <!-- Liste des notifications -->
    <div class="bg-white rounded-lg shadow-sm border">
      <!-- Chargement -->
      <div v-if="isLoading && notifications.length === 0" class="p-8 text-center">
        <div class="animate-spin rounded-full h-12 w-12 border-b-2 border-blue-500 mx-auto"></div>
        <p class="text-gray-500 mt-4">Chargement des notifications...</p>
      </div>

      <!-- Erreur -->
      <div v-else-if="error" class="p-8 text-center">
        <div class="text-red-500 mb-4">
          <svg class="w-12 h-12 mx-auto" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L4.082 16.5c-.77.833.192 2.5 1.732 2.5z" />
          </svg>
        </div>
        <p class="text-gray-600 mb-4">{{ error }}</p>
        <button
          @click="refreshNotifications"
          class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700"
        >
          Réessayer
        </button>
      </div>

      <!-- Pas de notifications -->
      <div v-else-if="filteredNotifications.length === 0" class="p-8 text-center">
        <div class="text-gray-400 mb-4">
          <svg class="w-12 h-12 mx-auto" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2M4 13h2m13-8V4a1 1 0 00-1-1H7a1 1 0 00-1 1v1m8 0V4a1 1 0 00-1-1H9a1 1 0 00-1 1v1" />
          </svg>
        </div>
        <p class="text-gray-500">
          {{ notifications.length === 0 ? 'Aucune notification pour le moment' : 'Aucune notification ne correspond à vos filtres' }}
        </p>
      </div>

      <!-- Liste des notifications -->
      <div v-else class="divide-y divide-gray-200">
        <NotificationItem
          v-for="notification in filteredNotifications"
          :key="notification.id"
          :notification="notification"
          @mark-as-read="handleMarkAsRead"
          @delete="handleDelete"
          class="hover:bg-gray-50"
        />
      </div>

      <!-- Pagination ou chargement plus -->
      <div v-if="filteredNotifications.length > 0" class="p-4 border-t text-center">
        <button
          @click="loadMore"
          :disabled="isLoadingMore"
          class="px-6 py-2 bg-gray-100 text-gray-700 rounded-lg hover:bg-gray-200 disabled:opacity-50"
        >
          {{ isLoadingMore ? 'Chargement...' : 'Charger plus' }}
        </button>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue'
import { useSystemNotificationStore } from '../stores/notification'
import NotificationItem from '../components/NotificationItem.vue'

const notificationStore = useSystemNotificationStore()

// État local
const statusFilter = ref('all')
const typeFilter = ref('all')
const isMarkingAllAsRead = ref(false)
const isDeletingAllRead = ref(false)
const isLoadingMore = ref(false)

// Computed properties
const notifications = computed(() => notificationStore.notifications)
const unreadCount = computed(() => {
  // S'assurer qu'on retourne toujours un nombre
  const storeCount = notificationStore.unreadCount
  const localCount = notifications.value.filter(n => !n.is_read).length
  
  // Debug temporaire
  // debug retiré
  
  if (typeof storeCount === 'number' && !isNaN(storeCount)) {
    return storeCount
  }
  // Fallback: calculer localement
  return localCount
})
const isLoading = computed(() => notificationStore.isLoading)
const error = computed(() => notificationStore.error)

const readCount = computed(() => {
  return notifications.value.filter(n => n.is_read).length
})

const filteredNotifications = computed(() => {
  let filtered = [...notifications.value]

  // Filtre par statut
  if (statusFilter.value === 'unread') {
    filtered = filtered.filter(n => !n.is_read)
  } else if (statusFilter.value === 'read') {
    filtered = filtered.filter(n => n.is_read)
  }

  // Filtre par type
  if (typeFilter.value !== 'all') {
    filtered = filtered.filter(n => n.type === typeFilter.value)
  }

  // Trier par date (plus récent en premier) et statut (non lues en premier)
  return filtered.sort((a, b) => {
    const aIsRead = !!a.is_read
    const bIsRead = !!b.is_read
    if (aIsRead !== bIsRead) {
      return aIsRead ? 1 : -1
    }
    return new Date(b.created_at) - new Date(a.created_at)
  })
})

// Methods
const refreshNotifications = async () => {
  await notificationStore.fetchNotifications(false, 100)
}

const markAllAsRead = async () => {
  if (isMarkingAllAsRead.value) return
  
  isMarkingAllAsRead.value = true
  try {
    await notificationStore.markAllAsRead()
  } finally {
    isMarkingAllAsRead.value = false
  }
}

const deleteAllRead = async () => {
  if (isDeletingAllRead.value) return
  
  const readNotifications = notifications.value.filter(n => n.is_read)
  if (readNotifications.length === 0) return
  
  if (!confirm(`Êtes-vous sûr de vouloir supprimer ${readNotifications.length} notification${readNotifications.length > 1 ? 's' : ''} lue${readNotifications.length > 1 ? 's' : ''} ?`)) {
    return
  }
  
  isDeletingAllRead.value = true
  try {
    for (const notification of readNotifications) {
      await notificationStore.deleteNotification(notification.id)
    }
  } finally {
    isDeletingAllRead.value = false
  }
}

const loadMore = async () => {
  if (isLoadingMore.value) return
  
  isLoadingMore.value = true
  try {
    const currentLength = notifications.value.length
    await notificationStore.fetchNotifications(false, currentLength + 20)
  } finally {
    isLoadingMore.value = false
  }
}

const handleMarkAsRead = async (notificationId) => {
  await notificationStore.markAsRead(notificationId)
}

const handleDelete = async (notificationId) => {
  await notificationStore.deleteNotification(notificationId)
}

// Lifecycle
onMounted(async () => {
  await refreshNotifications()
})
</script>