<template>
  <div class="fixed top-4 right-4 z-[9999] space-y-2 pointer-events-none">
    <TransitionGroup name="notification">
      <div
        v-for="notification in notifications"
        :key="notification.id"
        :class="[
          'pointer-events-auto max-w-sm w-full rounded-xl shadow-2xl overflow-hidden transform transition-all duration-300',
          notification.visible ? 'translate-x-0 opacity-100' : 'translate-x-full opacity-0',
          getNotificationClasses(notification.type)
        ]"
      >
        <div class="p-4 flex items-start gap-3">
          <!-- Icon -->
          <div class="flex-shrink-0">
            <!-- Success Icon -->
            <svg v-if="notification.type === 'success'" class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
            </svg>
            <!-- Error Icon -->
            <svg v-else-if="notification.type === 'error'" class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z" />
            </svg>
            <!-- Warning Icon -->
            <svg v-else-if="notification.type === 'warning'" class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
            </svg>
            <!-- Info Icon -->
            <svg v-else class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
            </svg>
          </div>
          
          <!-- Message -->
          <div class="flex-1 pt-0.5">
            <p class="text-sm font-medium leading-relaxed">
              {{ notification.message }}
            </p>
          </div>
          
          <!-- Close button -->
          <button
            @click="removeNotification(notification.id)"
            class="flex-shrink-0 inline-flex rounded-lg p-1.5 hover:bg-black/10 dark:hover:bg-white/10 transition-colors"
            aria-label="Fermer"
          >
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
            </svg>
          </button>
        </div>
        
        <!-- Progress bar (auto-dismiss indicator) -->
        <div class="h-1 bg-black/10 dark:bg-white/10">
          <div 
            class="h-full bg-current transition-all duration-[4000ms] ease-linear"
            :style="{ width: notification.visible ? '0%' : '100%' }"
          ></div>
        </div>
      </div>
    </TransitionGroup>
  </div>
</template>

<script setup>
import { useNotification } from '../composables/useNotification'

const { notifications, removeNotification } = useNotification()

const getNotificationClasses = (type) => {
  const classes = {
    success: 'bg-gradient-to-br from-green-500 to-green-600 text-white',
    error: 'bg-gradient-to-br from-red-500 to-red-600 text-white',
    warning: 'bg-gradient-to-br from-yellow-400 to-yellow-500 text-gray-900',
    info: 'bg-gradient-to-br from-blue-500 to-blue-600 text-white'
  }
  return classes[type] || classes.info
}
</script>

<style scoped>
.notification-enter-active,
.notification-leave-active {
  transition: all 0.3s ease;
}

.notification-enter-from {
  opacity: 0;
  transform: translateX(100%);
}

.notification-leave-to {
  opacity: 0;
  transform: translateX(100%);
}

.notification-move {
  transition: transform 0.3s ease;
}
</style>