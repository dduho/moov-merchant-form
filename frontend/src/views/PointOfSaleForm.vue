<template>
  <div class="form-container relative min-h-dvh">
    <!-- Decorative circles -->
    <div class="decorative-circle circle-1"></div>
    <div class="decorative-circle circle-2"></div>
    <div class="decorative-circle circle-3"></div>

    <div class="w-full max-w-none lg:max-w-4xl mx-auto px-4 sm:px-3 py-8 relative z-10 pb-24 sm:pb-0">
      <!-- Progress Bar -->
      <div class="mb-8 bg-white dark:bg-gray-800 rounded-2xl shadow-lg p-4 sm:p-6">
        <div class="progress-bar mb-2">
          <div class="progress-fill" :style="`width: ${progress}%`"></div>
        </div>
        <div class="flex justify-between text-[11px] sm:text-xs text-gray-500 dark:text-gray-400">
          <span>Étape {{ currentStep }} sur {{ totalSteps }}</span>
          <span>{{ Math.round(progress) }}% complété</span>
        </div>
      </div>

      <!-- Form Card -->
      <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-lg p-4 sm:p-6 md:p-8">
        <!-- Step 1: Dealer Info -->
        <div v-show="currentStep === 1">
          <h2 class="text-xl sm:text-2xl font-bold text-gray-800 dark:text-white mb-6">
            Informations du Dealer
          </h2>

          <div class="space-y-6">
            <!-- Organization Selection -->
            <div>
              <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                Organisation <span class="text-red-500">*</span>
              </label>
              <select
                v-model="formData.organization_id"
                class="w-full px-4 py-2.5 rounded-xl border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:ring-2 focus:ring-orange-500 focus:border-orange-500"
                :class="{ 'border-red-500': errors.organization_id }"
              >
                <option value="">Sélectionnez une organisation</option>
                <option v-for="org in organizations" :key="org.id" :value="org.id">
                  {{ org.name }} ({{ org.code }})
                </option>
              </select>
              <p v-if="errors.organization_id" class="mt-1 text-sm text-red-500">{{ errors.organization_id }}</p>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
              <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                  Nom du Dealer <span class="text-red-500">*</span>
                </label>
                <input
                  v-model="formData.dealer_name"
                  type="text"
                  class="w-full px-4 py-2.5 rounded-xl border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:ring-2 focus:ring-orange-500"
                  :class="{ 'border-red-500': errors.dealer_name }"
                  placeholder="Nom du dealer"
                />
                <p v-if="errors.dealer_name" class="mt-1 text-sm text-red-500">{{ errors.dealer_name }}</p>
              </div>

              <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                  N° Séquentiel
                </label>
                <input
                  v-model="formData.numero"
                  type="text"
                  class="w-full px-4 py-2.5 rounded-xl border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:ring-2 focus:ring-orange-500"
                  placeholder="Numéro séquentiel"
                />
              </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
              <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                  Numéro Flooz
                </label>
                <input
                  v-model="formData.numero_flooz"
                  type="text"
                  class="w-full px-4 py-2.5 rounded-xl border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:ring-2 focus:ring-orange-500"
                  placeholder="Ex: 228XXXXXXXX"
                />
              </div>

              <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                  Shortcode
                </label>
                <input
                  v-model="formData.shortcode"
                  type="text"
                  class="w-full px-4 py-2.5 rounded-xl border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:ring-2 focus:ring-orange-500"
                  placeholder="Shortcode"
                />
              </div>
            </div>
          </div>
        </div>

        <!-- Step 2: PDV Info -->
        <div v-show="currentStep === 2">
          <h2 class="text-xl sm:text-2xl font-bold text-gray-800 dark:text-white mb-6">
            Informations du Point de Vente
          </h2>

          <div class="space-y-6">
            <div>
              <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                Nom du Point <span class="text-red-500">*</span>
              </label>
              <input
                v-model="formData.nom_point"
                type="text"
                class="w-full px-4 py-2.5 rounded-xl border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:ring-2 focus:ring-orange-500"
                :class="{ 'border-red-500': errors.nom_point }"
                placeholder="Nom du point de vente"
              />
              <p v-if="errors.nom_point" class="mt-1 text-sm text-red-500">{{ errors.nom_point }}</p>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
              <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                  Profil
                </label>
                <select
                  v-model="formData.profil"
                  class="w-full px-4 py-2.5 rounded-xl border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:ring-2 focus:ring-orange-500"
                >
                  <option value="">Sélectionnez un profil</option>
                  <option value="MASTER">Master</option>
                  <option value="AGENT">Agent</option>
                  <option value="STANDARD">Standard</option>
                </select>
              </div>

              <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                  Type d'Activité
                </label>
                <select
                  v-model="formData.type_activite"
                  class="w-full px-4 py-2.5 rounded-xl border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:ring-2 focus:ring-orange-500"
                >
                  <option value="">Sélectionnez un type</option>
                  <option value="BOUTIQUE">Boutique</option>
                  <option value="PHARMACIE">Pharmacie</option>
                  <option value="STATION_SERVICE">Station Service</option>
                  <option value="SUPERMARCHE">Supermarché</option>
                  <option value="KIOSQUE">Kiosque</option>
                  <option value="AUTRE">Autre</option>
                </select>
              </div>
            </div>

            <div>
              <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                Numéro Propriétaire PDV <span class="text-red-500">*</span>
              </label>
              <input
                v-model="formData.numero_proprietaire"
                type="tel"
                class="w-full px-4 py-2.5 rounded-xl border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:ring-2 focus:ring-orange-500"
                :class="{ 'border-red-500': errors.numero_proprietaire }"
                placeholder="Ex: +228 90 XX XX XX"
              />
              <p v-if="errors.numero_proprietaire" class="mt-1 text-sm text-red-500">{{ errors.numero_proprietaire }}</p>
            </div>

            <div>
              <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                Autre Contact
              </label>
              <input
                v-model="formData.autre_contact"
                type="tel"
                class="w-full px-4 py-2.5 rounded-xl border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:ring-2 focus:ring-orange-500"
                placeholder="Numéro alternatif"
              />
            </div>
          </div>
        </div>

        <!-- Step 3: Manager Info -->
        <div v-show="currentStep === 3">
          <h2 class="text-xl sm:text-2xl font-bold text-gray-800 dark:text-white mb-6">
            Informations du Gérant
          </h2>

          <div class="space-y-6">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
              <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                  Prénom <span class="text-red-500">*</span>
                </label>
                <input
                  v-model="formData.firstname"
                  type="text"
                  class="w-full px-4 py-2.5 rounded-xl border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:ring-2 focus:ring-orange-500"
                  :class="{ 'border-red-500': errors.firstname }"
                  placeholder="Prénom du gérant"
                />
                <p v-if="errors.firstname" class="mt-1 text-sm text-red-500">{{ errors.firstname }}</p>
              </div>

              <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                  Nom <span class="text-red-500">*</span>
                </label>
                <input
                  v-model="formData.lastname"
                  type="text"
                  class="w-full px-4 py-2.5 rounded-xl border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:ring-2 focus:ring-orange-500"
                  :class="{ 'border-red-500': errors.lastname }"
                  placeholder="Nom du gérant"
                />
                <p v-if="errors.lastname" class="mt-1 text-sm text-red-500">{{ errors.lastname }}</p>
              </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
              <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                  Date de Naissance
                </label>
                <input
                  v-model="formData.date_of_birth"
                  type="date"
                  class="w-full px-4 py-2.5 rounded-xl border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:ring-2 focus:ring-orange-500"
                />
              </div>

              <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                  Sexe
                </label>
                <select
                  v-model="formData.gender"
                  class="w-full px-4 py-2.5 rounded-xl border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:ring-2 focus:ring-orange-500"
                >
                  <option value="">Sélectionnez</option>
                  <option value="M">Masculin</option>
                  <option value="F">Féminin</option>
                </select>
              </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
              <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                  Type de Pièce
                </label>
                <select
                  v-model="formData.id_description"
                  class="w-full px-4 py-2.5 rounded-xl border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:ring-2 focus:ring-orange-500"
                >
                  <option value="">Sélectionnez</option>
                  <option value="CNI">Carte Nationale d'Identité</option>
                  <option value="PASSPORT">Passeport</option>
                  <option value="PERMIS">Permis de conduire</option>
                  <option value="CARTE_ELECTEUR">Carte d'électeur</option>
                </select>
              </div>

              <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                  Numéro de Pièce
                </label>
                <input
                  v-model="formData.id_number"
                  type="text"
                  class="w-full px-4 py-2.5 rounded-xl border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:ring-2 focus:ring-orange-500"
                  placeholder="Numéro de la pièce"
                />
              </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
              <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                  Date d'Expiration
                </label>
                <input
                  v-model="formData.id_expiry_date"
                  type="date"
                  class="w-full px-4 py-2.5 rounded-xl border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:ring-2 focus:ring-orange-500"
                />
              </div>

              <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                  Nationalité
                </label>
                <input
                  v-model="formData.nationality"
                  type="text"
                  class="w-full px-4 py-2.5 rounded-xl border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:ring-2 focus:ring-orange-500"
                  placeholder="Nationalité"
                />
              </div>
            </div>

            <div>
              <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                Profession
              </label>
              <input
                v-model="formData.profession"
                type="text"
                class="w-full px-4 py-2.5 rounded-xl border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:ring-2 focus:ring-orange-500"
                placeholder="Profession du gérant"
              />
            </div>
          </div>
        </div>

        <!-- Step 4: Location -->
        <div v-show="currentStep === 4">
          <h2 class="text-xl sm:text-2xl font-bold text-gray-800 dark:text-white mb-6">
            Localisation
          </h2>

          <div class="space-y-6">
            <!-- Geography Selector -->
            <GeographySelector
              v-model="locationData"
              :errors="locationErrors"
            />

            <!-- GPS Capture -->
            <div class="mt-6 pt-6 border-t border-gray-200 dark:border-gray-700">
              <h3 class="text-lg font-semibold text-gray-800 dark:text-white mb-4">
                Coordonnées GPS
              </h3>
              <GpsCapture
                v-model:latitude="formData.latitude"
                v-model:longitude="formData.longitude"
                v-model:accuracy="formData.gps_accuracy"
                :required-accuracy="30"
                @captured="onGpsCaptured"
              />
            </div>

            <!-- Proximity Alert -->
            <ProximityAlert
              v-if="proximityAlert && proximityAlert.has_nearby"
              :nearby-pdvs="proximityAlert.nearby_pdvs"
              :alert-distance="proximityAlert.alert_distance"
              @proceed="acknowledgeProximity"
              @cancel="() => {}"
            />
          </div>
        </div>

        <!-- Step 5: Additional Info -->
        <div v-show="currentStep === 5">
          <h2 class="text-xl sm:text-2xl font-bold text-gray-800 dark:text-white mb-6">
            Informations Complémentaires
          </h2>

          <div class="space-y-6">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
              <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                  NIF
                </label>
                <input
                  v-model="formData.nif"
                  type="text"
                  class="w-full px-4 py-2.5 rounded-xl border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:ring-2 focus:ring-orange-500"
                  placeholder="Numéro d'Identification Fiscale"
                />
              </div>

              <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                  Régime Fiscal
                </label>
                <select
                  v-model="formData.regime_fiscal"
                  class="w-full px-4 py-2.5 rounded-xl border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:ring-2 focus:ring-orange-500"
                >
                  <option value="">Sélectionnez</option>
                  <option value="RSI">RSI - Réel Simplifié</option>
                  <option value="RNI">RNI - Réel Normal</option>
                  <option value="FORFAIT">Forfait</option>
                </select>
              </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
              <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                  Support de Visibilité
                </label>
                <select
                  v-model="formData.support_visibilite"
                  class="w-full px-4 py-2.5 rounded-xl border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:ring-2 focus:ring-orange-500"
                >
                  <option value="">Sélectionnez</option>
                  <option value="CHEVALET">Chevalet</option>
                  <option value="POTENCE">Potence</option>
                  <option value="AUTOCOLLANT">Autocollant</option>
                  <option value="AUTRE">Autre</option>
                </select>
              </div>

              <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                  État du Support
                </label>
                <select
                  v-model="formData.etat_support"
                  class="w-full px-4 py-2.5 rounded-xl border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:ring-2 focus:ring-orange-500"
                >
                  <option value="">Sélectionnez</option>
                  <option value="BON">Bon</option>
                  <option value="MAUVAIS">Mauvais</option>
                </select>
              </div>
            </div>

            <div>
              <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                Numéro CAGNT
              </label>
              <input
                v-model="formData.numero_cagnt"
                type="text"
                class="w-full px-4 py-2.5 rounded-xl border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:ring-2 focus:ring-orange-500"
                placeholder="Numéro CAGNT"
              />
            </div>
          </div>
        </div>

        <!-- Navigation Buttons -->
        <div class="flex justify-between mt-8 pt-6 border-t border-gray-200 dark:border-gray-700">
          <button
            v-if="currentStep > 1"
            @click="prevStep"
            class="px-6 py-3 rounded-xl bg-gray-100 dark:bg-gray-700 text-gray-700 dark:text-gray-300 font-medium hover:bg-gray-200 dark:hover:bg-gray-600 transition-colors"
          >
            Précédent
          </button>
          <div v-else></div>

          <button
            v-if="currentStep < totalSteps"
            @click="nextStep"
            class="px-6 py-3 rounded-xl bg-orange-500 text-white font-medium hover:bg-orange-600 transition-colors"
          >
            Suivant
          </button>
          <button
            v-else
            @click="submitForm"
            :disabled="submitting"
            class="px-6 py-3 rounded-xl bg-green-500 text-white font-medium hover:bg-green-600 transition-colors disabled:opacity-50 disabled:cursor-not-allowed"
          >
            <span v-if="submitting">Envoi en cours...</span>
            <span v-else>Soumettre le PDV</span>
          </button>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, reactive, computed, onMounted, watch } from 'vue'
