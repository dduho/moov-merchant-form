<template>
  <div class="space-y-4">
    <div class="flex space-x-4">
      <button @click="getCurrentLocation" 
              type="button"
              class="btn-primary flex-1"
              :disabled="isGettingLocation">
        <i v-if="isGettingLocation" class="fas fa-spinner fa-spin mr-2"></i>
        <i v-else class="fas fa-location-arrow mr-2"></i>
        {{ isGettingLocation ? 'Localisation...' : 'Ma position actuelle' }}
      </button>
      
      <button @click="showMap = !showMap" 
              type="button"
              class="btn-secondary">
        <i class="fas fa-map mr-2"></i>
        {{ showMap ? 'Masquer' : 'Voir' }} la carte
      </button>
    </div>
    
    <!-- Informations de localisation -->
    <div v-if="location" class="bg-green-50 border border-green-200 rounded-xl p-4">
      <div class="flex items-start space-x-3">
        <i class="fas fa-map-marker-alt text-green-600 mt-1"></i>
        <div class="flex-1">
          <p class="text-sm font-medium text-green-800">Position enregistrée</p>
          <p class="text-sm text-green-600">
            Latitude: {{ location.lat.toFixed(6) }}, 
            Longitude: {{ location.lng.toFixed(6) }}
          </p>
          <p v-if="location.accuracy" class="text-xs text-green-500">
            Précision: ±{{ Math.round(location.accuracy) }}m
          </p>
        </div>
      </div>
    </div>
    
    <!-- Carte interactive -->
    <div v-if="showMap" class="border border-gray-200 rounded-xl overflow-hidden">
      <div ref="mapContainer" class="h-64 bg-gray-100"></div>
    </div>
    
    <!-- Sélection manuelle des coordonnées -->
    <details class="border border-gray-200 rounded-xl" open>
      <summary class="p-4 dark:text-gray-400 cursor-pointer text-sm font-medium text-gray-700 hover:bg-gray-50">
        <i class="fas fa-pencil-alt mr-2 text-orange-500"></i>
        Saisie manuelle des coordonnées
      </summary>
      <div class="p-4 border-t border-gray-200 space-y-4">
        <div class="grid grid-cols-2 gap-4">
          <div>
            <label class="form-label dark:text-gray-400">Latitude</label>
            <input v-model.number="manualLat" 
                   type="number" 
                   step="any"
                   class="form-input"
                   placeholder="Ex: 6.1319">
          </div>
          <div>
            <label class="form-label dark:text-gray-400">Longitude</label>
            <input v-model.number="manualLng" 
                   type="number" 
                   step="any"
                   class="form-input"
                   placeholder="Ex: 1.2225">
          </div>
        </div>
        <button @click="setManualLocation" 
                type="button"
                class="btn-secondary">
          Utiliser ces coordonnées
        </button>
      </div>
    </details>
  </div>
</template>

<script setup>
import { ref, watch, onMounted, onBeforeUnmount, nextTick } from 'vue'
import L from 'leaflet'
import 'leaflet/dist/leaflet.css'
import { useNotification } from '../composables/useNotification'
import { getCurrentPosition, getAccuracyLevel, formatAccuracy } from '../utils/geolocation'

const props = defineProps({
  initialLocation: {
    type: Object,
    default: null
  }
})

const emit = defineEmits(['location-selected'])

const { warning, info, error } = useNotification()

