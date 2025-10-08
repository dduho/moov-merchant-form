<script setup>
import { computed } from 'vue'
import { useNotificationStore } from '../stores/notifications'
import NotificationToast from './NotificationToast.vue'

const notificationStore = useNotificationStore()

const notifications = computed(() => notificationStore.notifications)

const handleClose = (notificationId) => {
  notificationStore.removeNotification(notificationId)
}
</script>

<template>
  <div class="fixed bottom-6 right-6 z-50 w-80 max-w-[calc(100vw-3rem)] space-y-3 pointer-events-none">
    <div
      v-for="(notification, index) in notifications"
      :key="notification.id"
      class="pointer-events-auto"
      :style="{ transform: `translateY(-${index * 8}px)` }"
    >
      <NotificationToast
        :show="notification.show"
        :type="notification.type"
        :title="notification.title"
        :message="notification.message"
        :duration="notification.duration"
        :persistent="notification.persistent"
        @close="handleClose(notification.id)"
      />
    </div>
  </div>
</template>