import { useRouter } from 'vue-router'
import { usePointOfSaleStore } from '../stores/pointOfSale'
import { useOrganizationStore } from '../stores/organization'
import GeographySelector from '../components/GeographySelector.vue'
import GpsCapture from '../components/GpsCapture.vue'
import ProximityAlert from '../components/ProximityAlert.vue'

const router = useRouter()
const pdvStore = usePointOfSaleStore()
const orgStore = useOrganizationStore()

// State
const currentStep = ref(1)
const totalSteps = 5
const submitting = ref(false)
const proximityAlert = ref(null)
const proximityAcknowledged = ref(false)

// Form Data
const formData = reactive({
  organization_id: '',
  dealer_name: '',
  numero: '',
  numero_flooz: '',
  shortcode: '',
  nom_point: '',
  profil: '',
  type_activite: '',
  numero_proprietaire: '',
  autre_contact: '',
  firstname: '',
  lastname: '',
  date_of_birth: '',
  gender: '',
  id_description: '',
  id_number: '',
  id_expiry_date: '',
  nationality: 'Togolaise',
  profession: '',
  latitude: null,
  longitude: null,
  gps_accuracy: null,
  nif: '',
  regime_fiscal: '',
  support_visibilite: '',
  etat_support: '',
  numero_cagnt: ''
})

