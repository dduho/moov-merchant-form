<template>
  <div class="map-component relative" :style="{ height: height }">
    <!-- Map Container -->
    <div ref="mapContainer" class="w-full h-full rounded-xl overflow-hidden"></div>

    <!-- Loading Overlay -->
    <div v-if="loading" class="absolute inset-0 bg-white/80 dark:bg-gray-900/80 flex items-center justify-center rounded-xl z-10">
      <div class="flex flex-col items-center">
        <svg class="w-10 h-10 text-orange-500 animate-spin" fill="none" viewBox="0 0 24 24">
          <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
          <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
        </svg>
        <p class="mt-2 text-sm text-gray-600 dark:text-gray-400">Chargement de la carte...</p>
      </div>
    </div>

    <!-- Controls -->
    <div v-if="showControls" class="absolute top-4 right-4 z-20 flex flex-col space-y-2">
      <button
        @click="centerOnUser"
        class="p-2 bg-white dark:bg-gray-800 rounded-lg shadow-lg hover:bg-gray-50 dark:hover:bg-gray-700 transition-colors"
        title="Centrer sur ma position"
      >
        <svg class="w-5 h-5 text-gray-700 dark:text-gray-300" fill="none" viewBox="0 0 24 24" stroke="currentColor">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" />
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z" />
        </svg>
      </button>
      <button
        @click="zoomIn"
        class="p-2 bg-white dark:bg-gray-800 rounded-lg shadow-lg hover:bg-gray-50 dark:hover:bg-gray-700 transition-colors"
        title="Zoom avant"
      >
        <svg class="w-5 h-5 text-gray-700 dark:text-gray-300" fill="none" viewBox="0 0 24 24" stroke="currentColor">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6" />
        </svg>
      </button>
      <button
        @click="zoomOut"
        class="p-2 bg-white dark:bg-gray-800 rounded-lg shadow-lg hover:bg-gray-50 dark:hover:bg-gray-700 transition-colors"
        title="Zoom arrière"
      >
        <svg class="w-5 h-5 text-gray-700 dark:text-gray-300" fill="none" viewBox="0 0 24 24" stroke="currentColor">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 12H4" />
        </svg>
      </button>
    </div>

    <!-- Legend -->
    <div v-if="showLegend" class="absolute bottom-4 left-4 z-20 bg-white dark:bg-gray-800 rounded-lg shadow-lg p-3">
      <h4 class="text-sm font-semibold text-gray-700 dark:text-gray-300 mb-2">Légende</h4>
      <div class="space-y-1">
        <div class="flex items-center space-x-2">
          <span class="w-3 h-3 rounded-full bg-green-500"></span>
          <span class="text-xs text-gray-600 dark:text-gray-400">Validé</span>
        </div>
        <div class="flex items-center space-x-2">
          <span class="w-3 h-3 rounded-full bg-yellow-500"></span>
          <span class="text-xs text-gray-600 dark:text-gray-400">En attente</span>
        </div>
        <div class="flex items-center space-x-2">
          <span class="w-3 h-3 rounded-full bg-red-500"></span>
          <span class="text-xs text-gray-600 dark:text-gray-400">Rejeté</span>
        </div>
        <div v-if="alertDistance" class="flex items-center space-x-2">
          <span class="w-3 h-3 rounded-full border-2 border-orange-500 border-dashed"></span>
          <span class="text-xs text-gray-600 dark:text-gray-400">Zone proximité ({{ alertDistance }}m)</span>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted, onUnmounted, watch } from 'vue'
import L from 'leaflet'
import 'leaflet/dist/leaflet.css'

const props = defineProps({
  markers: {
    type: Array,
    default: () => []
  },
  center: {
    type: Object,
    default: () => ({ lat: 6.1375, lng: 1.2123 }) // Lomé, Togo
  },
  zoom: {
    type: Number,
    default: 8
  },
  height: {
    type: String,
    default: '400px'
  },
  showControls: {
    type: Boolean,
    default: true
  },
  showLegend: {
    type: Boolean,
    default: true
  },
  alertDistance: {
    type: Number,
    default: 300
  },
  showProximityZones: {
    type: Boolean,
    default: false
  },
  clickable: {
    type: Boolean,
    default: false
  },
  selectable: {
    type: Boolean,
    default: false
  }
})

const emit = defineEmits(['marker-click', 'map-click', 'location-selected'])

const mapContainer = ref(null)
const loading = ref(true)
let map = null
let markersLayer = null
let proximityCircles = []
let selectionMarker = null

// Fix Leaflet default icon issue
delete L.Icon.Default.prototype._getIconUrl
L.Icon.Default.mergeOptions({
  iconRetinaUrl: 'https://cdnjs.cloudflare.com/ajax/libs/leaflet/1.9.4/images/marker-icon-2x.png',
  iconUrl: 'https://cdnjs.cloudflare.com/ajax/libs/leaflet/1.9.4/images/marker-icon.png',
  shadowUrl: 'https://cdnjs.cloudflare.com/ajax/libs/leaflet/1.9.4/images/marker-shadow.png',
})

