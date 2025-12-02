<template>
  <div class="proximity-alert p-4 rounded-xl" :class="alertClasses">
    <div class="flex items-start space-x-3">
      <!-- Icon -->
      <div class="flex-shrink-0">
        <div class="w-10 h-10 rounded-full flex items-center justify-center" :class="iconClasses">
          <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
          </svg>
        </div>
      </div>

      <!-- Content -->
      <div class="flex-1 min-w-0">
        <h4 class="font-semibold" :class="titleClasses">
          {{ title }}
        </h4>
        <p class="text-sm mt-1" :class="descClasses">
          {{ description }}
        </p>

        <!-- Nearby PDVs List -->
        <div v-if="nearbyPdvs && nearbyPdvs.length > 0" class="mt-3 space-y-2">
          <div
            v-for="pdv in nearbyPdvs"
            :key="pdv.id"
            class="flex items-center justify-between p-2 rounded-lg"
            :class="itemClasses"
          >
            <div class="flex-1 min-w-0">
              <p class="font-medium text-sm truncate" :class="itemTitleClasses">
                {{ pdv.nom_point }}
              </p>
              <p class="text-xs" :class="itemDescClasses">
                {{ pdv.dealer_name }} • {{ pdv.reference_number }}
              </p>
            </div>
            <div class="flex-shrink-0 ml-3">
              <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium" :class="distanceClasses">
                {{ pdv.distance }}m
              </span>
            </div>
          </div>
        </div>

        <!-- Actions -->
        <div v-if="showActions" class="mt-4 flex space-x-3">
          <button
            v-if="canProceed"
            @click="$emit('proceed')"
            class="px-4 py-2 text-sm font-medium rounded-lg transition-colors"
            :class="proceedButtonClasses"
          >
            Continuer quand même
          </button>
          <button
            @click="$emit('cancel')"
            class="px-4 py-2 text-sm font-medium rounded-lg transition-colors"
            :class="cancelButtonClasses"
          >
            Annuler
          </button>
        </div>
      </div>

      <!-- Close button -->
      <button
        v-if="dismissible"
        @click="$emit('dismiss')"
        class="flex-shrink-0 p-1 rounded-lg transition-colors"
        :class="closeButtonClasses"
      >
        <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
        </svg>
      </button>
    </div>
  </div>
</template>

<script setup>
import { computed } from 'vue'

const props = defineProps({
  type: {
    type: String,
    default: 'warning',
    validator: (v) => ['warning', 'error', 'info'].includes(v)
  },
  title: {
    type: String,
    default: 'PDV à proximité détecté'
  },
  description: {
    type: String,
    default: 'Un ou plusieurs PDV existent déjà dans cette zone.'
  },
  nearbyPdvs: {
    type: Array,
    default: () => []
  },
  alertDistance: {
    type: Number,
    default: 300
  },
  showActions: {
    type: Boolean,
    default: true
  },
  canProceed: {
    type: Boolean,
    default: true
  },
  dismissible: {
    type: Boolean,
    default: false
  }
})

defineEmits(['proceed', 'cancel', 'dismiss'])

// Computed classes based on type
const alertClasses = computed(() => {
  switch (props.type) {
    case 'error':
      return 'bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-800'
    case 'info':
      return 'bg-blue-50 dark:bg-blue-900/20 border border-blue-200 dark:border-blue-800'
    default:
      return 'bg-orange-50 dark:bg-orange-900/20 border border-orange-200 dark:border-orange-800'
  }
})

const iconClasses = computed(() => {
  switch (props.type) {
    case 'error':
      return 'bg-red-100 dark:bg-red-900 text-red-600'
    case 'info':
      return 'bg-blue-100 dark:bg-blue-900 text-blue-600'
    default:
      return 'bg-orange-100 dark:bg-orange-900 text-orange-600'
  }
})

const titleClasses = computed(() => {
  switch (props.type) {
    case 'error':
      return 'text-red-800 dark:text-red-200'
    case 'info':
      return 'text-blue-800 dark:text-blue-200'
    default:
      return 'text-orange-800 dark:text-orange-200'
  }
})

const descClasses = computed(() => {
  switch (props.type) {
    case 'error':
      return 'text-red-700 dark:text-red-300'
    case 'info':
      return 'text-blue-700 dark:text-blue-300'
    default:
      return 'text-orange-700 dark:text-orange-300'
  }
})

const itemClasses = computed(() => {
  switch (props.type) {
    case 'error':
      return 'bg-red-100 dark:bg-red-900/50'
    case 'info':
      return 'bg-blue-100 dark:bg-blue-900/50'
    default:
      return 'bg-orange-100 dark:bg-orange-900/50'
  }
})

const itemTitleClasses = computed(() => {
  switch (props.type) {
    case 'error':
      return 'text-red-900 dark:text-red-100'
    case 'info':
      return 'text-blue-900 dark:text-blue-100'
    default:
      return 'text-orange-900 dark:text-orange-100'
  }
})

const itemDescClasses = computed(() => {
  switch (props.type) {
    case 'error':
      return 'text-red-700 dark:text-red-300'
    case 'info':
      return 'text-blue-700 dark:text-blue-300'
    default:
      return 'text-orange-700 dark:text-orange-300'
  }
})

const distanceClasses = computed(() => {
  switch (props.type) {
    case 'error':
      return 'bg-red-200 dark:bg-red-800 text-red-800 dark:text-red-200'
    case 'info':
      return 'bg-blue-200 dark:bg-blue-800 text-blue-800 dark:text-blue-200'
    default:
      return 'bg-orange-200 dark:bg-orange-800 text-orange-800 dark:text-orange-200'
  }
})

const proceedButtonClasses = computed(() => {
  switch (props.type) {
    case 'error':
      return 'bg-red-600 hover:bg-red-700 text-white'
    case 'info':
      return 'bg-blue-600 hover:bg-blue-700 text-white'
    default:
      return 'bg-orange-600 hover:bg-orange-700 text-white'
  }
})

const cancelButtonClasses = computed(() => {
  return 'bg-white dark:bg-gray-800 border border-gray-300 dark:border-gray-600 text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-700'
})

const closeButtonClasses = computed(() => {
  switch (props.type) {
    case 'error':
      return 'text-red-600 hover:bg-red-100 dark:hover:bg-red-900'
    case 'info':
      return 'text-blue-600 hover:bg-blue-100 dark:hover:bg-blue-900'
    default:
      return 'text-orange-600 hover:bg-orange-100 dark:hover:bg-orange-900'
  }
})
</script>

<style scoped>
.proximity-alert {
  width: 100%;
}
</style>
