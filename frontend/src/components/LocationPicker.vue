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
      <summary class="p-4 cursor-pointer text-sm font-medium text-gray-700 hover:bg-gray-50">
        <i class="fas fa-pencil-alt mr-2 text-orange-500"></i>
        Saisie manuelle des coordonnées (recommandé si pas de HTTPS)
      </summary>
      <div class="p-4 border-t border-gray-200 space-y-4">
        <div class="grid grid-cols-2 gap-4">
          <div>
            <label class="form-label">Latitude</label>
            <input v-model.number="manualLat" 
                   type="number" 
                   step="any"
                   class="form-input"
                   placeholder="Ex: 6.1319">
          </div>
          <div>
            <label class="form-label">Longitude</label>
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

<script>
import { ref, onMounted, watch, nextTick } from 'vue'
import L from 'leaflet'
import 'leaflet/dist/leaflet.css'

export default {
  name: 'LocationPicker',
  props: {
    initialLocation: {
      type: Object,
      default: null
    }
  },
  emits: ['location-selected'],
  setup(props, { emit }) {
    const mapContainer = ref(null)
    const showMap = ref(false)
    const isGettingLocation = ref(false)
    const location = ref(props.initialLocation)
    const manualLat = ref('')
    const manualLng = ref('')
    
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
      emit('location-selected', newLocation)
      
      if (map) {
        addMarker(lat, lng)
        map.setView([lat, lng], 15)
      }
    }
    
    const getCurrentLocation = () => {
      if (!navigator.geolocation) {
        alert('La géolocalisation n\'est pas supportée par votre navigateur')
        return
      }
      
      // Vérifier si on est en HTTPS ou localhost
      const isSecureContext = window.isSecureContext || location.hostname === 'localhost' || location.hostname === '127.0.0.1'
      
      if (!isSecureContext) {
        alert('⚠️ La géolocalisation nécessite une connexion sécurisée (HTTPS).\n\nVeuillez utiliser la saisie manuelle des coordonnées ou cliquer sur la carte pour définir votre position.')
        return
      }
      
      isGettingLocation.value = true
      
      navigator.geolocation.getCurrentPosition(
        (position) => {
          const { latitude, longitude, accuracy } = position.coords
          setLocation(latitude, longitude, accuracy)
          isGettingLocation.value = false
        },
        (error) => {
          console.error('Erreur géolocalisation:', error)
          let message = 'Impossible d\'obtenir votre position.'
          
          if (error.code === 1) {
            message = '⚠️ Accès à la localisation refusé.\n\nSi vous êtes en HTTP (non sécurisé), la géolocalisation est bloquée par le navigateur.\n\nVeuillez utiliser la saisie manuelle des coordonnées ou cliquer sur la carte.'
          } else if (error.code === 2) {
            message = 'Position non disponible. Veuillez réessayer.'
          } else if (error.code === 3) {
            message = 'Délai d\'attente dépassé. Veuillez réessayer.'
          }
          
          alert(message)
          isGettingLocation.value = false
        },
        {
          enableHighAccuracy: true,
          timeout: 10000,
          maximumAge: 300000
        }
      )
    }
    
    const setManualLocation = () => {
      if (!manualLat.value || !manualLng.value) {
        alert('Veuillez saisir la latitude et la longitude')
        return
      }
      setLocation(manualLat.value, manualLng.value)
    }
    
    watch(showMap, async (show) => {
      if (show) {
        await initMap()
      }
    })
    
    onMounted(() => {
      if (props.initialLocation) {
        location.value = props.initialLocation
      }
    })
    
    return {
      mapContainer,
      showMap,
      isGettingLocation,
      location,
      manualLat,
      manualLng,
      getCurrentLocation,
      setManualLocation
    }
  }
}
</script>

<style>
.custom-div-icon {
  background: transparent;
  border: none;
}
</style>