// Custom marker icons
const createIcon = (color) => {
  return L.divIcon({
    className: 'custom-marker',
    html: `<div style="background-color: ${color}; width: 24px; height: 24px; border-radius: 50%; border: 3px solid white; box-shadow: 0 2px 6px rgba(0,0,0,0.3);"></div>`,
    iconSize: [24, 24],
    iconAnchor: [12, 12],
    popupAnchor: [0, -12]
  })
}

const getMarkerColor = (status) => {
  switch (status) {
    case 'validated': return '#22c55e'
    case 'pending': return '#eab308'
    case 'rejected': return '#ef4444'
    default: return '#3b82f6'
  }
}

const initMap = () => {
  if (!mapContainer.value || map) return

  map = L.map(mapContainer.value, {
    zoomControl: false
  }).setView([props.center.lat, props.center.lng], props.zoom)

  // Add tile layer (OpenStreetMap)
  L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
    attribution: '© OpenStreetMap contributors',
    maxZoom: 19
  }).addTo(map)

  // Create markers layer group
  markersLayer = L.layerGroup().addTo(map)

  // Add click handler for selectable mode
  if (props.selectable) {
    map.on('click', (e) => {
      const { lat, lng } = e.latlng
      emit('location-selected', { lat, lng })
      
      // Update selection marker
      if (selectionMarker) {
        selectionMarker.setLatLng([lat, lng])
      } else {
        selectionMarker = L.marker([lat, lng], {
          icon: L.divIcon({
            className: 'custom-marker',
            html: '<div style="background-color: #f97316; width: 30px; height: 30px; border-radius: 50%; border: 4px solid white; box-shadow: 0 2px 8px rgba(0,0,0,0.4);"></div>',
            iconSize: [30, 30],
            iconAnchor: [15, 15]
          })
        }).addTo(map)
      }
    })
  }

  // Add click handler for map clicks
  if (props.clickable) {
    map.on('click', (e) => {
      emit('map-click', { lat: e.latlng.lat, lng: e.latlng.lng })
    })
  }

  loading.value = false
  updateMarkers()
}

const updateMarkers = () => {
  if (!markersLayer) return

  // Clear existing markers and circles
  markersLayer.clearLayers()
  proximityCircles.forEach(circle => circle.remove())
  proximityCircles = []

  // Add new markers
  props.markers.forEach(markerData => {
    const marker = L.marker([markerData.latitude, markerData.longitude], {
      icon: createIcon(getMarkerColor(markerData.status))
    })

    // Create popup content
    const popupContent = `
      <div class="p-2">
        <h4 class="font-semibold text-gray-900">${markerData.nom_point || 'PDV'}</h4>
        <p class="text-sm text-gray-600">${markerData.dealer_name || ''}</p>
        <p class="text-xs text-gray-500">${markerData.reference_number || ''}</p>
        <p class="text-xs mt-1">
          <span class="inline-block px-2 py-0.5 rounded-full text-white" 
                style="background-color: ${getMarkerColor(markerData.status)}">
            ${markerData.status === 'validated' ? 'Validé' : markerData.status === 'pending' ? 'En attente' : 'Rejeté'}
          </span>
        </p>
      </div>
    `
    marker.bindPopup(popupContent)

    // Add click handler
    marker.on('click', () => {
      emit('marker-click', markerData)
    })

    markersLayer.addLayer(marker)

    // Add proximity circle if enabled
    if (props.showProximityZones && props.alertDistance && markerData.status === 'validated') {
      const circle = L.circle([markerData.latitude, markerData.longitude], {
        radius: props.alertDistance,
        color: '#f97316',
        fillColor: '#f97316',
        fillOpacity: 0.1,
        weight: 1,
        dashArray: '5, 5'
      }).addTo(map)
      proximityCircles.push(circle)
    }
  })
}

const centerOnUser = () => {
  if (!navigator.geolocation) return

  navigator.geolocation.getCurrentPosition((position) => {
    const { latitude, longitude } = position.coords
    map?.setView([latitude, longitude], 15)
  })
}

const zoomIn = () => {
  map?.zoomIn()
}

const zoomOut = () => {
  map?.zoomOut()
}

const setView = (lat, lng, zoom = 15) => {
  map?.setView([lat, lng], zoom)
}

const fitBounds = () => {
  if (!map || props.markers.length === 0) return

  const bounds = L.latLngBounds(
    props.markers.map(m => [m.latitude, m.longitude])
  )
  map.fitBounds(bounds, { padding: [50, 50] })
}

// Watch for marker changes
watch(() => props.markers, () => {
  updateMarkers()
}, { deep: true })

// Watch for center changes
watch(() => props.center, (newCenter) => {
  if (map && newCenter) {
    map.setView([newCenter.lat, newCenter.lng], props.zoom)
  }
})

onMounted(() => {
  setTimeout(() => {
    initMap()
  }, 100)
})

onUnmounted(() => {
  if (map) {
    map.remove()
    map = null
  }
})

// Expose methods for parent components
defineExpose({
  setView,
  fitBounds,
  centerOnUser,
  zoomIn,
  zoomOut
})
</script>

<style>
.custom-marker {
  background: transparent;
  border: none;
}

.leaflet-popup-content-wrapper {
  border-radius: 12px;
}

.leaflet-popup-content {
  margin: 8px 12px;
}
</style>
