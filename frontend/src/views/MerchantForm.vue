<template>
  <div class="form-container relative min-h-dvh">
    <!-- Cercles décoratifs -->
    <div class="decorative-circle circle-1"></div>
    <div class="decorative-circle circle-2"></div>
    <div class="decorative-circle circle-3"></div>
    <div class="decorative-circle circle-4"></div>
    <div class="decorative-circle circle-5"></div>
    <div class="decorative-circle circle-6"></div>

    <!-- Contenu du formulaire -->
    <div class="w-full max-w-none lg:max-w-4xl mx-auto px-4 sm:px-3 py-8 relative z-10 pb-24 sm:pb-0">
      <!-- Barre de progression -->
      <div class="mb-8 bg-white rounded-2xl shadow-lg p-4 sm:p-6">
        <div class="progress-bar mb-2">
          <div class="progress-fill" :style="`width: ${progress}%`"></div>
        </div>
        <div class="flex justify-between text-[11px] sm:text-xs text-gray-500">
          <span>Étape {{ currentStep }} sur {{ totalSteps }}</span>
          <span>{{ Math.round(progress) }}% complété</span>
        </div>
      </div>

      <form @submit.prevent="submitForm" class="bg-white rounded-2xl shadow-lg p-4 sm:p-6 md:p-8">
        <!-- Zone animée avec auto-height -->
        <div ref="stage" class="stage relative overflow-visible"
          :style="{ minHeight: stageHeight }">
          <Transition mode="out-in" :enter-active-class="'transition duration-300 ease-out'"
            :leave-active-class="'transition duration-200 ease-in'"
            :enter-from-class="direction > 0 ? 'opacity-0 translate-x-4' : 'opacity-0 -translate-x-4'"
            :enter-to-class="'opacity-100 translate-x-0'" :leave-from-class="'opacity-100 translate-x-0'"
            :leave-to-class="direction > 0 ? 'opacity-0 -translate-x-4' : 'opacity-0 translate-x-4'"
            @before-enter="beforeEnter" @enter="enter" @after-enter="afterEnter" @before-leave="beforeLeave"
            @leave="leave">
            <div :key="currentStep" class="py-1">
              
              <!-- Étape 1: Informations personnelles du représentant -->
              <template v-if="currentStep === 1">
                <div class="form-section">
                  <h2 class="section-title">
                    <i class="fas fa-user-circle text-orange-500 mr-2"></i>
                    Informations Personnelles du Représentant
                  </h2>

                  <div class="grid grid-cols-1 md:grid-cols-2 gap-4 sm:gap-6">
                    <div class="form-group">
                      <label class="form-label">Nom *</label>
                      <input v-model="formData.lastName" type="text" 
                        class="form-input h-12" :class="{ 'border-red-500': errors.lastName }"
                        placeholder="Nom de famille" autocomplete="family-name" required>
                      <p v-if="errors.lastName" class="mt-1 text-sm text-red-600">{{ errors.lastName }}</p>
                    </div>

                    <div class="form-group">
                      <label class="form-label">Prénom(s) *</label>
                      <input v-model="formData.firstName" type="text" 
                        class="form-input h-12" :class="{ 'border-red-500': errors.firstName }"
                        placeholder="Prénom(s)" autocomplete="given-name" required>
                      <p v-if="errors.firstName" class="mt-1 text-sm text-red-600">{{ errors.firstName }}</p>
                    </div>

                    <div class="form-group">
                      <label class="form-label">Date de naissance *</label>
                      <input v-model="formData.birthDate" type="date" 
                        class="form-input h-12" :class="{ 'border-red-500': errors.birthDate }"
                        autocomplete="bday" required>
                      <p v-if="errors.birthDate" class="mt-1 text-sm text-red-600">{{ errors.birthDate }}</p>
                    </div>

                    <div class="form-group">
                      <label class="form-label">Lieu de naissance *</label>
                      <input v-model="formData.birthPlace" type="text" 
                        class="form-input h-12" :class="{ 'border-red-500': errors.birthPlace }"
                        placeholder="Ville, Pays" required>
                      <p v-if="errors.birthPlace" class="mt-1 text-sm text-red-600">{{ errors.birthPlace }}</p>
                    </div>

                    <div class="form-group">
                      <label class="form-label">Genre *</label>
                      <select v-model="formData.gender" 
                        class="form-input h-12" :class="{ 'border-red-500': errors.gender }"
                        required>
                        <option value="">Sélectionnez</option>
                        <option value="M">Masculin</option>
                        <option value="F">Féminin</option>
                      </select>
                      <p v-if="errors.gender" class="mt-1 text-sm text-red-600">{{ errors.gender }}</p>
                    </div>

                    <div class="form-group">
                      <label class="form-label">Nationalité *</label>
                      <input v-model="formData.nationality" type="text" 
                        class="form-input h-12" :class="{ 'border-red-500': errors.nationality }"
                        placeholder="Ex: Togolaise" required>
                      <p v-if="errors.nationality" class="mt-1 text-sm text-red-600">{{ errors.nationality }}</p>
                    </div>

                    <div class="form-group">
                      <label class="form-label">Téléphone personnel *</label>
                      <PhoneInput v-model="formData.personalPhone" 
                        :class="{ 'border-red-500': errors.personalPhone }"
                        autocomplete="tel" required />
                      <p v-if="errors.personalPhone" class="mt-1 text-sm text-red-600">{{ errors.personalPhone }}</p>
                    </div>

                    <div class="form-group">
                      <label class="form-label">Email</label>
                      <input v-model="formData.email" type="email" 
                        class="form-input h-12" :class="{ 'border-red-500': errors.email }"
                        placeholder="email@exemple.com" autocomplete="email">
                      <p v-if="errors.email" class="mt-1 text-sm text-red-600">{{ errors.email }}</p>
                    </div>

                    <div class="form-group md:col-span-2">
                      <label class="form-label">Adresse complète *</label>
                      <textarea v-model="formData.address" 
                        class="form-input min-h-[100px]" :class="{ 'border-red-500': errors.address }"
                        placeholder="Adresse détaillée" autocomplete="street-address" required></textarea>
                      <p v-if="errors.address" class="mt-1 text-sm text-red-600">{{ errors.address }}</p>
                    </div>
                  </div>
                </div>
              </template>

              <!-- Étape 2: Documents d'identité -->
              <template v-else-if="currentStep === 2">
                <div class="form-section">
                  <h2 class="section-title">
                    <i class="fas fa-id-card text-orange-500 mr-2"></i>
                    Documents d'Identité
                  </h2>

                  <!-- Type et numéro de pièce -->
                  <div class="grid grid-cols-1 md:grid-cols-3 gap-4 sm:gap-6 mb-6">
                    <div class="form-group">
                      <label class="form-label">
                        Type de pièce d'identité <span v-if="!formData.hasAnidCard">*</span>
                      </label>
                      <select v-model="formData.idType" 
                        class="form-input h-12" 
                        :class="{ 'border-red-500': errors.idType }"
                        :required="!formData.hasAnidCard">
                        <option value="">Sélectionnez</option>
                        <option value="cni">Carte Nationale d'Identité</option>
                        <option value="passport">Passeport</option>
                        <option value="residence">Carte de séjour</option>
                        <option value="elector">Carte d'électeur</option>
                      </select>
                      <p v-if="errors.idType" class="mt-1 text-sm text-red-600">{{ errors.idType }}</p>
                    </div>

                    <div class="form-group">
                      <label class="form-label">
                        Numéro de pièce <span v-if="!formData.hasAnidCard">*</span>
                      </label>
                      <IdNumberInput 
                        v-if="formData.idType"
                        v-model="formData.idNumber"
                        :id-type="formData.idType"
                        :class="{ 'border-red-500': errors.idNumber }"
                        :required="!formData.hasAnidCard"
                      />
                      <input v-else
                        v-model="formData.idNumber"
                        type="text"
                        class="form-input h-12"
                        placeholder="Sélectionnez d'abord le type de pièce"
                        disabled
                      >
                      <p v-if="errors.idNumber" class="mt-1 text-sm text-red-600">{{ errors.idNumber }}</p>
                    </div>

                    <div class="form-group">
                      <label class="form-label">
                        Date d'expiration <span v-if="!formData.hasAnidCard">*</span>
                      </label>
                      <input v-model="formData.idExpiryDate" 
                        type="date" 
                        class="form-input h-12" 
                        :class="{ 'border-red-500': errors.idExpiryDate }"
                        :required="!formData.hasAnidCard">
                      <p v-if="errors.idExpiryDate" class="mt-1 text-sm text-red-600">{{ errors.idExpiryDate }}</p>
                    </div>
                  </div>

                  <!-- Upload pièce d'identité -->
                  <div class="mb-6">
                    <label class="form-label">
                      Photo de la pièce d'identité <span v-if="!formData.hasAnidCard">*</span>
                    </label>
                    <FileUpload @file-uploaded="handleFileUpload('idCard', $event)" 
                      accept="image/*"
                      :current-file="formData.documents.idCard"
                      :has-error="!!errors.idCard" />
                    <p v-if="errors.idCard" class="mt-1 text-sm text-red-600">{{ errors.idCard }}</p>
                  </div>

                  <!-- Carte ANID -->
                  <div class="mb-4">
                    <label class="flex items-center space-x-2">
                      <input v-model="formData.hasAnidCard" type="checkbox"
                        class="w-4 h-4 text-orange-600 border-gray-300 rounded focus:ring-orange-500">
                      <span class="text-sm font-medium text-gray-700">Je possède une carte ANID</span>
                    </label>
                  </div>

                  <div v-if="formData.hasAnidCard" class="mb-6">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4 sm:gap-6 mb-4">
                      <div class="form-group">
                        <label class="form-label">Numéro ANID *</label>
                        <input v-model="formData.anidNumber" 
                          type="text" 
                          class="form-input h-12"
                          :class="{ 'border-red-500': errors.anidNumber }"
                          placeholder="Numéro ANID"
                          required>
                        <p v-if="errors.anidNumber" class="mt-1 text-sm text-red-600">{{ errors.anidNumber }}</p>
                      </div>
                      <div class="form-group">
                        <label class="form-label">Date d'expiration ANID *</label>
                        <input v-model="formData.anidExpiryDate" 
                          type="date" 
                          class="form-input h-12"
                          :class="{ 'border-red-500': errors.anidExpiryDate }"
                          required>
                        <p v-if="errors.anidExpiryDate" class="mt-1 text-sm text-red-600">{{ errors.anidExpiryDate }}</p>
                      </div>
                    </div>
                    <FileUpload @file-uploaded="handleFileUpload('anidCard', $event)" 
                      accept="image/*"
                      :current-file="formData.documents.anidCard" 
                      :has-error="!!errors.anidCard"
                      label="Photo de la carte ANID (obligatoire)" />
                    <p v-if="errors.anidCard" class="mt-1 text-sm text-red-600">{{ errors.anidCard }}</p>
                  </div>
                </div>
              </template>

              <!-- Étape 3: Informations commerciales -->
              <template v-else-if="currentStep === 3">
                <div class="form-section">
                  <h2 class="section-title">
                    <i class="fas fa-store text-orange-500 mr-2"></i>
                    Informations sur l'Entreprise
                  </h2>

                  <div class="grid grid-cols-1 md:grid-cols-2 gap-4 sm:gap-6 mb-6">
                    <div class="form-group">
                      <label class="form-label">Nom commercial *</label>
                      <input v-model="formData.businessName" type="text" class="form-input h-12"
                        placeholder="Nom de votre commerce" required>
                    </div>

                    <div class="form-group">
                      <label class="form-label">Type d'activité *</label>
                      <select v-model="formData.businessType" class="form-input h-12" required>
                        <option value="">Sélectionnez</option>
                        <option value="boutique">Boutique générale</option>
                        <option value="pharmacie">Pharmacie</option>
                        <option value="station-service">Station service</option>
                        <option value="supermarche">Supermarché</option>
                        <option value="autre">Autre</option>
                      </select>
                    </div>

                    <div class="form-group md:col-span-2">
                      <label class="form-label">Adresse du commerce *</label>
                      <textarea v-model="formData.businessAddress" 
                        class="form-input min-h-[100px]" :class="{ 'border-red-500': errors.businessAddress }"
                        placeholder="Adresse où se situe votre commerce" required></textarea>
                      <p v-if="errors.businessAddress" class="mt-1 text-sm text-red-600">{{ errors.businessAddress }}</p>
                    </div>

                    <div class="form-group md:col-span-2">
                      <label class="form-label">Type d'utilisation *</label>
                      <select v-model="formData.usageType" class="form-input h-12" required>
                        <option value="">Sélectionnez le type d'utilisation</option>
                        <option value="TRADER">TRADER - Commerçant simple</option>
                        <option value="MERC">MERC - Marchand avec TPE</option>
                        <option value="TRADERWNIF">TRADERWNIF - Commerçant avec NIF</option>
                        <option value="CORP">CORP - Entreprise/Corporation</option>
                      </select>
                    </div>
                  </div>

                  <!-- Numéro de téléphone du commerce -->
                  <div class="mb-6">
                    <label class="form-label">Numéro marchand (optionnel)</label>
                    <PhoneInput v-model="formData.businessPhone" 
                      :class="{ 'border-red-500': errors.businessPhone }"
                      placeholder="Numéro de téléphone du commerce" />
                    <p v-if="errors.businessPhone" class="mt-1 text-sm text-red-600">
                      {{ errors.businessPhone }}
                    </p>
                  </div>

                  <!-- Cartes CFE et NIF -->
                  <div class="border-t pt-6">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-3 sm:gap-4 mb-3 sm:mb-4">
                      <label class="flex items-center space-x-2">
                        <input v-model="formData.hasCFE" type="checkbox"
                          class="w-4 h-4 text-orange-600 border-gray-300 rounded focus:ring-orange-500">
                        <span class="text-sm font-medium text-gray-700">Je possède une carte CFE</span>
                      </label>

                      <label class="flex items-center space-x-2">
                        <input v-model="formData.hasNIF" type="checkbox"
                          class="w-4 h-4 text-orange-600 border-gray-300 rounded focus:ring-orange-500">
                        <span class="text-sm font-medium text-gray-700">Je possède un numéro NIF</span>
                      </label>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4 sm:gap-6">
                      <div v-if="formData.hasCFE" class="form-group">
                        <label class="form-label">Numéro CFE</label>
                        <input v-model="formData.cfeNumber" type="text" class="form-input h-12"
                          placeholder="Numéro CFE">
                      </div>

                      <div v-if="formData.hasCFE" class="form-group">
                        <label class="form-label">Date d'expiration CFE</label>
                        <input v-model="formData.cfeExpiryDate" type="date" class="form-input h-12">
                      </div>

                      <div v-if="formData.hasNIF" class="form-group">
                        <label class="form-label">Numéro NIF</label>
                        <input v-model="formData.nifNumber" type="text" class="form-input h-12"
                          placeholder="Numéro NIF">
                      </div>
                    </div>

                    <!-- Upload CFE Card -->
                    <div v-if="formData.hasCFE" class="mt-6">
                      <FileUpload @file-uploaded="handleFileUpload('cfeCard', $event)" accept="image/*"
                        :current-file="formData.documents.cfeCard" label="Photo de la carte CFE" />
                    </div>
                  </div>

                  <!-- Section Commercial -->
                  <div class="border-t pt-6 mt-6">
                    <h3 class="text-base font-semibold mb-4 flex items-center">
                      <i class="fas fa-user-tie text-orange-500 mr-2"></i>
                      Informations du Commercial
                    </h3>
                    
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4 sm:gap-6">
                      <div class="form-group">
                        <label class="form-label">
                          Nom du commercial <span v-if="isCommercial">*</span>
                        </label>
                        <input v-model="formData.commercialLastName" type="text" 
                          class="form-input h-12" :class="{ 'border-red-500': errors.commercialLastName }"
                          placeholder="Nom" :required="isCommercial" :disabled="isCommercial"
                          :title="isCommercial ? 'Pré-rempli avec le nom du commercial connecté' : ''">
                        <p v-if="errors.commercialLastName" class="mt-1 text-sm text-red-600">{{ errors.commercialLastName }}</p>
                      </div>

                      <div class="form-group">
                        <label class="form-label">
                          Prénoms du commercial <span v-if="isCommercial">*</span>
                        </label>
                        <input v-model="formData.commercialFirstName" type="text" 
                          class="form-input h-12" :class="{ 'border-red-500': errors.commercialFirstName }"
                          placeholder="Prénoms" :required="isCommercial" :disabled="isCommercial"
                          :title="isCommercial ? 'Pré-rempli avec le prénom du commercial connecté' : ''">
                        <p v-if="errors.commercialFirstName" class="mt-1 text-sm text-red-600">{{ errors.commercialFirstName }}</p>
                      </div>

                      <div class="form-group">
                        <label class="form-label">
                          Téléphone du commercial <span v-if="isCommercial">*</span>
                        </label>
                        <PhoneInput v-model="formData.commercialPhone" 
                          :class="{ 'border-red-500': errors.commercialPhone }"
                          :required="isCommercial" :disabled="isCommercial"
                          :title="isCommercial ? 'Pré-rempli avec le téléphone du commercial connecté' : ''" />
                        <p v-if="errors.commercialPhone" class="mt-1 text-sm text-red-600">{{ errors.commercialPhone }}</p>
                      </div>
                    </div>
                  </div>
                </div>
              </template>

              <!-- Étape 4: Localisation -->
              <template v-else-if="currentStep === 4">
                <div class="form-section">
                  <h2 class="section-title">
                    <i class="fas fa-map-marker-alt text-orange-500 mr-2"></i>
                    Localisation de la boutique
                  </h2>

                  <div class="mb-6">
                    <LocationPicker @location-selected="handleLocationSelected" :initial-location="formData.location" />
                  </div>

                  <div class="form-group">
                    <label class="form-label">Description de l'emplacement</label>
                    <textarea v-model="formData.locationDescription" class="form-input min-h-[100px]"
                      placeholder="Décrivez précisément l'emplacement de votre boutique"></textarea>
                  </div>
                </div>
              </template>

              <!-- Étape 5: Signature -->
              <template v-else>
                <div class="form-section">
                  <h2 class="section-title">
                    <i class="fas fa-signature text-orange-500 mr-2"></i>
                    Signature électronique
                  </h2>

                  <div class="mb-6">
                    <SignaturePad @signature-saved="handleSignatureSaved" :current-signature="formData.signature" />
                  </div>

                  <div class="form-group">
                    <label class="flex items-center space-x-2">
                      <input v-model="formData.acceptTerms" type="checkbox"
                        class="w-4 h-4 text-orange-600 border-gray-300 rounded focus:ring-orange-500" required>
                      <span class="text-sm text-gray-700">
                        J'accepte les <a href="#" class="text-orange-600 underline">termes et conditions</a>
                        de Moov Money et certifie l'exactitude des informations fournies.
                      </span>
                    </label>
                  </div>
                </div>
              </template>
            </div>
          </Transition>
        </div>

        <!-- Boutons de navigation (desktop uniquement) -->
        <div class="hidden space-x-4 sm:flex justify-between items-center pt-6 border-t border-gray-100">
          <button v-if="currentStep > 1" @click="prevStep" type="button"
            class="h-12 w-1/2 px-5 rounded-xl border font-medium btn-secondary">
            <i class="fas fa-arrow-left mr-2"></i>
            Précédent
          </button>

          <div class="ml-auto flex w-1/2">
            <button v-if="currentStep < totalSteps" @click="nextStep" type="button"
              class="h-12 px-6 rounded-xl text-white font-semibold bg-[#EC6707] btn-primary">
              Suivant
              <i class="fas fa-arrow-right ml-2"></i>
            </button>

            <button v-if="currentStep === totalSteps" type="submit"
              class="h-12 px-6 rounded-xl text-white font-semibold bg-[#EC6707] btn-primary" :disabled="isSubmitting">
              <i v-if="isSubmitting" class="fas fa-spinner fa-spin mr-2"></i>
              <i v-else class="fas fa-paper-plane mr-2"></i>
              {{ isSubmitting ? 'Envoi...' : 'Envoyer la demande' }}
            </button>
          </div>
        </div>
      </form>

      <!-- Indicateur de sauvegarde automatique -->
      <div v-if="autoSaveStatus" class="fixed right-4 bottom-20 sm:bottom-4 bg-white rounded-lg shadow-lg p-3 z-50">
        <div class="flex items-center space-x-2 text-sm">
          <i class="fas fa-save text-green-500"></i>
        </div>
      </div>
    </div>

    <!-- Barre sticky mobile -->
    <nav class="sm:hidden fixed inset-x-0 bottom-0 z-40 bg-white/95 backdrop-blur border-t">
      <div class="max-w-4xl mx-auto px-4 py-3 flex items-center gap-3 pb-[max(env(safe-area-inset-bottom),8px)]">
        <button class="h-12 rounded-xl border flex-1 font-medium disabled:opacity-50" :disabled="currentStep === 1"
          type="button" @click="prevStep">
          <i class="fas fa-arrow-left mr-2"></i>Précédent
        </button>

        <button v-if="currentStep < totalSteps" class="h-12 rounded-xl bg-[#EC6707] text-white flex-1 font-semibold"
          type="button" @click="nextStep">
          Suivant<i class="fas fa-arrow-right ml-2"></i>
        </button>

        <button v-else class="h-12 rounded-xl bg-[#EC6707] text-white flex-1 font-semibold disabled:opacity-50"
          type="button" :disabled="isSubmitting" @click="submitForm">
          <i v-if="isSubmitting" class="fas fa-spinner fa-spin mr-2"></i>
          <i v-else class="fas fa-paper-plane mr-2"></i>
          {{ isSubmitting ? 'Envoi...' : 'Envoyer' }}
        </button>
      </div>
    </nav>
  </div>