const mapContainer = ref(null)
const showMap = ref(false)
const isGettingLocation = ref(false)
const location = ref(props.initialLocation)
const manualLat = ref(props.initialLocation?.lat || '')
    const manualLng = ref(props.initialLocation?.lng || '')
    
    let map = null
    let marker = null
    
    const initMap = async () => {
      await nextTick()
      
      if (!mapContainer.value || map) return
      
      // Centre sur Lomé par défaut
      const defaultCenter = [6.1319, 1.2225]
      const center = location.value ? [location.value.lat, location.value.lng] : defaultCenter
      
      map = L.map(mapContainer.value).setView(center, 13)
      
      L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
        attribution: '© OpenStreetMap contributors'
      }).addTo(map)
      
      // Marker personnalisé
      if (location.value) {
        addMarker(location.value.lat, location.value.lng)
      }
      
      // Clic sur la carte
      map.on('click', (e) => {
        const { lat, lng } = e.latlng
        setLocation(lat, lng)
      })
    }
    
    const addMarker = (lat, lng) => {
      if (marker) {
        map.removeLayer(marker)
      }
      
      marker = L.marker([lat, lng], {
        icon: L.divIcon({
          html: '<i class="fas fa-map-marker-alt text-2xl text-orange-500"></i>',
          iconSize: [20, 20],
          className: 'custom-div-icon'
        })
      }).addTo(map)
    }
    
    const setLocation = (lat, lng, accuracy = null) => {
      const newLocation = { lat, lng }
      if (accuracy) newLocation.accuracy = accuracy
      
      location.value = newLocation
      
      // Synchroniser les champs manuels
      manualLat.value = lat
      manualLng.value = lng
      
      emit('location-selected', newLocation)
      
      if (map) {
        addMarker(lat, lng)
        map.setView([lat, lng], 15)
      }
    }
    
    const getCurrentLocation = async () => {
      // Vérifier si on est en HTTPS ou localhost
      const isSecureContext = window.isSecureContext || location.hostname === 'localhost' || location.hostname === '127.0.0.1'
      
      if (!isSecureContext) {
        warning('La géolocalisation nécessite une connexion sécurisée (HTTPS). Veuillez utiliser la saisie manuelle des coordonnées ou cliquer sur la carte.', 6000)
        return
      }
      
      isGettingLocation.value = true
      
      try {
        // Utiliser le nouveau service avec retry automatique
        const position = await getCurrentPosition(3) // Max 3 tentatives
        
        const { latitude, longitude, accuracy } = position.coords
        setLocation(latitude, longitude, accuracy)
        
        // Vibration de succès (si supporté)
        if (navigator.vibrate) {
          navigator.vibrate(200)
        }
        
        // Message selon la précision
        const accuracyLevel = getAccuracyLevel(accuracy)
        if (accuracyLevel === 'poor') {
          warning(`Position obtenue avec une précision faible (${formatAccuracy(accuracy)}). Vous pouvez ajuster manuellement la position si nécessaire.`, 5000)
        } else if (position.fromCache) {
          info(`Position récupérée depuis le cache. Précision: ${formatAccuracy(accuracy)}`, 4000)
        }
        
      } catch (error) {
        console.error('Erreur géolocalisation:', error)
        let message = 'Impossible d\'obtenir votre position.'
        
        if (error.code === 1) {
          message = '⚠️ Accès à la localisation refusé.\n\nVeuillez autoriser l\'accès à votre position ou utiliser la saisie manuelle.'
        } else if (error.code === 2) {
          message = '⚠️ Position non disponible.\n\nAssurez-vous que le GPS est activé et réessayez.'
        } else if (error.code === 3) {
          message = '⏱️ Délai d\'attente dépassé.\n\nLe GPS met du temps à répondre. Veuillez réessayer.'
        } else if (error.message?.includes('non supportée')) {
          message = error.message
        }
        
        error(message, 5000)
      } finally {
        isGettingLocation.value = false
      }
    }
    
    const setManualLocation = () => {
      if (!manualLat.value || !manualLng.value) {
        warning('Veuillez saisir la latitude et la longitude')
        return
      }
      setLocation(manualLat.value, manualLng.value)
    }
    
    watch(showMap, async (show) => {
      if (show) {
        await initMap()
      }
    })
    
    watch(() => props.initialLocation, (newLocation) => {
      if (newLocation) {
        location.value = newLocation
        manualLat.value = newLocation.lat || ''
        manualLng.value = newLocation.lng || ''
      }
    })
    
    // Watch manual coordinate changes and auto-emit
    watch([manualLat, manualLng], ([lat, lng]) => {
      if (lat && lng && !isNaN(lat) && !isNaN(lng)) {
        setLocation(lat, lng);
      }
    })
    
    onMounted(() => {
      if (props.initialLocation) {
        location.value = props.initialLocation
        manualLat.value = props.initialLocation.lat
        manualLng.value = props.initialLocation.lng
      }
    })
</script>

<style>
.custom-div-icon {
  background: transparent;
  border: none;
}
</style>