const locationData = ref({
  region: '',
  prefecture: '',
  commune: '',
  canton: '',
  ville: '',
  quartier: '',
  localisation: ''
})

const errors = reactive({})
const locationErrors = reactive({})

// Organizations
const organizations = ref([])

// Computed
const progress = computed(() => {
  return (currentStep.value / totalSteps) * 100
})

// Methods
const validateStep = (step) => {
  Object.keys(errors).forEach(key => delete errors[key])
  Object.keys(locationErrors).forEach(key => delete locationErrors[key])

  let isValid = true

  if (step === 1) {
    if (!formData.organization_id) {
      errors.organization_id = 'L\'organisation est requise'
      isValid = false
    }
    if (!formData.dealer_name?.trim()) {
      errors.dealer_name = 'Le nom du dealer est requis'
      isValid = false
    }
  }

  if (step === 2) {
    if (!formData.nom_point?.trim()) {
      errors.nom_point = 'Le nom du point est requis'
      isValid = false
    }
    if (!formData.numero_proprietaire?.trim()) {
      errors.numero_proprietaire = 'Le numéro du propriétaire est requis'
      isValid = false
    }
  }

  if (step === 3) {
    if (!formData.firstname?.trim()) {
      errors.firstname = 'Le prénom est requis'
      isValid = false
    }
    if (!formData.lastname?.trim()) {
      errors.lastname = 'Le nom est requis'
      isValid = false
    }
  }

  if (step === 4) {
    if (!locationData.value.region) {
      locationErrors.region = 'La région est requise'
      isValid = false
    }
    if (!formData.latitude || !formData.longitude) {
      errors.gps = 'Les coordonnées GPS sont requises'
      isValid = false
    }
  }

  return isValid
}