</template>

<script>
import { ref, computed, watch, onMounted, nextTick, onBeforeUnmount } from 'vue'
import { useRouter } from 'vue-router'
import { useMerchantStore } from '../stores/merchant'
import { useAuthStore } from '../stores/auth'
import { useNotificationStore } from '../stores/notifications'
import FileUpload from '../components/FileUpload.vue'
import LocationPicker from '../components/LocationPicker.vue'
import SignaturePad from '../components/SignaturePad.vue'
import PhoneInput from '../components/PhoneInput.vue'
import IdNumberInput from '../components/IdNumberInput.vue'

export default {
  name: 'MerchantForm',
  components: {
    FileUpload,
    LocationPicker,
    SignaturePad,
    PhoneInput,
    IdNumberInput
  },
  setup() {
    // Services et Stores
    const router = useRouter();
    const merchantStore = useMerchantStore();
    const authStore = useAuthStore();
    const notificationStore = useNotificationStore();

    // État de l'authentification
    const isCommercial = computed(() => authStore.isCommercial);
    const userInfo = computed(() => authStore.user);
    
    // États du formulaire
    const currentStep = ref(1);
    const totalSteps = ref(5);
    const isSubmitting = ref(false);
    const autoSaveStatus = ref('');
    const direction = ref(1);
    const errors = ref({});

    // État de la mise en page
    const stage = ref(null);
    const stageHeight = ref('auto');

    // Create a default form data structure
    const defaultFormData = {
      // Informations personnelles du représentant
      lastName: '',
      firstName: '',
      birthDate: '',
      birthPlace: '',
      gender: '',
      nationality: '',
      personalPhone: '',
      email: '',
      address: '',
      // Documents d'identité
      idType: '',
      idNumber: '',
      idExpiryDate: '',
      hasAnidCard: false,
      anidNumber: '',
      anidExpiryDate: '',
      // Informations commerciales
      businessName: '',
      businessType: '',
      businessAddress: '',
      usageType: '',
      businessPhone: '',
      hasCFE: false,
      cfeNumber: '',
      cfeExpiryDate: '',
      hasNIF: false,
      nifNumber: '',
      // Informations du commercial
      commercialLastName: '',
      commercialFirstName: '',
      commercialPhone: '',
      // Localisation
      location: null,
      locationDescription: '',
      // Signature et acceptation
      signature: null,
      acceptTerms: false,
      // Documents
      documents: {
        idCard: null,
        anidCard: null,
        cfeCard: null
      }
    };

    const setStageHeightToCurrent = async () => {
      await nextTick()
      if (!stage.value) return
      const child = stage.value.firstElementChild
      if (child) {
        // Set to auto first to get the real height with error messages
        stageHeight.value = 'auto'
        await nextTick()
        // Then get the actual height
        const height = child.offsetHeight
        stageHeight.value = height + 'px'
      }
    }    // Données du formulaire
    const formData = ref({
      // Informations personnelles du représentant
      lastName: '',
      firstName: '',
      birthDate: '',
      birthPlace: '',
      gender: '',
      nationality: '',
      personalPhone: '',
      email: '',
      address: '',

      // Documents d'identité
      idType: '',
      idNumber: '',
      idExpiryDate: '',
      hasAnidCard: false,
      anidNumber: '',
      anidExpiryDate: '',

      // Informations commerciales
      businessName: '',
      businessType: '',
      businessAddress: '',
      usageType: '',
      businessPhone: '',
      hasCFE: false,
      cfeNumber: '',
      cfeExpiryDate: '',
      hasNIF: false,
      nifNumber: '',

      // Informations du commercial
      commercialLastName: '',
      commercialFirstName: '',
      commercialPhone: '',

      // Localisation
      location: null,
      locationDescription: '',

      // Signature et acceptation
      signature: null,
      acceptTerms: false,

      // Documents
      documents: {
        idCard: null,
        anidCard: null,
        cfeCard: null
      }
    })

    // Progression calculée
    const progress = computed(() => (currentStep.value / totalSteps.value) * 100)

    // Les téléphones du commerce ne sont plus gérés en liste

    // Validation functions
    const validateCommercialInfo = () => {
      const validationErrors = {}
      
      if (isCommercial.value) {
        // Commercial fields are required for commercial users
        if (!formData.value.commercialLastName) {
          validationErrors.commercialLastName = 'Le nom du commercial est requis'
        }
        if (!formData.value.commercialFirstName) {
          validationErrors.commercialFirstName = 'Le prénom du commercial est requis'
        }
        if (!formData.value.commercialPhone) {
          validationErrors.commercialPhone = 'Le téléphone du commercial est requis'
        }
      }
      
      return validationErrors
    }

    const validateEmail = (email) => {
      if (!email) return true // Optional field
      const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/
      return emailRegex.test(email)
    }

    const validateBirthDate = (date) => {
      if (!date) return false
      const birthDate = new Date(date)
      const today = new Date()
      const minDate = new Date()
      minDate.setFullYear(today.getFullYear() - 18)
      return birthDate <= minDate
    }

    const validateIdExpiryDate = (date) => {
      if (!date) return false
      const expiryDate = new Date(date)
      const today = new Date()
      return expiryDate > today
    }

    const validateStep = (step) => {
      errors.value = {}
      
      // Validate commercial info if user is commercial
      if (step === 1 && isCommercial.value) {
        const commercialErrors = validateCommercialInfo()
        Object.assign(errors.value, commercialErrors)
      }
      
      if (step === 1) {
        // Validate personal information
        if (!formData.value.lastName) errors.value.lastName = 'Champ obligatoire'
        if (!formData.value.firstName) errors.value.firstName = 'Champ obligatoire'
        if (!formData.value.birthDate) errors.value.birthDate = 'Champ obligatoire'
        else if (!validateBirthDate(formData.value.birthDate)) errors.value.birthDate = 'Vous devez avoir au moins 18 ans'
        if (!formData.value.birthPlace) errors.value.birthPlace = 'Champ obligatoire'
        if (!formData.value.gender) errors.value.gender = 'Champ obligatoire'
        if (!formData.value.nationality) errors.value.nationality = 'Champ obligatoire'
        if (!formData.value.personalPhone) errors.value.personalPhone = 'Champ obligatoire'
        if (formData.value.email && !validateEmail(formData.value.email)) errors.value.email = 'Format email invalide'
        if (!formData.value.address) errors.value.address = 'Champ obligatoire'
      }
      else if (step === 2) {
        // Validate ID documents
        if (formData.value.hasAnidCard) {
          // Si l'utilisateur a une carte ANID, valider les champs ANID
          if (!formData.value.anidNumber) errors.value.anidNumber = 'Champ obligatoire'
          if (!formData.value.anidExpiryDate) errors.value.anidExpiryDate = 'Champ obligatoire'
          else if (!validateIdExpiryDate(formData.value.anidExpiryDate)) errors.value.anidExpiryDate = 'Date invalide'
          if (!formData.value.documents.anidCard) errors.value.anidCard = 'Champ obligatoire'
        } else {
          // Sinon, valider les champs de pièce d'identité standard
          if (!formData.value.idType) errors.value.idType = 'Champ obligatoire'
          if (!formData.value.idNumber) errors.value.idNumber = 'Champ obligatoire'
          if (!formData.value.idExpiryDate) errors.value.idExpiryDate = 'Champ obligatoire'
          else if (!validateIdExpiryDate(formData.value.idExpiryDate)) errors.value.idExpiryDate = 'Date invalide'
          if (!formData.value.documents.idCard) errors.value.idCard = 'Champ obligatoire'
        }
      }
      else if (step === 3) {
        // Validate business information
        if (!formData.value.businessName) errors.value.businessName = 'Champ obligatoire'
        if (!formData.value.businessType) errors.value.businessType = 'Champ obligatoire'
        if (!formData.value.businessAddress) errors.value.businessAddress = 'Champ obligatoire'
        if (!formData.value.usageType) errors.value.usageType = 'Champ obligatoire'
        // businessPhone is now optional, no validation needed
        if (!formData.value.commercialLastName) errors.value.commercialLastName = 'Champ obligatoire'
        if (!formData.value.commercialFirstName) errors.value.commercialFirstName = 'Champ obligatoire'
        if (!formData.value.commercialPhone) errors.value.commercialPhone = 'Champ obligatoire'
      }

      return Object.keys(errors.value).length === 0
    }

    // Navigation entre étapes
    const nextStep = async () => {
      if (currentStep.value < totalSteps.value) {
        if (validateStep(currentStep.value)) {
          direction.value = 1
          currentStep.value++
          setStageHeightToCurrent()
        } else {
          // Afficher une notification pour les erreurs de validation
          const errorCount = Object.keys(errors.value).length
          notificationStore.warning(
            'Informations manquantes',
            `Veuillez remplir ${errorCount === 1 ? 'le champ obligatoire' : `les ${errorCount} champs obligatoires`} pour continuer`
          )
        }
      }
    }

    const prevStep = async () => {
      if (currentStep.value > 1) {
        direction.value = -1
        currentStep.value--
        setStageHeightToCurrent()
      }
    }

    // Gestion des fichiers
    const handleFileUpload = (type, file) => {
      if (file instanceof File || file instanceof Blob) {
        // Si c'est un nouveau fichier, créer un objet avec les métadonnées ET l'aperçu
        const fileData = {
          name: file.name,
          type: file.type,
          size: file.size,
          lastModified: file.lastModified,
          file: file // Garder une référence au fichier pour l'upload
        }
        
        // Créer une URL de données pour l'aperçu si c'est une image
        if (file.type.startsWith('image/')) {
          const reader = new FileReader()
          reader.onload = (e) => {
            fileData.dataUrl = e.target.result
            formData.value.documents[type] = fileData
            autoSave()
          }
          reader.readAsDataURL(file)
          // Ne pas appeler autoSave ici car le reader est asynchrone
          return
        } else {
          formData.value.documents[type] = fileData
        }
      } else {
        // Si null ou déjà un objet avec url/dataUrl
        formData.value.documents[type] = file
      }
      autoSave()
    }

    // Gestion de la localisation
    const handleLocationSelected = (location) => {
      formData.value.location = location
      autoSave()
    }

    // Gestion de la signature
    const handleSignatureSaved = (signature) => {
      formData.value.signature = signature
      autoSave()
    }

    // Sauvegarde automatique
    const autoSave = async () => {
      try {
        await merchantStore.saveFormData(formData.value)
        autoSaveStatus.value = 'Sauvegardé automatiquement'
        setTimeout(() => {
          autoSaveStatus.value = ''
        }, 2000)
      } catch (error) {
        console.error('Erreur sauvegarde automatique:', error)
        notificationStore.warning(
          'Sauvegarde automatique échouée',
          'Vos données seront sauvegardées à la prochaine action',
          { duration: 3000 }
        )
      }
    }

    // Soumission du formulaire
    const submitForm = async () => {
      // Validate final step before submission
      if (!validateStep(currentStep.value)) {
        notificationStore.error(
          'Erreur de validation',
          'Veuillez corriger les erreurs avant de continuer'
        )
        return
      }

      isSubmitting.value = true
      try {
        await merchantStore.submitApplication(formData.value)
        notificationStore.success(
          'Candidature envoyée !',
          'Votre demande a été soumise avec succès'
        )
        router.push('/success')
      } catch (error) {
        console.error('Erreur lors de l\'envoi:', error)
        
        // Handle 422 validation errors specifically
        if (error.response && error.response.status === 422) {
          const validationErrors = error.response.data.errors
          if (validationErrors) {
            // Extract the first validation error message
            const firstError = Object.values(validationErrors)[0]
            const errorMessage = Array.isArray(firstError) ? firstError[0] : firstError
            
            notificationStore.error(
              'Erreur de validation',
              errorMessage || error.response.data.message || 'Veuillez corriger les données saisies'
            )
            return
          }
        }
        
        // Handle other types of errors
        notificationStore.error(
          'Erreur lors de l\'envoi',
          error.message || error.response?.data?.message || 'Une erreur inattendue s\'est produite'
        )
      } finally {
        isSubmitting.value = false
      }
    }

    // Hooks de transition
    const beforeEnter = () => {
      if (stage.value) {
        stageHeight.value = 'auto'
        nextTick(() => {
          stageHeight.value = stage.value.offsetHeight + 'px'
        })
      }
    }
    const enter = (el, done) => {
      requestAnimationFrame(() => {
        stageHeight.value = 'auto'
        nextTick(() => {
          const targetH = el.offsetHeight
          stageHeight.value = targetH + 'px'
          setTimeout(done, 300)
        })
      })
    }
    const afterEnter = () => {
      stageHeight.value = 'auto'
    }
    const beforeLeave = () => {
      if (stage.value) {
        stageHeight.value = stage.value.offsetHeight + 'px'
      }
    }
    const leave = (el, done) => {
      requestAnimationFrame(() => {
        stageHeight.value = 'auto'
        nextTick(() => {
          setTimeout(done, 200)
        })
      })
    }
    
    // Watch for errors to trigger height recalculation
    watch(errors, () => {
      nextTick(() => setStageHeightToCurrent())
    }, { deep: true })

    // Sauvegarde automatique lors des changements
    watch(formData, autoSave, { deep: true })

    // Initialisation du formulaire
    onMounted(async () => {
      try {
        await setStageHeightToCurrent();
        
        // Charger les données sauvegardées
        const savedData = await merchantStore.loadFormData();
        
        // Initialiser avec les données par défaut
        formData.value = { ...defaultFormData };
        
        // Remplir le formulaire pour un commercial connecté
        if (isCommercial.value && userInfo.value) {
          const commercialData = {
            commercialLastName: userInfo.value.last_name || '',
            commercialFirstName: userInfo.value.first_name || '',
            commercialPhone: userInfo.value.phone || '',
            businessName: userInfo.value.business_name || '',
            businessType: userInfo.value.business_type || '',
            businessPhone: (userInfo.value.business_phones && userInfo.value.business_phones[0]) || '',
            businessEmail: userInfo.value.email || '',
            businessAddress: userInfo.value.business_address || ''
          };
          
          // Fusionner dans l'ordre : défaut -> commercial -> sauvegardé
          formData.value = {
            ...formData.value,
            ...commercialData,
            ...(savedData || {})
          };
            // Valider après le remplissage des données
            nextTick(() => {
            validateCommercialInfo();
            });
          } 
          // Si données sauvegardées mais pas de commercial
          else if (savedData) {
            formData.value = { ...formData.value, ...savedData };
          }
          
          // Configuration de la mise en page
          await setStageHeightToCurrent();
          window.addEventListener('resize', setStageHeightToCurrent);
          } catch (error) {
          console.error('Erreur lors de l\'initialisation du formulaire:', error);
          }
        })
        onBeforeUnmount(() => {
          window.removeEventListener('resize', setStageHeightToCurrent)
        })

    return {
      currentStep,
      totalSteps,
      isSubmitting,
      autoSaveStatus,
      formData,
      progress,
      direction,
      stage,
      stageHeight,
      errors,
      nextStep,
      prevStep,
      handleFileUpload,
      handleLocationSelected,
      handleSignatureSaved,
      submitForm,
      beforeEnter,
      enter,
      afterEnter,
      beforeLeave,
      leave,
      // Add commercial properties
      isCommercial,
      userInfo
    }
  }
}
</script>

