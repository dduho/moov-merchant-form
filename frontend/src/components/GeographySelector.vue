<template>
  <div class="geography-selector space-y-4">
    <!-- Région -->
    <div>
      <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
        Région <span class="text-red-500">*</span>
      </label>
      <select
        v-model="selectedRegion"
        @change="onRegionChange"
        class="w-full px-4 py-2.5 rounded-xl border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:ring-2 focus:ring-orange-500 focus:border-orange-500"
        :class="{ 'border-red-500': errors.region }"
      >
        <option value="">Sélectionnez une région</option>
        <option v-for="region in regions" :key="region.id || region" :value="region.name || region">
          {{ region.name || region }}
        </option>
      </select>
      <p v-if="errors.region" class="mt-1 text-sm text-red-500">{{ errors.region }}</p>
    </div>

    <!-- Préfecture -->
    <div v-if="showPrefecture">
      <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
        Préfecture
      </label>
      <select
        v-model="selectedPrefecture"
        @change="onPrefectureChange"
        class="w-full px-4 py-2.5 rounded-xl border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:ring-2 focus:ring-orange-500 focus:border-orange-500"
        :disabled="loadingPrefectures"
      >
        <option value="">Sélectionnez une préfecture</option>
        <option v-for="prefecture in prefectures" :key="prefecture.id" :value="prefecture.name">
          {{ prefecture.name }}
        </option>
      </select>
      <div v-if="loadingPrefectures" class="mt-1 text-sm text-gray-500">
        Chargement des préfectures...
      </div>
    </div>

    <!-- Commune -->
    <div v-if="showCommune">
      <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
        Commune
      </label>
      <select
        v-model="selectedCommune"
        @change="onCommuneChange"
        class="w-full px-4 py-2.5 rounded-xl border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:ring-2 focus:ring-orange-500 focus:border-orange-500"
        :disabled="loadingCommunes"
      >
        <option value="">Sélectionnez une commune</option>
        <option v-for="commune in communes" :key="commune.id" :value="commune.name">
          {{ commune.name }}
        </option>
      </select>
      <div v-if="loadingCommunes" class="mt-1 text-sm text-gray-500">
        Chargement des communes...
      </div>
    </div>

    <!-- Ville -->
    <div>
      <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
        Ville
      </label>
      <input
        v-model="selectedVille"
        @input="onVilleChange"
        type="text"
        placeholder="Nom de la ville"
        class="w-full px-4 py-2.5 rounded-xl border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:ring-2 focus:ring-orange-500 focus:border-orange-500"
      />
    </div>

    <!-- Quartier -->
    <div>
      <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
        Quartier
      </label>
      <input
        v-model="selectedQuartier"
        @input="onQuartierChange"
        type="text"
        placeholder="Nom du quartier"
        class="w-full px-4 py-2.5 rounded-xl border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:ring-2 focus:ring-orange-500 focus:border-orange-500"
      />
    </div>

    <!-- Localisation détaillée -->
    <div>
      <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
        Localisation détaillée
      </label>
      <textarea
        v-model="selectedLocalisation"
        @input="onLocalisationChange"
        rows="2"
        placeholder="Description détaillée de l'emplacement (repères, proximité...)"
        class="w-full px-4 py-2.5 rounded-xl border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:ring-2 focus:ring-orange-500 focus:border-orange-500 resize-none"
      ></textarea>
    </div>
  </div>
</template>

<script setup>
import { ref, watch, computed, onMounted } from 'vue'
import GeographyService from '../services/GeographyService'

const props = defineProps({
  modelValue: {
    type: Object,
    default: () => ({
      region: '',
      prefecture: '',
      commune: '',
      canton: '',
      ville: '',
      quartier: '',
      localisation: ''
    })
  },
  errors: {
    type: Object,
    default: () => ({})
  },
  useApiHierarchy: {
    type: Boolean,
    default: false
  }
})

const emit = defineEmits(['update:modelValue'])