const nextStep = () => {
  if (validateStep(currentStep.value)) {
    currentStep.value++
    window.scrollTo({ top: 0, behavior: 'smooth' })
  }
}

const prevStep = () => {
  if (currentStep.value > 1) {
    currentStep.value--
    window.scrollTo({ top: 0, behavior: 'smooth' })
  }
}

const onGpsCaptured = async ({ latitude, longitude }) => {
  // Check proximity
  const result = await pdvStore.checkProximity(latitude, longitude)
  if (result.has_nearby) {
    proximityAlert.value = result
    proximityAcknowledged.value = false
  }
}

const acknowledgeProximity = () => {
  proximityAcknowledged.value = true
}

const submitForm = async () => {
  if (!validateStep(currentStep.value)) return

  // Check if proximity was detected but not acknowledged
  if (proximityAlert.value?.has_nearby && !proximityAcknowledged.value) {
    alert('Veuillez confirmer que vous souhaitez créer ce PDV malgré la proximité avec d\'autres PDV existants.')
    return
  }

  submitting.value = true
  try {
    const data = {
      ...formData,
      region: locationData.value.region,
      prefecture: locationData.value.prefecture,
      commune: locationData.value.commune,
      canton: locationData.value.canton,
      ville: locationData.value.ville,
      quartier: locationData.value.quartier,
      localisation: locationData.value.localisation
    }

    await pdvStore.createPdv(data)
    router.push({ name: 'PdvSuccess' })
  } catch (error) {
    console.error('Error submitting form:', error)
    alert(error.response?.data?.error || 'Une erreur est survenue lors de l\'envoi du formulaire')
  } finally {
    submitting.value = false
  }
}

