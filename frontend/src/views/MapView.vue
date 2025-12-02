<template>
  <div class="map-view min-h-screen bg-gray-50 dark:bg-gray-900">
    <!-- Header -->
    <div class="bg-white dark:bg-gray-800 shadow-sm border-b border-gray-200 dark:border-gray-700">
      <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-4">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between space-y-4 sm:space-y-0">
          <div>
            <h1 class="text-xl sm:text-2xl font-bold text-gray-800 dark:text-white">
              Carte des Points de Vente
            </h1>
            <p class="text-sm text-gray-500 dark:text-gray-400">
              {{ mapData.length }} PDV affichés
            </p>
          </div>
          
          <div class="flex flex-wrap gap-3">
            <!-- Region Filter -->
            <select
              v-model="selectedRegion"
              @change="applyFilters"
              class="px-3 py-2 rounded-xl border border-gray-200 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-700 dark:text-gray-300 text-sm"
            >
              <option value="">Toutes les régions</option>
              <option v-for="region in regions" :key="region" :value="region">
                {{ region }}
              </option>
            </select>

            <!-- Status Filter -->
            <select
              v-model="selectedStatus"
              @change="applyFilters"
              class="px-3 py-2 rounded-xl border border-gray-200 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-700 dark:text-gray-300 text-sm"
            >
              <option value="">Tous les statuts</option>
              <option value="validated">Validés</option>
              <option value="pending">En attente</option>
              <option value="rejected">Rejetés</option>
            </select>

            <!-- Toggle Proximity Zones -->
            <button
              @click="showProximityZones = !showProximityZones"
              :class="[
                'px-4 py-2 rounded-xl text-sm font-medium transition-colors',
                showProximityZones
                  ? 'bg-orange-500 text-white'
                  : 'bg-gray-100 dark:bg-gray-700 text-gray-700 dark:text-gray-300'
              ]"
            >
              {{ showProximityZones ? 'Masquer' : 'Afficher' }} zones
            </button>

            <!-- Refresh -->
            <button
              @click="loadMapData"
              :disabled="loading"
              class="px-4 py-2 rounded-xl bg-gray-100 dark:bg-gray-700 text-gray-700 dark:text-gray-300 hover:bg-gray-200 dark:hover:bg-gray-600 transition-colors"
            >
              <svg class="w-5 h-5" :class="{ 'animate-spin': loading }" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
              </svg>
            </button>
          </div>
        </div>
      </div>
    </div>

    <!-- Map Container -->
    <div class="relative" style="height: calc(100vh - 180px);">
      <MapComponent
        ref="mapRef"
        :markers="filteredMapData"
        :center="mapCenter"
        :zoom="8"
        :show-proximity-zones="showProximityZones"
        :alert-distance="alertDistance"
        height="100%"
        @marker-click="onMarkerClick"
      />
    </div>

    <!-- PDV Details Modal -->
    <Teleport to="body">
      <div v-if="selectedPdv" class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/50" @click.self="selectedPdv = null">
        <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-xl w-full max-w-lg max-h-[80vh] overflow-auto">
          <div class="p-6">
            <div class="flex items-center justify-between mb-4">
              <h3 class="text-lg font-bold text-gray-900 dark:text-white">
                {{ selectedPdv.nom_point }}
              </h3>
              <button @click="selectedPdv = null" class="text-gray-500 hover:text-gray-700 dark:hover:text-gray-300">
                <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                </svg>
              </button>
            </div>

            <div class="space-y-3">
              <div class="flex items-center space-x-2">
                <span class="inline-block px-3 py-1 rounded-full text-sm font-medium"
                      :class="{
                        'bg-green-100 text-green-800': selectedPdv.status === 'validated',
                        'bg-yellow-100 text-yellow-800': selectedPdv.status === 'pending',
                        'bg-red-100 text-red-800': selectedPdv.status === 'rejected'
                      }">
                  {{ selectedPdv.status === 'validated' ? 'Validé' : selectedPdv.status === 'pending' ? 'En attente' : 'Rejeté' }}
                </span>
                <span class="text-sm text-gray-500">{{ selectedPdv.reference_number }}</span>
              </div>

              <div class="grid grid-cols-2 gap-4 text-sm">
                <div>
                  <p class="text-gray-500 dark:text-gray-400">Dealer</p>
                  <p class="font-medium text-gray-900 dark:text-white">{{ selectedPdv.dealer_name }}</p>
                </div>
                <div>
                  <p class="text-gray-500 dark:text-gray-400">Région</p>
                  <p class="font-medium text-gray-900 dark:text-white">{{ selectedPdv.region }}</p>
                </div>
                <div>
                  <p class="text-gray-500 dark:text-gray-400">Préfecture</p>
                  <p class="font-medium text-gray-900 dark:text-white">{{ selectedPdv.prefecture || '-' }}</p>
                </div>
                <div>
                  <p class="text-gray-500 dark:text-gray-400">Coordonnées</p>
                  <p class="font-medium text-gray-900 dark:text-white text-xs">
                    {{ selectedPdv.latitude?.toFixed(6) }}, {{ selectedPdv.longitude?.toFixed(6) }}
                  </p>
                </div>
              </div>

              <div class="pt-4 flex space-x-3">
                <router-link
                  :to="{ name: 'PdvDetails', params: { id: selectedPdv.id } }"
                  class="flex-1 px-4 py-2 text-center rounded-xl bg-orange-500 text-white font-medium hover:bg-orange-600 transition-colors"
                >
                  Voir détails
                </router-link>
                <button
                  @click="centerOnPdv(selectedPdv)"
                  class="px-4 py-2 rounded-xl bg-gray-100 dark:bg-gray-700 text-gray-700 dark:text-gray-300 font-medium hover:bg-gray-200 dark:hover:bg-gray-600 transition-colors"
                >
                  Centrer
                </button>
              </div>
            </div>
          </div>
        </div>
      </div>
    </Teleport>
  </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue'
import { usePointOfSaleStore } from '../stores/pointOfSale'
import MapComponent from '../components/MapComponent.vue'

const pdvStore = usePointOfSaleStore()

// State
const mapRef = ref(null)
const loading = ref(false)
const selectedRegion = ref('')
const selectedStatus = ref('')
const showProximityZones = ref(false)
const selectedPdv = ref(null)

// Map data
const mapData = computed(() => pdvStore.mapData)
const alertDistance = computed(() => pdvStore.alertDistance)

const mapCenter = { lat: 6.1375, lng: 1.2123 } // Lomé, Togo

const regions = ['MARITIME', 'PLATEAUX', 'CENTRALE', 'KARA', 'SAVANES']

// Filtered data
const filteredMapData = computed(() => {
  let data = mapData.value

  if (selectedRegion.value) {
    data = data.filter(p => p.region === selectedRegion.value)
  }

  if (selectedStatus.value) {
    data = data.filter(p => p.status === selectedStatus.value)
  }

  return data
})

// Methods
const loadMapData = async () => {
  loading.value = true
  try {
    await pdvStore.fetchMapData()
  } catch (error) {
    console.error('Error loading map data:', error)
  } finally {
    loading.value = false
  }
}

const applyFilters = () => {
  // Filters are reactive, no need to do anything
}

const onMarkerClick = (pdv) => {
  selectedPdv.value = pdv
}

const centerOnPdv = (pdv) => {
  if (mapRef.value && pdv.latitude && pdv.longitude) {
    mapRef.value.setView(pdv.latitude, pdv.longitude, 16)
  }
  selectedPdv.value = null
}

onMounted(() => {
  loadMapData()
})
</script>

<style scoped>
.map-view {
  width: 100%;
}
</style>
