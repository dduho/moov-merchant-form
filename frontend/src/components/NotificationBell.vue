<template>
  <div class="relative">
    <!-- Bouton de notification -->
    <button
      @click="toggleDropdown"
      class="relative p-2 text-gray-600 hover:text-gray-900 focus:outline-none focus:ring-2 focus:ring-blue-500 rounded-lg transition-colors"
      :class="{ 'text-blue-600': isOpen }"
    >
      <!-- Icône cloche -->
      <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path
          stroke-linecap="round"
          stroke-linejoin="round"
          stroke-width="2"
          d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"
        />
      </svg>
      
      <!-- Badge du nombre de notifications -->
      <span
        v-if="unreadCount > 0"
        class="absolute -top-1 -right-1 inline-flex items-center justify-center px-2 py-1 text-xs font-bold leading-none text-white transform translate-x-1/2 -translate-y-1/2 bg-red-500 rounded-full min-w-[1.25rem]"
      >
        {{ displayCount }}
      </span>
    </button>

    <!-- Dropdown des notifications -->
    <Transition
      enter-active-class="transition ease-out duration-200"
      enter-from-class="transform opacity-0 scale-95"
      enter-to-class="transform opacity-100 scale-100"
      leave-active-class="transition ease-in duration-75"
      leave-from-class="transform opacity-100 scale-100"
      leave-to-class="transform opacity-0 scale-95"
    >
      <div
        v-if="isOpen"
        class="absolute right-0 mt-2 w-[30rem] bg-white rounded-lg shadow-lg ring-1 ring-black ring-opacity-5 z-50"
      >
        <!-- En-tête -->
        <div class="px-4 py-3 border-b border-gray-200 flex items-center justify-between">
          <h3 class="text-lg font-semibold text-gray-900">Notifications</h3>
          <div class="flex items-center space-x-2">
            <span v-if="unreadCount > 0" class="text-sm text-gray-500">
              {{ unreadCount }} non lue{{ unreadCount > 1 ? 's' : '' }}
            </span>
            <button
              v-if="unreadCount > 0"
              @click="markAllAsRead"
              class="text-sm text-blue-600 hover:text-blue-800 font-medium"
              :disabled="isMarkingAllAsRead"
            >
              {{ isMarkingAllAsRead ? 'En cours...' : 'Tout marquer' }}
            </button>
          </div>
        </div>

        <!-- Liste des notifications -->
        <div class="max-h-96 overflow-y-auto">
          <!-- État de chargement -->
          <div v-if="isLoading" class="p-4 text-center">
            <div class="animate-spin rounded-full h-8 w-8 border-b-2 border-blue-500 mx-auto"></div>
            <p class="text-sm text-gray-500 mt-2">Chargement...</p>
          </div>

          <!-- Erreur -->
          <div v-else-if="error" class="p-4 text-center">
            <p class="text-sm text-red-600">{{ error }}</p>
            <button
              @click="refreshNotifications"
              class="mt-2 text-sm text-blue-600 hover:text-blue-800"
            >
              Réessayer
            </button>
          </div>

          <!-- Aucune notification -->
          <div v-else-if="notifications.length === 0" class="p-4 text-center">
            <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2M4 13h2m13-8V4a1 1 0 00-1-1H7a1 1 0 00-1 1v1m8 0V4a1 1 0 00-1-1H9a1 1 0 00-1 1v1" />
            </svg>
            <p class="text-sm text-gray-500 mt-2">Aucune notification</p>
          </div>

          <!-- Liste des notifications -->
          <div v-else class="divide-y divide-gray-200">
            <NotificationItem
              v-for="notification in sortedNotifications"
              :key="notification.id"
              :notification="notification"
              @mark-as-read="handleMarkAsRead"
              @delete="handleDelete"
            />
          </div>
        </div>

        <!-- Pied de page -->
        <div v-if="notifications.length > 0" class="px-4 py-3 bg-gray-50 border-t border-gray-200 rounded-b-lg">
          <router-link
            :to="{ name: 'notifications' }"
            class="block text-center text-sm text-blue-600 hover:text-blue-800 font-medium"
            @click="closeDropdown"
          >
            Voir toutes les notifications
          </router-link>
        </div>
      </div>
    </Transition>

    <!-- Overlay pour fermer le dropdown -->
    <div
      v-if="isOpen"
      @click="closeDropdown"
      class="fixed inset-0 z-40"
    ></div>
  </div>
</template>

<script setup>
import { ref, computed, onMounted, onUnmounted } from 'vue'
import { useSystemNotificationStore } from '../stores/notification'
import NotificationItem from './NotificationItem.vue'

const notificationStore = useSystemNotificationStore()
const isOpen = ref(false)
const isMarkingAllAsRead = ref(false)

// Computed properties
const notifications = computed(() => notificationStore.notifications)
const sortedNotifications = computed(() => notificationStore.sortedNotifications)
const unreadCount = computed(() => notificationStore.unreadCount)
const isLoading = computed(() => notificationStore.isLoading)
const error = computed(() => notificationStore.error)

const displayCount = computed(() => {
  return unreadCount.value > 99 ? '99+' : unreadCount.value.toString()
})

// Methods
const toggleDropdown = async () => {
  isOpen.value = !isOpen.value
  
  if (isOpen.value) {
    await refreshNotifications()
  }
}

const closeDropdown = () => {
  isOpen.value = false
}

const refreshNotifications = async () => {
  await notificationStore.fetchNotifications(false, 10)
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

const handleMarkAsRead = async (notificationId) => {
  await notificationStore.markAsRead(notificationId)
}

const handleDelete = async (notificationId) => {
  await notificationStore.deleteNotification(notificationId)
}

// Lifecycle
onMounted(async () => {
  // Charger le compteur initial
  await notificationStore.updateUnreadCount()
  
  // Démarrer le rafraîchissement automatique
  notificationStore.startAutoRefresh(30000) // 30 secondes
})

onUnmounted(() => {
  notificationStore.stopAutoRefresh()
})

// Fermer le dropdown quand on clique ailleurs
const handleClickOutside = (event) => {
  if (isOpen.value && !event.target.closest('.relative')) {
    closeDropdown()
  }
}

onMounted(() => {
  document.addEventListener('click', handleClickOutside)
})

onUnmounted(() => {
  document.removeEventListener('click', handleClickOutside)
})
</script>