onMounted(async () => {
  try {
    await orgStore.fetchOrganizationsList()
    organizations.value = orgStore.organizationsList
  } catch (error) {
    console.error('Error loading organizations:', error)
  }
})
</script>

<style scoped>
.form-container {
  min-height: 100dvh;
  background: linear-gradient(180deg, #fff7ed 0%, #ffffff 100%);
}

.dark .form-container {
  background: linear-gradient(180deg, #1f2937 0%, #111827 100%);
}

.decorative-circle {
  position: absolute;
  border-radius: 50%;
  background: linear-gradient(135deg, rgba(249, 115, 22, 0.1), rgba(249, 115, 22, 0.05));
  pointer-events: none;
}

.circle-1 {
  width: 300px;
  height: 300px;
  top: -100px;
  right: -50px;
}

.circle-2 {
  width: 200px;
  height: 200px;
  bottom: 20%;
  left: -100px;
}

.circle-3 {
  width: 150px;
  height: 150px;
  top: 40%;
  right: -75px;
}

.progress-bar {
  height: 8px;
  background: #e5e7eb;
  border-radius: 9999px;
  overflow: hidden;
}

.progress-fill {
  height: 100%;
  background: linear-gradient(90deg, #f97316, #fb923c);
  border-radius: 9999px;
  transition: width 0.3s ease;
}
</style>