// Static regions list (Togo)
const staticRegions = [
  'MARITIME',
  'PLATEAUX',
  'CENTRALE',
  'KARA',
  'SAVANES'
]

// State
const regions = ref([])
const prefectures = ref([])
const communes = ref([])
const loadingPrefectures = ref(false)
const loadingCommunes = ref(false)

const selectedRegion = ref(props.modelValue?.region || '')
const selectedPrefecture = ref(props.modelValue?.prefecture || '')
const selectedCommune = ref(props.modelValue?.commune || '')
const selectedVille = ref(props.modelValue?.ville || '')
const selectedQuartier = ref(props.modelValue?.quartier || '')
const selectedLocalisation = ref(props.modelValue?.localisation || '')

// Computed
const showPrefecture = computed(() => selectedRegion.value && prefectures.value.length > 0)
const showCommune = computed(() => selectedPrefecture.value && communes.value.length > 0)

// Methods
const emitValue = () => {
  emit('update:modelValue', {
    region: selectedRegion.value,
    prefecture: selectedPrefecture.value,
    commune: selectedCommune.value,
    canton: '',
    ville: selectedVille.value,
    quartier: selectedQuartier.value,
    localisation: selectedLocalisation.value
  })
}

const loadRegions = async () => {
  if (props.useApiHierarchy) {
    try {
      const response = await GeographyService.getRegions()
      regions.value = response.data || []
    } catch (error) {
      console.error('Error loading regions:', error)
      regions.value = staticRegions.map(r => ({ name: r }))
    }
  } else {
    regions.value = staticRegions.map(r => ({ name: r }))
  }
}

const loadPrefectures = async (regionName) => {
  if (!props.useApiHierarchy) {
    prefectures.value = []
    return
  }
  
  loadingPrefectures.value = true
  try {
    const region = regions.value.find(r => r.name === regionName)
    if (region?.id) {
      const response = await GeographyService.getPrefectures(region.id)
      prefectures.value = response.data || []
    }
  } catch (error) {
    console.error('Error loading prefectures:', error)
    prefectures.value = []
  } finally {
    loadingPrefectures.value = false
  }
}

const loadCommunes = async (prefectureName) => {
  if (!props.useApiHierarchy) {
    communes.value = []
    return
  }
  
  loadingCommunes.value = true
  try {
    const prefecture = prefectures.value.find(p => p.name === prefectureName)
    if (prefecture?.id) {
      const response = await GeographyService.getCommunes(prefecture.id)
      communes.value = response.data || []
    }
  } catch (error) {
    console.error('Error loading communes:', error)
    communes.value = []
  } finally {
    loadingCommunes.value = false
  }
}

const onRegionChange = async () => {
  selectedPrefecture.value = ''
  selectedCommune.value = ''
  prefectures.value = []
  communes.value = []
  
  if (selectedRegion.value) {
    await loadPrefectures(selectedRegion.value)
  }
  emitValue()
}

const onPrefectureChange = async () => {
  selectedCommune.value = ''
  communes.value = []
  
  if (selectedPrefecture.value) {
    await loadCommunes(selectedPrefecture.value)
  }
  emitValue()
}

const onCommuneChange = () => {
  emitValue()
}

const onVilleChange = () => {
  emitValue()
}

const onQuartierChange = () => {
  emitValue()
}

const onLocalisationChange = () => {
  emitValue()
}

// Watch for external changes
watch(() => props.modelValue, (newValue) => {
  if (newValue) {
    selectedRegion.value = newValue.region || ''
    selectedPrefecture.value = newValue.prefecture || ''
    selectedCommune.value = newValue.commune || ''
    selectedVille.value = newValue.ville || ''
    selectedQuartier.value = newValue.quartier || ''
    selectedLocalisation.value = newValue.localisation || ''
  }
}, { deep: true })

onMounted(() => {
  loadRegions()
})
</script>

<style scoped>
.geography-selector {
  max-width: 100%;
}
</style>
