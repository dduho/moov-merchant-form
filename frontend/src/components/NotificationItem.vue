<template>
  <div
    class="px-4 py-3 hover:bg-gray-50 cursor-pointer transition-colors"
    :class="{
      'bg-blue-50 border-l-4 border-l-blue-500': !notification.is_read,
      'opacity-75': notification.is_read
    }"
    @click="handleClick"
  >
    <div class="flex items-start space-x-3">
      <!-- Icône -->
      <div
        class="flex-shrink-0 w-8 h-8 rounded-full flex items-center justify-center text-white text-sm font-semibold"
        :style="{ backgroundColor: notification.color }"
      >
        <span v-if="notification.icon">{{ getIconEmoji(notification.type) }}</span>
      </div>

      <!-- Contenu -->
      <div class="flex-1 min-w-0">
        <div class="flex items-start justify-between">
          <div class="flex-1">
            <p class="text-sm font-medium text-gray-900 mb-1">
              {{ notification.title }}
            </p>
            <p class="text-sm text-gray-600 leading-relaxed">
              {{ notification.message }}
            </p>
            
            <!-- Données additionnelles si disponibles -->
            <div v-if="notification.data && hasDisplayableData" class="mt-2 text-xs text-gray-500">
              <span v-if="notification.data.reference_number" class="inline-block bg-gray-100 px-2 py-1 rounded">
                Ref: {{ notification.data.reference_number }}
              </span>
            </div>
          </div>

          <!-- Actions -->
          <div class="flex items-center space-x-1 ml-2">
            <!-- Marquer comme lu -->
            <button
              v-if="!notification.is_read"
              @click.stop="markAsRead"
              class="p-1 text-gray-400 hover:text-blue-600 rounded"
              title="Marquer comme lu"
            >
              <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
              </svg>
            </button>

            <!-- Supprimer -->
            <button
              @click.stop="deleteNotification"
              class="p-1 text-gray-400 hover:text-red-600 rounded"
              title="Supprimer"
            >
              <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
              </svg>
            </button>
          </div>
        </div>

        <!-- Horodatage -->
        <div class="flex items-center justify-between mt-2">
          <span class="text-xs text-gray-500">
            {{ notification.formatted_time }}
          </span>
          
          <!-- Indicateur de priorité -->
          <span
            v-if="notification.priority === 'high'"
            class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-800"
          >
            Urgent
          </span>
          <span
            v-else-if="notification.priority === 'medium'"
            class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-yellow-100 text-yellow-800"
          >
            Important
          </span>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { computed } from 'vue'
import { useRouter } from 'vue-router'

const props = defineProps({
  notification: {
    type: Object,
    required: true
  }
})

const emit = defineEmits(['mark-as-read', 'delete'])

const router = useRouter()

// Computed properties
const hasDisplayableData = computed(() => {
  return props.notification.data && (
    props.notification.data.reference_number || 
    props.notification.data.application_id
  )
})

// Methods
const getIconEmoji = (type) => {
  const icons = {
    application_approved: '✅',
    application_rejected: '❌',
    document_verified: '🔍',
    system: 'ℹ️',
    reminder: '🔔'
  }
  return icons[type] || '🔔'
}

const handleClick = () => {
  // Marquer comme lu si pas encore lu
  if (!props.notification.is_read) {
    markAsRead()
  }
  
  // Navigation basée sur le type de notification
  if (props.notification.data) {
    const data = props.notification.data
    
    if (data.application_id) {
      router.push(`/applications/${data.application_id}`)
    } else if (data.reference_number) {
      router.push(`/applications/reference/${data.reference_number}`)
    }
  }
}

const markAsRead = () => {
  emit('mark-as-read', props.notification.id)
}

const deleteNotification = () => {
  if (confirm('Êtes-vous sûr de vouloir supprimer cette notification ?')) {
    emit('delete', props.notification.id)
  }
}
</script>