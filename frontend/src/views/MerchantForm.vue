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
      
      <!-- Skeleton Loader pour le mode édition -->
      <div v-if="isLoadingApplication" class="space-y-6">
        <div class="bg-white rounded-2xl shadow-lg p-4 sm:p-6">
          <div class="animate-pulse">
            <div class="h-4 bg-gray-200 rounded-full mb-4"></div>
            <div class="flex justify-between">
              <div class="h-3 bg-gray-200 rounded w-20"></div>
              <div class="h-3 bg-gray-200 rounded w-16"></div>
            </div>
          </div>
        </div>
        
        <div class="bg-white rounded-2xl shadow-lg p-4 sm:p-6 md:p-8">
          <div class="animate-pulse space-y-6">
            <div class="space-y-2">
              <div class="h-6 bg-gray-200 rounded w-3/4"></div>
              <div class="h-4 bg-gray-200 rounded w-1/2"></div>
            </div>
            
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
              <div class="space-y-3">
                <div class="h-4 bg-gray-200 rounded w-1/3"></div>
                <div class="h-12 bg-gray-200 rounded"></div>
              </div>
              <div class="space-y-3">
                <div class="h-4 bg-gray-200 rounded w-1/3"></div>
                <div class="h-12 bg-gray-200 rounded"></div>
              </div>
            </div>
            
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
              <div class="space-y-3">
                <div class="h-4 bg-gray-200 rounded w-1/2"></div>
                <div class="h-12 bg-gray-200 rounded"></div>
              </div>
              <div class="space-y-3">
                <div class="h-4 bg-gray-200 rounded w-1/3"></div>
                <div class="h-12 bg-gray-200 rounded"></div>
              </div>
            </div>
            
            <div class="flex justify-between mt-8">
              <div class="h-12 bg-gray-200 rounded w-20"></div>
              <div class="h-12 bg-gray-200 rounded w-24"></div>
            </div>
          </div>
        </div>
      </div>

      <!-- Formulaire normal -->
      <div v-else>
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
                    {{ isEditMode ? 'Modifier les Informations Personnelles' : 'Informations Personnelles du Représentant' }}
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
                      <div class="relative">
                        <input 
                          v-model="formData.nationality" 
                          type="text" 
                          class="form-input h-12" 
                          :class="{ 'border-red-500': errors.nationality }"
                          placeholder="Tapez ou sélectionnez votre nationalité"
                          @input="filterNationalities"
                          @focus="showNationalitySuggestions = true"
                          @blur="hideNationalitySuggestions"
                          autocomplete="off"
                          required
                        />
                        <!-- Liste des suggestions -->
                        <div 
                          v-if="showNationalitySuggestions && filteredNationalities.length > 0"
                          class="absolute z-10 w-full mt-1 bg-white border border-gray-300 rounded-lg shadow-lg max-h-48 overflow-y-auto"
                        >
                          <div 
                            v-for="nationality in filteredNationalities" 
                            :key="nationality"
                            class="px-4 py-2 cursor-pointer hover:bg-gray-100 transition-colors"
                            @mousedown="selectNationality(nationality)"
                          >
                            {{ nationality }}
                          </div>
                        </div>
                      </div>
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
                        <option value="driving_license">Permis de conduire</option>
                        <option value="foreign_id">Carte d'identité étrangère</option>
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
                  <div class="mb-4" v-show="false">
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
                          :maxlength="maxLengths.anidNumber"
                          @input="limitInput('anidNumber', $event)"
                          @keydown="handleKeydown('anidNumber', $event)"
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
                      <input v-model="formData.businessName" type="text" class="form-input h-12 uppercase"
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

                    <div class="form-group">
                      <label class="form-label">Région *</label>
                      <select v-model="formData.region" 
                        class="form-input h-12" :class="{ 'border-red-500': errors.region }" required>
                        <option value="">Sélectionnez votre région</option>
                        <option value="Maritime">Maritime</option>
                        <option value="Plateaux">Plateaux</option>
                        <option value="Centrale">Centrale</option>
                        <option value="Kara">Kara</option>
                        <option value="Savanes">Savanes</option>
                      </select>
                      <p v-if="errors.region" class="mt-1 text-sm text-red-600">{{ errors.region }}</p>
                    </div>

                    <div class="form-group">
                      <label class="form-label">Ville/Village *</label>
                      <div class="relative">
                        <input 
                          v-model="formData.city" 
                          type="text" 
                          class="form-input h-12" 
                          :class="{ 'border-red-500': errors.city }"
                          placeholder="Tapez votre ville ou village"
                          @input="filterCities"
                          @focus="showCitySuggestions = true"
                          @blur="hideCitySuggestions"
                          autocomplete="off"
                          required
                        />
                        <!-- Liste des suggestions -->
                        <div 
                          v-if="showCitySuggestions && filteredCities.length > 0"
                          class="absolute z-10 w-full mt-1 bg-white border border-gray-300 rounded-lg shadow-lg max-h-48 overflow-y-auto"
                        >
                          <div 
                            v-for="city in filteredCities" 
                            :key="city"
                            class="px-4 py-2 cursor-pointer hover:bg-gray-100 transition-colors"
                            @mousedown="selectCity(city)"
                          >
                            {{ city }}
                          </div>
                        </div>
                      </div>
                      <p v-if="errors.city" class="mt-1 text-sm text-red-600">{{ errors.city }}</p>
                    </div>

                    <div class="form-group md:col-span-2">
                      <label class="form-label">Type d'utilisation *</label>
                      <select v-model="formData.usageType" class="form-input h-12" required>
                        <option value="">Sélectionnez le type d'utilisation</option>
                        <option value="TRADER">TRADER - Commerçant simple</option>
                        <option value="MERC">MERC - Marchand avec TPE</option>
                        <option value="TRADERWNIF">TRADERWNIF - Commerçant sans NIF</option>
                        <option value="CORP">CORP - Entreprise/Corporation</option>
                      </select>
                    </div>
                  </div>

                  <!-- Numéro de téléphone du commerce -->
                  <div class="mb-6" v-show="false">
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
                        <label class="form-label">Numéro CFE *</label>
                        <input v-model="formData.cfeNumber" type="text" 
                          class="form-input h-12" :class="{ 'border-red-500': errors.cfeNumber }"
                          placeholder="Numéro CFE" 
                          :maxlength="maxLengths.cfeNumber"
                          @input="limitInput('cfeNumber', $event)"
                          @keydown="handleKeydown('cfeNumber', $event)"
                          required>
                        <p v-if="errors.cfeNumber" class="mt-1 text-sm text-red-600">{{ errors.cfeNumber }}</p>
                      </div>

                      <div v-if="formData.hasCFE" class="form-group">
                        <label class="form-label">Date d'expiration CFE *</label>
                        <input v-model="formData.cfeExpiryDate" type="date" 
                          class="form-input h-12" :class="{ 'border-red-500': errors.cfeExpiryDate }"
                          required>
                        <p v-if="errors.cfeExpiryDate" class="mt-1 text-sm text-red-600">{{ errors.cfeExpiryDate }}</p>
                      </div>

                      <div v-if="formData.hasNIF" class="form-group">
                        <label class="form-label">Numéro NIF *</label>
                        <input v-model="formData.nifNumber" type="text" 
                          class="form-input h-12" :class="{ 'border-red-500': errors.nifNumber }"
                          placeholder="Numéro NIF" 
                          :maxlength="maxLengths.nifNumber"
                          @input="limitInput('nifNumber', $event)"
                          @keydown="handleKeydown('nifNumber', $event)"
                          required>
                        <p v-if="errors.nifNumber" class="mt-1 text-sm text-red-600">{{ errors.nifNumber }}</p>
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
                    
                    <!-- Note d'information si les champs ne sont pas modifiables -->
                    <div v-if="isCommercialInfoDisabled" class="mb-4 p-3 bg-blue-50 border border-blue-200 rounded-lg">
                      <div class="flex items-start">
                        <i class="fas fa-info-circle text-blue-500 mr-2 mt-0.5"></i>
                        <div class="text-sm text-blue-700">
                          <strong>Information :</strong> Ces informations ne peuvent pas être modifiées car cette candidature est liée à un utilisateur commercial spécifique.
                        </div>
                      </div>
                    </div>
                    
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4 sm:gap-6">
                      <div class="form-group">
                        <label class="form-label">
                          Nom du commercial <span v-if="isCommercial">*</span>
                        </label>
                        <input v-model="formData.commercialLastName" type="text" 
                          class="form-input h-12" :class="{ 'border-red-500': errors.commercialLastName }"
                          placeholder="Nom" :required="isCommercial" :disabled="isCommercialInfoDisabled"
                          :title="isCommercialInfoDisabled ? 'Non modifiable - Candidature liée à un utilisateur' : ''">
                        <p v-if="errors.commercialLastName" class="mt-1 text-sm text-red-600">{{ errors.commercialLastName }}</p>
                      </div>

                      <div class="form-group">
                        <label class="form-label">
                          Prénoms du commercial <span v-if="isCommercial">*</span>
                        </label>
                        <input v-model="formData.commercialFirstName" type="text" 
                          class="form-input h-12" :class="{ 'border-red-500': errors.commercialFirstName }"
                          placeholder="Prénoms" :required="isCommercial" :disabled="isCommercialInfoDisabled"
                          :title="isCommercialInfoDisabled ? 'Non modifiable - Candidature liée à un utilisateur' : ''">
                        <p v-if="errors.commercialFirstName" class="mt-1 text-sm text-red-600">{{ errors.commercialFirstName }}</p>
                      </div>

                      <div class="form-group">
                        <label class="form-label">
                          Téléphone du commercial <span v-if="isCommercial">*</span>
                        </label>
                        <PhoneInput v-model="formData.commercialPhone" 
                          :class="{ 'border-red-500': errors.commercialPhone }"
                          :required="isCommercial" :disabled="isCommercialInfoDisabled"
                          :title="isCommercialInfoDisabled ? 'Non modifiable - Candidature liée à un utilisateur' : ''" />
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

      <!-- Bouton pour vider le formulaire (flottant) -->
      <div class="fixed left-4 bottom-32 sm:bottom-20 z-50">
        <button @click="clearForm" type="button"
          class="bg-red-600 hover:bg-red-700 text-white rounded-full hover:rounded-2xl shadow-lg hover:shadow-xl transition-all duration-500 ease-in-out group flex items-center justify-center overflow-hidden min-w-[3.5rem] h-14 hover:pl-4 hover:pr-6"
          title="Vider tous les champs du formulaire">
          <i class="fas fa-trash text-lg group-hover:scale-110 transition-all duration-500 ease-in-out flex-shrink-0 group-hover:mr-3"></i>
          <span class="whitespace-nowrap opacity-0 group-hover:opacity-100 transition-all duration-500 ease-in-out text-sm font-medium max-w-0 group-hover:max-w-[8rem] overflow-hidden">
            Vider tous les champs
          </span>
        </button>
      </div>

      <!-- Bouton flottant pour retourner aux détails (mode édition uniquement) -->
      <div v-if="isEditMode" class="fixed left-4 bottom-20 sm:bottom-4 z-50">
        <button @click="goToApplicationDetails" 
          class="bg-blue-600 hover:bg-blue-700 text-white rounded-full hover:rounded-2xl shadow-lg hover:shadow-xl transition-all duration-500 ease-in-out group flex items-center justify-center overflow-hidden min-w-[3.5rem] h-14 hover:pl-4 hover:pr-6"
          title="Retourner aux détails de la candidature">
          <i class="fas fa-eye text-lg group-hover:scale-110 transition-all duration-500 ease-in-out flex-shrink-0 group-hover:mr-3"></i>
          <span class="whitespace-nowrap opacity-0 group-hover:opacity-100 transition-all duration-500 ease-in-out text-sm font-medium max-w-0 group-hover:max-w-[8rem] overflow-hidden">
            Retourner sur la fiche
          </span>
        </button>
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
    
    </div> <!-- Fin du formulaire normal -->
  </div>
