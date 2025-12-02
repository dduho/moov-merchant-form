<template>
  <div class="gps-capture">
    <!-- GPS Status -->
    <div class="mb-4 p-4 rounded-xl" :class="statusClasses">
      <div class="flex items-center justify-between">
        <div class="flex items-center space-x-3">
          <div class="w-10 h-10 rounded-full flex items-center justify-center" :class="iconClasses">
            <svg v-if="capturing" class="w-5 h-5 animate-spin" fill="none" viewBox="0 0 24 24" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
            </svg>
            <svg v-else-if="hasValidCoordinates" class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
            </svg>
            <svg v-else class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" />
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z" />
            </svg>
          </div>
          <div>
            <p class="font-medium" :class="textClasses">
              {{ statusText }}
            </p>
            <p v-if="hasValidCoordinates" class="text-sm opacity-75">
              Précision: {{ accuracyText }}
            </p>
          </div>
        </div>
        
        <button
          @click="captureLocation"
          :disabled="capturing"
          class="px-4 py-2 rounded-xl text-white font-medium transition-all"
          :class="buttonClasses"
        >
          {{ capturing ? 'Capture...' : 'Capturer' }}
        </button>
      </div>
    </div>

    <!-- Coordinates Display -->
    <div v-if="hasValidCoordinates" class="grid grid-cols-2 gap-4 mb-4">
      <div>
        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
          Latitude
        </label>
        <input
          :value="latitude"
          readonly
          class="w-full px-4 py-2.5 rounded-xl border border-gray-300 dark:border-gray-600 bg-gray-100 dark:bg-gray-800 text-gray-900 dark:text-white"
        />
      </div>
      <div>
        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
          Longitude
        </label>
        <input
          :value="longitude"
          readonly
          class="w-full px-4 py-2.5 rounded-xl border border-gray-300 dark:border-gray-600 bg-gray-100 dark:bg-gray-800 text-gray-900 dark:text-white"
        />
      </div>
    </div>

    <!-- Accuracy Warning -->
    <div v-if="hasValidCoordinates && !accuracyValid" class="p-3 rounded-xl bg-yellow-50 dark:bg-yellow-900/20 border border-yellow-200 dark:border-yellow-800">
      <div class="flex items-start space-x-2">
        <svg class="w-5 h-5 text-yellow-600 mt-0.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
        </svg>
        <div>
          <p class="font-medium text-yellow-800 dark:text-yellow-200">Précision insuffisante</p>
          <p class="text-sm text-yellow-700 dark:text-yellow-300">
            La précision GPS doit être inférieure à {{ requiredAccuracy }}m. 
            Essayez de capturer à nouveau à l'extérieur ou attendez une meilleure réception.
          </p>
        </div>
      </div>
    </div>

    <!-- Error -->
    <div v-if="error" class="p-3 rounded-xl bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-800">
      <div class="flex items-start space-x-2">
        <svg class="w-5 h-5 text-red-600 mt-0.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
        </svg>
        <div>
          <p class="font-medium text-red-800 dark:text-red-200">Erreur de localisation</p>
          <p class="text-sm text-red-700 dark:text-red-300">{{ error }}</p>
        </div>
      </div>
    </div>

    <!-- Proximity Alert -->
    <div v-if="proximityAlert && proximityAlert.has_nearby" class="mt-4 p-3 rounded-xl bg-orange-50 dark:bg-orange-900/20 border border-orange-200 dark:border-orange-800">
      <div class="flex items-start space-x-2">
        <svg class="w-5 h-5 text-orange-600 mt-0.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
        </svg>
        <div>
          <p class="font-medium text-orange-800 dark:text-orange-200">
            PDV à proximité détecté ({{ proximityAlert.nearby_count }})
          </p>
          <ul class="text-sm text-orange-700 dark:text-orange-300 mt-1 space-y-1">
            <li v-for="pdv in proximityAlert.nearby_pdvs" :key="pdv.id">
              {{ pdv.nom_point }} - {{ pdv.distance }}m
            </li>
          </ul>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, watch } from 'vue'
import { usePointOfSaleStore } from '../stores/pointOfSale'

const props = defineProps({
  latitude: {
    type: [Number, String],
    default: null
  },
  longitude: {
    type: [Number, String],
    default: null
  },
  accuracy: {
    type: [Number, String],
    default: null
  },
  requiredAccuracy: {
    type: Number,
    default: 30
  },
  checkProximity: {
    type: Boolean,
    default: true
  }
})