<style scoped>
/* Progress */
.progress-bar {
  width: 100%;
  height: 8px;
  background: #e5e7eb;
  border-radius: 9999px;
  overflow: hidden;
}

.progress-fill {
  height: 100%;
  background: linear-gradient(90deg, #FF8C42 0%, #FF6B35 100%);
  transition: width 0.3s ease;
  border-radius: 9999px;
}

/* Inputs & Selects */
.form-input {
  @apply w-full rounded-xl border px-4 py-2 placeholder:text-gray-400 focus:outline-none focus:ring-2 focus:ring-orange-500/50;
}

.form-select {
  @apply form-input;
}

/* Typo & sections */
.section-title {
  @apply text-base sm:text-lg font-semibold mb-3 sm:mb-4;
}

.form-label {
  @apply block mb-1 text-sm font-medium text-gray-700;
}

.form-group {
  @apply space-y-1;
}

/* Boutons */
.btn-primary {
  @apply transition active:scale-[.99];
}

.btn-secondary {
  @apply bg-white text-gray-700 hover:bg-gray-50;
}

@media (max-width: 768px) {
  .circle-1 {
    width: 200px;
    height: 200px;
    border-radius: 9999px;
    background: radial-gradient(closest-side, #FFEDD5, transparent);
    top: -100px;
    left: -100px;
  }

  .circle-2 {
    width: 150px;
    height: 150px;
    border-radius: 9999px;
    background: radial-gradient(closest-side, #FFE4E6, transparent);
    top: 300px;
    right: -75px;
  }

  .circle-3 {
    width: 100px;
    height: 100px;
    border-radius: 9999px;
    background: radial-gradient(closest-side, #FEF9C3, transparent);
    bottom: 200px;
    left: -50px;
  }
}

@media (max-width: 767px) {
  .form-card {
    width: 100% !important;
    max-width: none !important;
  }

  .stage {
    width: 100%;
  }

  .stage > div {
    width: 100%;
    min-width: 100%;
  }

  .form-input,
  .form-select {
    height: 3rem;
  }
}
</style>