</template>

<script>
import { ref, computed, watch, onMounted, nextTick, onBeforeUnmount } from 'vue'
import { useRouter, useRoute } from 'vue-router'
import { useMerchantStore } from '../stores/merchant'
import { useAuthStore } from '../stores/auth'
import { useNotificationStore } from '../stores/notifications'
import MerchantService from '../services/MerchantService'
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
    const route = useRoute();
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

    // États pour l'édition
    const isEditMode = computed(() => !!route.params.id);
    const applicationId = computed(() => route.params.id);
    const isLoadingApplication = ref(false);
    const loadingError = ref(null);
    
    // ID de l'utilisateur lié à la candidature (pour les candidatures créées par un commercial)
    const applicationUserId = ref(null);
    
    // Détermine si les informations commerciales doivent être désactivées
    const isCommercialInfoDisabled = computed(() => {
      return isEditMode.value && applicationUserId.value !== null;
    });

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
      region: '',
      city: '',
      businessEmail: '',
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

    // Fonctions de limitation de longueur pour les numéros
    const maxLengths = {
      anidNumber: 15,  // ANID peut avoir jusqu'à 15 caractères
      cfeNumber: 20,   // CFE peut avoir jusqu'à 20 caractères 
      nifNumber: 15    // NIF peut avoir jusqu'à 15 caractères
    };

    const limitInput = (fieldName, event) => {
      const input = event.target;
      const maxLength = maxLengths[fieldName];
      if (input.value.length > maxLength) {
        input.value = input.value.slice(0, maxLength);
        formData.value[fieldName] = input.value;
      }
    };

    const handleKeydown = (fieldName, event) => {
      const input = event.target;
      const maxLength = maxLengths[fieldName];
      const allowedKeys = ['Backspace', 'Delete', 'ArrowLeft', 'ArrowRight', 'ArrowUp', 'ArrowDown', 'Tab', 'Enter'];
      
      // If max length is reached and the key is not a control key, prevent default
      if (input.value.length >= maxLength && !allowedKeys.includes(event.key) && !event.ctrlKey && !event.metaKey) {
        event.preventDefault();
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
    }

    // Fonction pour charger les données d'une candidature existante
    const loadApplicationData = async (id) => {
      if (!id) return;
      
      isLoadingApplication.value = true;
      loadingError.value = null;
      
      try {
        const response = await MerchantService.getApplicationForEdit(id);
        
        // La réponse peut avoir différentes structures selon l'API
        const data = response.data || response;
        
        if (data) {
          // Pré-remplir les données du formulaire
          Object.keys(defaultFormData).forEach(key => {
            if (data[key] !== undefined && data[key] !== null) {
              formData.value[key] = data[key];
            }
          });
          
          // Gérer les champs spécifiques de l'API
          if (data.last_name) formData.value.lastName = data.last_name;
          if (data.first_name) formData.value.firstName = data.first_name;
          if (data.birth_date) formData.value.birthDate = data.birth_date;
          if (data.birth_place) formData.value.birthPlace = data.birth_place;
          if (data.gender) formData.value.gender = data.gender;
          if (data.nationality) formData.value.nationality = data.nationality;
          // Le téléphone personnel est dans le champ 'phone' pas 'personal_phone'
          if (data.phone) formData.value.personalPhone = data.phone;
          if (data.email) formData.value.email = data.email;
          if (data.address) formData.value.address = data.address;
          if (data.region) formData.value.region = data.region;
          if (data.id_type) formData.value.idType = data.id_type;
          if (data.id_number) formData.value.idNumber = data.id_number;
          if (data.id_expiry_date) formData.value.idExpiryDate = data.id_expiry_date;
          if (data.has_anid_card !== undefined) formData.value.hasAnidCard = data.has_anid_card;
          if (data.anid_number) formData.value.anidNumber = data.anid_number;
          if (data.anid_expiry_date) formData.value.anidExpiryDate = data.anid_expiry_date;
          if (data.business_name) formData.value.businessName = data.business_name;
          if (data.business_type) formData.value.businessType = data.business_type;
          if (data.business_address) formData.value.businessAddress = data.business_address;
          if (data.city) formData.value.city = data.city;
          if (data.business_email) formData.value.businessEmail = data.business_email;
          if (data.usage_type) formData.value.usageType = data.usage_type;
          // Le téléphone business peut être dans merchant_phone selon les données
          if (data.merchant_phone) formData.value.businessPhone = data.merchant_phone;
          if (data.has_cfe !== undefined) formData.value.hasCFE = data.has_cfe;
          if (data.cfe_number) formData.value.cfeNumber = data.cfe_number;
          if (data.cfe_expiry_date) formData.value.cfeExpiryDate = data.cfe_expiry_date;
          if (data.has_nif !== undefined) formData.value.hasNIF = data.has_nif;
          if (data.nif_number) formData.value.nifNumber = data.nif_number;
          
          // Stocker l'ID utilisateur pour vérifier si les infos commerciales doivent être désactivées
          if (data.user_id) {
            applicationUserId.value = data.user_id;
          }
          
          // Gérer les informations commerciales depuis l'objet 'commercial'
          if (data.commercial) {
            if (data.commercial.last_name) formData.value.commercialLastName = data.commercial.last_name;
            if (data.commercial.first_name) formData.value.commercialFirstName = data.commercial.first_name;
            if (data.commercial.phone) formData.value.commercialPhone = data.commercial.phone;
          }
          
          if (data.location_description) formData.value.locationDescription = data.location_description;
          if (data.accept_terms !== undefined) formData.value.acceptTerms = data.accept_terms;
          
          // Gérer la localisation
          console.log('🗺️ Location data from API:', { 
            latitude: data.latitude, 
            longitude: data.longitude,
            type_lat: typeof data.latitude,
            type_lng: typeof data.longitude
          });
          if (data.latitude && data.longitude) {
            formData.value.location = {
              lat: parseFloat(data.latitude),
              lng: parseFloat(data.longitude)
            };
            console.log('✅ Location set in formData:', formData.value.location);
          } else {
            console.log('❌ Missing latitude or longitude in API response');
          }
          
          // Gérer la signature
          if (data.signature) {
            formData.value.signature = data.signature;
          }
          
          // Gérer les documents depuis le tableau 'documents'
          if (data.documents && Array.isArray(data.documents)) {
            data.documents.forEach(doc => {
              // Construire l'URL complète du document
              let documentUrl = doc.url || doc.file_path;
              // Si l'URL est relative, préfixer avec l'URL du backend
              if (documentUrl && documentUrl.startsWith('/storage/')) {
                const baseURL = import.meta.env.VITE_API_URL || 'http://localhost:8000/api';
                const backendURL = baseURL.replace('/api', ''); // Enlever /api pour avoir l'URL de base
                documentUrl = backendURL + documentUrl;
              }
              
              // Créer un objet document compatible avec FileUpload
              const documentObject = {
                name: doc.original_name || doc.file_name,
                type: doc.mime_type,
                size: doc.file_size,
                url: documentUrl,
                uploaded: true, // Marquer comme déjà uploadé
                id: doc.id
              };
              
              // Mapper les types de documents backend vers les clés frontend
              const documentTypeMapping = {
                'id_card': 'idCard',
                'anid_card': 'anidCard',
                'residence_card': 'residenceCard',
                'cfe_card': 'cfeCard',
                'business_license': 'businessDocument',
                'nif_document': 'nifDocument',
                'other': 'residenceProof'
              };
              
              const frontendKey = documentTypeMapping[doc.type];
              if (frontendKey) {
                formData.value.documents[frontendKey] = documentObject;
              }
            });
          }
          
          // logs retirés
        } else {
          throw new Error('Aucune donnée reçue');
        }
      } catch (error) {
        console.error('Error loading application:', error);
        loadingError.value = 'Erreur lors du chargement';
        notificationStore.addNotification({
          type: 'error',
          message: 'Erreur lors du chargement de la candidature: ' + (error.message || error)
        });
      } finally {
        isLoadingApplication.value = false;
      }
    };

    // Données du formulaire
    const formData = ref({ ...defaultFormData })

    // Variables pour l'autocomplétion des villes
    const cities = ref([
      // Région Maritime
      'Lomé', 'Aného', 'Vogan', 'Tsévié', 'Kpalimé', 'Tabligbo',
      // Région Plateaux  
      'Atakpamé', 'Kpalimé', 'Badou', 'Danyi', 'Agou', 'Kloto',
      // Région Centrale
      'Sokodé', 'Tchamba', 'Blitta', 'Sotouboua', 'Tchaoudjo',
      // Région Kara
      'Kara', 'Bassar', 'Niamtougou', 'Pagouda', 'Bafilo', 'Ketao',
      // Région Savanes
      'Dapaong', 'Mango', 'Gando', 'Kantè', 'Tandjoaré', 'Cinkassé'
    ])
    const filteredCities = ref([])
    const showCitySuggestions = ref(false)

    // Variables pour l'autocomplétion des nationalités
    const nationalities = ref([
      'Afghane', 'Albanaise', 'Algérienne', 'Allemande', 'Américaine', 'Andorrane', 'Angolaise', 'Antiguaise-et-Barbudienne',
      'Argentine', 'Arménienne', 'Australienne', 'Autrichienne', 'Azerbaïdjanaise', 'Bahaméenne', 'Bahreïnienne', 'Bangladaise',
      'Barbadienne', 'Belge', 'Bélizienne', 'Béninoise', 'Bhoutanaise', 'Biélorusse', 'Birmane', 'Bolivienne',
      'Bosnienne', 'Botswanaise', 'Brésilienne', 'Britannique', 'Brunéienne', 'Bulgare', 'Burkinabè', 'Burundaise',
      'Cambodgienne', 'Camerounaise', 'Canadienne', 'Cap-verdienne', 'Centrafricaine', 'Chilienne', 'Chinoise', 'Chypriote',
      'Colombienne', 'Comorienne', 'Congolaise (RDC)', 'Congolaise (République du Congo)', 'Costaricienne', 'Croate', 'Cubaine',
      'Danoise', 'Djiboutienne', 'Dominicaine', 'Dominiquaise', 'Égyptienne', 'Émirienne', 'Équatorienne', 'Érythréenne',
      'Espagnole', 'Estonienne', 'Éthiopienne', 'Fidjienne', 'Finlandaise', 'Française', 'Gabonaise', 'Gambienne',
      'Géorgienne', 'Ghanéenne', 'Grecque', 'Grenadienne', 'Guatémaltèque', 'Guinéenne', 'Bissau-guinéenne', 'Guyanienne',
      'Haïtienne', 'Hondurienne', 'Hongroise', 'Indienne', 'Indonésienne', 'Irakienne', 'Iranienne', 'Irlandaise',
      'Islandaise', 'Israélienne', 'Italienne', 'Ivoirienne', 'Jamaïcaine', 'Japonaise', 'Jordanienne', 'Kazakhe',
      'Kényane', 'Kirghize', 'Kiribatienne', 'Kosovare', 'Koweïtienne', 'Laotienne', 'Lesothane', 'Lettone',
      'Libanaise', 'Libérienne', 'Libyenne', 'Liechtensteinoise', 'Lituanienne', 'Luxembourgeoise', 'Macédonienne',
      'Malgache', 'Malaisienne', 'Malawite', 'Maldivienne', 'Malienne', 'Maltaise', 'Marocaine', 'Marshallaise',
      'Mauricienne', 'Mauritanienne', 'Mexicaine', 'Micronésienne', 'Moldave', 'Monégasque', 'Mongole', 'Monténégrine',
      'Mozambicaine', 'Namibienne', 'Nauruane', 'Népalaise', 'Néerlandaise', 'Néo-zélandaise', 'Nicaraguayenne',
      'Nigérienne', 'Nigériane', 'Nord-coréenne', 'Norvégienne', 'Omanaise', 'Ougandaise', 'Ouzbèke', 'Pakistanaise',
      'Palaosienne', 'Palestinienne', 'Panaméenne', 'Papouane-néo-guinéenne', 'Paraguayenne', 'Péruvienne', 'Philippine',
      'Polonaise', 'Portugaise', 'Qatarienne', 'Roumaine', 'Russe', 'Rwandaise', 'Saint-lucienne', 'Saint-marinaise',
      'Saint-vincentaise-et-grenadine', 'Salomonaise', 'Salvadorienne', 'Samoane', 'São-toméenne', 'Saoudienne',
      'Sénégalaise', 'Serbe', 'Seychelloise', 'Sierra-leonaise', 'Singapourienne', 'Slovaque', 'Slovène',
      'Somalienne', 'Soudanaise', 'Sud-africaine', 'Sud-coréenne', 'Sud-soudanaise', 'Suédoise', 'Suisse',
      'Surinamaise', 'Swazie', 'Syrienne', 'Tadjike', 'Tanzanienne', 'Tchadienne', 'Tchèque', 'Thaïlandaise',
      'Timoraise', 'Togolaise', 'Tongienne', 'Trinidadienne', 'Tunisienne', 'Turque', 'Turkmène', 'Tuvaluane',
      'Ukrainienne', 'Uruguayenne', 'Vanuatuane', 'Vaticane', 'Vénézuélienne', 'Vietnamienne', 'Yéménite',
      'Zambienne', 'Zimbabwéenne'
    ])
    const filteredNationalities = ref([])
    const showNationalitySuggestions = ref(false)

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
        // Debug: Log values during validation
        // logs retirés étape 3
        
        // Validate business information
        if (!formData.value.businessName) errors.value.businessName = 'Champ obligatoire'
        if (!formData.value.businessType) errors.value.businessType = 'Champ obligatoire'
        if (!formData.value.businessAddress) errors.value.businessAddress = 'Champ obligatoire'
        if (!formData.value.region) errors.value.region = 'Champ obligatoire'
        if (!formData.value.city) errors.value.city = 'Champ obligatoire'
        if (!formData.value.usageType) errors.value.usageType = 'Champ obligatoire'
        // businessPhone is now optional, no validation needed
        
        // Validate commercial info if user is commercial
        if (isCommercial.value) {
          const commercialErrors = validateCommercialInfo()
          Object.assign(errors.value, commercialErrors)
        }
        
        // Validation conditionnelle CFE
        if (formData.value.hasCFE) {
          if (!formData.value.cfeNumber) errors.value.cfeNumber = 'Numéro CFE obligatoire'
          if (!formData.value.cfeExpiryDate) errors.value.cfeExpiryDate = 'Date d\'expiration CFE obligatoire'
          else if (!validateIdExpiryDate(formData.value.cfeExpiryDate)) errors.value.cfeExpiryDate = 'Date d\'expiration CFE invalide'
        }
        
        // Validation conditionnelle NIF
        if (formData.value.hasNIF) {
          if (!formData.value.nifNumber) errors.value.nifNumber = 'Numéro NIF obligatoire'
        }
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

    // Gestion de l'autocomplétion des villes
    const filterCities = () => {
      const query = formData.value.city.toLowerCase().trim()
      if (query.length === 0) {
        filteredCities.value = cities.value.slice(0, 10) // Limite à 10 suggestions
      } else {
        filteredCities.value = cities.value
          .filter(city => city.toLowerCase().includes(query))
          .slice(0, 10) // Limite à 10 suggestions
      }
    }

    const selectCity = (city) => {
      formData.value.city = city
      showCitySuggestions.value = false
      autoSave()
    }

    const hideCitySuggestions = () => {
      // Délai pour permettre le clic sur une suggestion
      setTimeout(() => {
        showCitySuggestions.value = false
      }, 200)
    }

    // Gestion de l'autocomplétion des nationalités
    const filterNationalities = () => {
      const query = formData.value.nationality.toLowerCase().trim()
      if (query.length === 0) {
        filteredNationalities.value = nationalities.value.slice(0, 10) // Limite à 10 suggestions
      } else {
        filteredNationalities.value = nationalities.value
          .filter(nationality => nationality.toLowerCase().includes(query))
          .slice(0, 10) // Limite à 10 suggestions
      }
    }

    const selectNationality = (nationality) => {
      formData.value.nationality = nationality
      showNationalitySuggestions.value = false
      autoSave()
    }

    const hideNationalitySuggestions = () => {
      // Délai pour permettre le clic sur une suggestion
      setTimeout(() => {
        showNationalitySuggestions.value = false
      }, 200)
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
      const isValid = validateStep(currentStep.value)
      
      if (!isValid) {
        notificationStore.error(
          'Erreur de validation',
          'Veuillez corriger les erreurs avant de continuer'
        )
        return
      }

      isSubmitting.value = true
      try {
        // Préparer les données pour l'envoi
        const dataToSubmit = { ...formData.value }
        
        // Note: La transformation de location en latitude/longitude est faite dans MerchantService
        // Ne pas transformer ici pour éviter les doublons
        
        if (isEditMode.value) {
          // Mode édition : utiliser updateApplication
          await merchantStore.updateApplication(applicationId.value, dataToSubmit)
          notificationStore.success(
            'Candidature modifiée !',
            'Votre demande a été mise à jour avec succès'
          )
        } else {
          // Mode création : utiliser submitApplication
          await merchantStore.submitApplication(dataToSubmit)
          
          // Vider le formulaire après soumission réussie
          formData.value = { ...defaultFormData }
          currentStep.value = 1
          errors.value = {}
          await merchantStore.clearFormData()
          
          notificationStore.success(
            'Candidature envoyée !',
            'Votre demande a été soumise avec succès'
          )
        }
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

    // Fonction pour vider le formulaire
    const clearForm = () => {
      // Confirmer avant de vider
      if (confirm('Êtes-vous sûr de vouloir vider tous les champs du formulaire ? Cette action est irréversible.')) {
        // Réinitialiser avec les données par défaut
        formData.value = { ...defaultFormData };
        
        // Réinitialiser les erreurs
        errors.value = {};
        
        // Retourner à la première étape
        currentStep.value = 1;
        
        // Vider le localStorage
        merchantStore.clearFormData();
        
        // Afficher une notification
        notificationStore.info(
          'Formulaire vidé',
          'Tous les champs ont été réinitialisés'
        );
        
        // Recalculer la hauteur
        nextTick(() => setStageHeightToCurrent());
      }
    };

    // Fonction pour naviguer vers les détails de la candidature
    const goToApplicationDetails = () => {
      if (applicationId.value) {
        router.push(`/applications/${applicationId.value}`);
      }
    };

    // Sauvegarde automatique lors des changements
    watch(formData, autoSave, { deep: true })

    // Watcher pour vider les champs quand le type de pièce change
    watch(() => formData.value.idType, (newIdType, oldIdType) => {
      // Vider les champs seulement si le type a vraiment changé et qu'il y avait une valeur précédente
      if (oldIdType && newIdType !== oldIdType) {
        formData.value.idNumber = '';
        formData.value.idExpiryDate = '';
        // Vider aussi les erreurs liées à ces champs
        if (errors.value.idNumber) delete errors.value.idNumber;
        if (errors.value.idExpiryDate) delete errors.value.idExpiryDate;
        
        // Recalculer la hauteur au cas où les erreurs auraient été supprimées
        nextTick(() => setStageHeightToCurrent());
      }
    })

    // Initialisation du formulaire
    onMounted(async () => {
      try {
        await setStageHeightToCurrent();
        
        // Si on est en mode édition, charger les données de la candidature
        if (isEditMode.value) {
          await loadApplicationData(applicationId.value);
        } else {
          // Charger les données sauvegardées pour un nouveau formulaire
          const savedData = await merchantStore.loadFormData();
          
          // Initialiser avec les données par défaut
          formData.value = { ...defaultFormData };
        
          // Appliquer les données sauvegardées d'abord
          if (savedData) {
            formData.value = { ...formData.value, ...savedData };
          }
          
          // Remplir les champs commerciaux seulement s'ils sont vides et que c'est un commercial
          if (isCommercial.value && userInfo.value && !isEditMode.value) {
            // Pré-remplir seulement les champs vides
            if (!formData.value.commercialLastName) {
              formData.value.commercialLastName = userInfo.value.last_name || '';
            }
            if (!formData.value.commercialFirstName) {
              formData.value.commercialFirstName = userInfo.value.first_name || '';
            }
            if (!formData.value.commercialPhone) {
              formData.value.commercialPhone = userInfo.value.phone || '';
            }
          }
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
      filterCities,
      selectCity,
      hideCitySuggestions,
      cities,
      filteredCities,
      showCitySuggestions,
      filterNationalities,
      selectNationality,
      hideNationalitySuggestions,
      nationalities,
      filteredNationalities,
      showNationalitySuggestions,
      submitForm,
      beforeEnter,
      enter,
      afterEnter,
      beforeLeave,
      leave,
      // Add commercial properties
      isCommercial,
      userInfo,
      // Add edit mode properties
      isEditMode,
      applicationId,
      isLoadingApplication,
      loadingError,
      applicationUserId,
      isCommercialInfoDisabled,
      // Add clear form and navigation functions
      clearForm,
      goToApplicationDetails,
      // Add input limitation functions
      maxLengths,
      limitInput,
      handleKeydown
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