const emit = defineEmits(['update:latitude', 'update:longitude', 'update:accuracy', 'captured'])

const pdvStore = usePointOfSaleStore()

// State
const capturing = ref(false)
const error = ref(null)
const proximityAlert = ref(null)

// Computed
const hasValidCoordinates = computed(() => {
  return props.latitude !== null && props.longitude !== null
})

const accuracyValid = computed(() => {
  return props.accuracy !== null && props.accuracy <= props.requiredAccuracy
})

const accuracyText = computed(() => {
  if (!props.accuracy) return 'Inconnue'
  return `${Math.round(props.accuracy)}m`
})

const statusText = computed(() => {
  if (capturing.value) return 'Capture de la position GPS...'
  if (hasValidCoordinates.value) {
    if (accuracyValid.value) return 'Position capturée avec succès'
    return 'Position capturée (précision faible)'
  }
  return 'Position non capturée'
})

const statusClasses = computed(() => {
  if (capturing.value) return 'bg-blue-50 dark:bg-blue-900/20 border border-blue-200 dark:border-blue-800'
  if (hasValidCoordinates.value) {
    if (accuracyValid.value) return 'bg-green-50 dark:bg-green-900/20 border border-green-200 dark:border-green-800'
    return 'bg-yellow-50 dark:bg-yellow-900/20 border border-yellow-200 dark:border-yellow-800'
  }
  return 'bg-gray-50 dark:bg-gray-800 border border-gray-200 dark:border-gray-700'
})

const iconClasses = computed(() => {
  if (capturing.value) return 'bg-blue-100 dark:bg-blue-900 text-blue-600'
  if (hasValidCoordinates.value) {
    if (accuracyValid.value) return 'bg-green-100 dark:bg-green-900 text-green-600'
    return 'bg-yellow-100 dark:bg-yellow-900 text-yellow-600'
  }
  return 'bg-gray-100 dark:bg-gray-700 text-gray-600'
})

const textClasses = computed(() => {
  if (capturing.value) return 'text-blue-800 dark:text-blue-200'
  if (hasValidCoordinates.value) {
    if (accuracyValid.value) return 'text-green-800 dark:text-green-200'
    return 'text-yellow-800 dark:text-yellow-200'
  }
  return 'text-gray-800 dark:text-gray-200'
})

const buttonClasses = computed(() => {
  if (capturing.value) return 'bg-gray-400 cursor-not-allowed'
  return 'bg-orange-500 hover:bg-orange-600'
})

// Methods
const captureLocation = async () => {
  if (!navigator.geolocation) {
    error.value = 'La géolocalisation n\'est pas supportée par votre navigateur'
    return
  }

  capturing.value = true
  error.value = null
  proximityAlert.value = null

  try {
    const position = await new Promise((resolve, reject) => {
      navigator.geolocation.getCurrentPosition(resolve, reject, {
        enableHighAccuracy: true,
        timeout: 30000,
        maximumAge: 0
      })
    })

    const { latitude, longitude, accuracy } = position.coords

    emit('update:latitude', latitude)
    emit('update:longitude', longitude)
    emit('update:accuracy', accuracy)
    emit('captured', { latitude, longitude, accuracy })

    // Check proximity if enabled
    if (props.checkProximity) {
      try {
        proximityAlert.value = await pdvStore.checkProximity(latitude, longitude)
      } catch (e) {
        console.error('Error checking proximity:', e)
      }
    }
  } catch (err) {
    switch (err.code) {
      case err.PERMISSION_DENIED:
        error.value = 'Vous avez refusé l\'accès à votre position. Veuillez autoriser la géolocalisation dans les paramètres de votre navigateur.'
        break
      case err.POSITION_UNAVAILABLE:
        error.value = 'Impossible de déterminer votre position. Vérifiez que le GPS est activé.'
        break
      case err.TIMEOUT:
        error.value = 'Le temps d\'attente pour obtenir la position a expiré. Veuillez réessayer.'
        break
      default:
        error.value = 'Une erreur inattendue est survenue lors de la capture de la position.'
    }
  } finally {
    capturing.value = false
  }
}

// Watch for coordinate changes to check proximity
watch([() => props.latitude, () => props.longitude], async ([lat, lng]) => {
  if (lat && lng && props.checkProximity && !capturing.value) {
    try {
      proximityAlert.value = await pdvStore.checkProximity(lat, lng)
    } catch (e) {
      console.error('Error checking proximity:', e)
    }
  }
})
</script>

<style scoped>
.gps-capture {
  width: 100%;
}
</style>
