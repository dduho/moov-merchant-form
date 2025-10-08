<template>
  <div class="bg-gray-100 min-h-screen p-6">
    <!-- Content Container -->
    <div class="max-w-7xl mx-auto space-y-6">
      <!-- Header avec navigation -->
      <div class="bg-white rounded-2xl shadow-sm p-6">
        <template v-if="loading">
          <!-- Skeleton pour le header -->
          <div class="flex items-center justify-between">
            <div class="flex items-center space-x-4">
              <div class="flex items-center">
                <div class="w-6 h-6 bg-gray-200 rounded animate-pulse"></div>
                <div class="ml-2 w-32 h-4 bg-gray-200 rounded animate-pulse"></div>
              </div>
              <div class="h-6 w-px bg-gray-300"></div>
              <div class="w-48 h-8 bg-gray-200 rounded animate-pulse"></div>
            </div>
            <div class="flex items-center space-x-4">
              <div class="w-20 h-6 bg-gray-200 rounded-full animate-pulse"></div>
              <div class="flex space-x-3">
                <div class="w-24 h-10 bg-gray-200 rounded-xl animate-pulse"></div>
                <div class="w-28 h-10 bg-gray-200 rounded-xl animate-pulse"></div>
              </div>
            </div>
          </div>
        </template>

        <template v-else>
          <div class="flex items-center justify-between">
            <div class="flex items-center space-x-4">
              <router-link 
                to="/dashboard" 
                class="flex items-center text-gray-600 hover:text-orange-600 transition-colors">
                <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                </svg>
                <span class="ml-2">Retour au dashboard</span>
              </router-link>
              <div class="h-6 w-px bg-gray-300"></div>
              <h1 class="text-2xl font-bold text-gray-800">
                Candidature #{{ application?.reference_number }}
              </h1>
            </div>
            <div class="flex items-center space-x-4">
              <!-- Badge de statut amélioré -->
              <div class="flex items-center">
                <span :class="[
                  'inline-flex items-center px-2.5 py-1 rounded-full text-xs font-medium shadow-sm border whitespace-nowrap',
                  application?.status === 'pending' && 'bg-gradient-to-r from-yellow-50 to-amber-50 text-yellow-800 border-yellow-200',
                  application?.status === 'approved' && 'bg-gradient-to-r from-green-50 to-emerald-50 text-green-800 border-green-200',
                  application?.status === 'rejected' && 'bg-gradient-to-r from-red-50 to-rose-50 text-red-800 border-red-200'
                ]">
                  <!-- Icône de statut -->
                  <svg v-if="application?.status === 'pending'" class="w-3 h-3 mr-1.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                  </svg>
                  <svg v-else-if="application?.status === 'approved'" class="w-3 h-3 mr-1.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                  </svg>
                  <svg v-else-if="application?.status === 'rejected'" class="w-3 h-3 mr-1.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z" />
                  </svg>
                  {{ getStatusLabel(application?.status) }}
                </span>
              </div>
              
              <!-- Actions selon les permissions -->
              <div class="flex items-center space-x-3">
                <!-- Bouton Modifier (Admin et Commercial) -->
                <button 
                  v-if="canEdit"
                  @click="editApplication"
                  class="inline-flex items-center px-4 py-2.5 border border-orange-300 text-orange-700 bg-orange-50 rounded-xl hover:bg-orange-100 hover:border-orange-400 focus:outline-none focus:ring-2 focus:ring-orange-500 focus:ring-offset-2 transition-all duration-200 font-medium shadow-sm">
                  <svg class="w-4 h-4 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                  </svg>
                  Modifier
                </button>

                <!-- Bouton Supprimer (Admin seulement) -->
                <button 
                  v-if="canDelete"
                  @click="confirmDelete"
                  class="inline-flex items-center px-4 py-2.5 border border-red-300 text-red-700 bg-red-50 rounded-xl hover:bg-red-100 hover:border-red-400 focus:outline-none focus:ring-2 focus:ring-red-500 focus:ring-offset-2 transition-all duration-200 font-medium shadow-sm">
                  <svg class="w-4 h-4 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                  </svg>
                  Supprimer
                </button>

                <!-- Boutons Validation (Admin seulement) -->
                <template v-if="canValidate && application?.status === 'pending'">
                  <button 
                    v-if="canReject"
                    @click="updateStatus('rejected')"
                    class="inline-flex items-center px-4 py-2.5 border border-red-300 text-red-700 bg-red-50 rounded-xl hover:bg-red-100 hover:border-red-400 focus:outline-none focus:ring-2 focus:ring-red-500 focus:ring-offset-2 transition-all duration-200 font-medium shadow-sm">
                    <svg class="w-4 h-4 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                    Rejeter
                  </button>
                  <button 
                    @click="updateStatus('approved')"
                    class="inline-flex items-center px-4 py-2.5 bg-gradient-to-r from-green-600 to-emerald-600 text-white rounded-xl hover:from-green-700 hover:to-emerald-700 focus:outline-none focus:ring-2 focus:ring-green-500 focus:ring-offset-2 transition-all duration-200 font-medium shadow-lg hover:shadow-xl transform hover:-translate-y-0.5">
                    <svg class="w-4 h-4 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                    </svg>
                    Approuver
                  </button>
                </template>
              </div>
            </div>
          </div>
        </template>
      </div>

      <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Informations principales -->
        <div class="lg:col-span-2 space-y-6">
          <div class="bg-white rounded-2xl shadow-sm p-6">
            <template v-if="loading">
              <!-- Skeleton pour les informations principales -->
              <div class="w-64 h-6 bg-gray-200 rounded animate-pulse mb-6"></div>
              
              <div class="space-y-8">
                <!-- Skeleton pour informations personnelles -->
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                  <div v-for="i in 8" :key="'info-skeleton-'+i">
                    <div class="w-20 h-4 bg-gray-200 rounded animate-pulse mb-2"></div>
                    <div class="w-32 h-4 bg-gray-200 rounded animate-pulse"></div>
                  </div>
                </div>

                <!-- Skeleton pour documents d'identification -->
                <div class="pt-6 border-t border-gray-200">
                  <div class="w-48 h-6 bg-gray-200 rounded animate-pulse mb-6"></div>
                  <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                    <div v-for="i in 3" :key="'doc-skeleton-'+i">
                      <div class="w-24 h-4 bg-gray-200 rounded animate-pulse mb-2"></div>
                      <div class="w-28 h-4 bg-gray-200 rounded animate-pulse"></div>
                    </div>
                  </div>
                  <div class="mt-4 space-y-4">
                    <div v-for="i in 4" :key="'extra-skeleton-'+i">
                      <div class="w-20 h-4 bg-gray-200 rounded animate-pulse mb-2"></div>
                      <div class="w-36 h-4 bg-gray-200 rounded animate-pulse"></div>
                    </div>
                  </div>
                </div>

                <!-- Skeleton pour informations commerciales -->
                <div class="pt-6 border-t border-gray-200">
                  <div class="w-48 h-6 bg-gray-200 rounded animate-pulse mb-6"></div>
                  <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div v-for="i in 6" :key="'business-skeleton-'+i">
                      <div class="w-24 h-4 bg-gray-200 rounded animate-pulse mb-2"></div>
                      <div class="w-40 h-4 bg-gray-200 rounded animate-pulse"></div>
                    </div>
                  </div>
                </div>

                <!-- Skeleton pour informations supplémentaires -->
                <div class="pt-6 border-t border-gray-200">
                  <div class="w-48 h-6 bg-gray-200 rounded animate-pulse mb-6"></div>
                  <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div v-for="i in 3" :key="'extra-info-skeleton-'+i">
                      <div class="w-32 h-4 bg-gray-200 rounded animate-pulse mb-2"></div>
                      <div class="w-24 h-4 bg-gray-200 rounded animate-pulse"></div>
                    </div>
                  </div>
                </div>
              </div>
            </template>

            <template v-else>
              <h2 class="text-xl font-semibold text-orange-600 mb-6">Informations du représentant légal</h2>
              
              <div class="space-y-8">
                <!-- Informations personnelles -->
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                  <div>
                    <label class="block text-sm font-medium" style="color: #005BA4">Nom</label>
                    <div class="mt-1 text-gray-900">{{ application?.last_name }}</div>
                  </div>
                  <div>
                    <label class="block text-sm font-medium" style="color: #005BA4">Prénom(s)</label>
                    <div class="mt-1 text-gray-900">{{ application?.first_name }}</div>
                  </div>
                  <div>
                    <label class="block text-sm font-medium" style="color: #005BA4">Date de naissance</label>
                    <div class="mt-1 text-gray-900">{{ formatDate(application?.birth_date, true) }}</div>
                  </div>
                  <div>
                    <label class="block text-sm font-medium" style="color: #005BA4">Lieu de naissance</label>
                    <div class="mt-1 text-gray-900">{{ application?.birth_place }}</div>
                  </div>
                  <div>
                    <label class="block text-sm font-medium" style="color: #005BA4">Genre</label>
                    <div class="mt-1 text-gray-900">{{ application?.gender === 'M' ? 'Masculin' : 'Féminin' }}</div>
                  </div>
                  <div>
                    <label class="block text-sm font-medium" style="color: #005BA4">Nationalité</label>
                    <div class="mt-1 text-gray-900">{{ application?.nationality }}</div>
                  </div>
                  <div>
                    <label class="block text-sm font-medium" style="color: #005BA4">Email</label>
                    <div class="mt-1 text-gray-900">{{ application?.email || 'Non renseigné' }}</div>
                  </div>
                  <div>
                    <label class="block text-sm font-medium" style="color: #005BA4">Téléphone</label>
                    <div class="mt-1 text-gray-900">{{ application?.phone }}</div>
                  </div>
                </div>

                <div class="pt-6 border-t border-gray-200">
                  <h3 class="text-xl font-semibold text-orange-600 mb-6">Documents d'identification</h3>
                  <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                    <div>
                      <label class="block text-sm font-medium" style="color: #005BA4">Type de pièce</label>
                      <div class="mt-1 text-gray-900">{{ getIdTypeLabel(application?.id_type) }}</div>
                    </div>
                    <div>
                      <label class="block text-sm font-medium" style="color: #005BA4">Numéro</label>
                      <div class="mt-1 text-gray-900">{{ application?.id_number }}</div>
                    </div>
                    <div>
                      <label class="block text-sm font-medium" style="color: #005BA4">Date d'expiration</label>
                      <div class="mt-1 text-gray-900">{{ formatDate(application?.id_expiry_date, true) }}</div>
                    </div>
                  </div>
                  
                  <div class="mt-4">
                    <label class="block text-sm font-medium" style="color: #005BA4">Est étranger</label>
                    <div class="mt-1 text-gray-900">{{ formatBoolean(application?.is_foreigner) }}</div>
                  </div>

                  <div class="mt-4">
                    <label class="block text-sm font-medium" style="color: #005BA4">Carte ANID</label>
                    <div class="mt-1 text-gray-900">{{ application?.anid_number || 'Non renseigné' }}</div>
                  </div>

                  <div v-if="application?.nif_number" class="mt-4">
                    <label class="block text-sm font-medium text-gray-700">Numéro NIF</label>
                    <div class="mt-1 text-gray-900">{{ application?.nif_number }}</div>
                  </div>

                  <div v-if="application?.cfe_number" class="mt-4">
                    <label class="block text-sm font-medium text-gray-700">Numéro CFE</label>
                    <div class="mt-1 text-gray-900">{{ application?.cfe_number }}</div>
                  </div>

                  <div v-if="application?.rccm_number" class="mt-4">
                    <label class="block text-sm font-medium text-gray-700">Numéro RCCM</label>
                    <div class="mt-1 text-gray-900">{{ application?.rccm_number }}</div>
                  </div>

                  <div v-if="application?.trade_register_number" class="mt-4">
                    <label class="block text-sm font-medium text-gray-700">Numéro de Registre du Commerce</label>
                    <div class="mt-1 text-gray-900">{{ application?.trade_register_number }}</div>
                  </div>

                  <div v-if="application?.professional_card_number" class="mt-4">
                    <label class="block text-sm font-medium text-gray-700">Numéro de Carte Professionnelle</label>
                    <div class="mt-1 text-gray-900">{{ application?.professional_card_number }}</div>
                  </div>
                </div>

                <div class="pt-6 border-t border-gray-200">
                  <h3 class="text-xl font-semibold text-orange-600 mb-6">Informations du commerce</h3>
                  <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                      <label class="block text-sm font-medium" style="color: #005BA4">Nom du commerce</label>
                      <div class="mt-1 text-gray-900">{{ application?.business_name }}</div>
                    </div>
                    <div>
                      <label class="block text-sm font-medium" style="color: #005BA4">Type de commerce</label>
                      <div class="mt-1 text-gray-900">{{ application?.business_type }}</div>
                    </div>
                    <div>
                      <label class="block text-sm font-medium" style="color: #005BA4">Téléphone du commerce</label>
                      <div class="mt-1 text-gray-900">{{ application?.merchant_phone }}</div>
                    </div>
                    <div>
                      <label class="block text-sm font-medium" style="color: #005BA4">Email du commerce</label>
                      <div class="mt-1 text-gray-900">{{ application?.business_email || 'Non renseigné' }}</div>
                    </div>
                    <div class="md:col-span-2">
                      <label class="block text-sm font-medium" style="color: #005BA4">Adresse du commerce</label>
                      <div class="mt-1 text-gray-900">{{ application?.business_address }}</div>
                    </div>
                    <div>
                      <label class="block text-sm font-medium" style="color: #005BA4">Type d'utilisation</label>
                      <div class="mt-1 text-gray-900">{{ getUsageTypeLabel(application?.usage_type) }}</div>
                    </div>
                    <div>
                      <label class="block text-sm font-medium" style="color: #005BA4">Numéro CFE</label>
                      <div class="mt-1 text-gray-900">{{ application?.cfe_number || 'Non renseigné' }}</div>
                    </div>
                  </div>
                </div>

                <div class="pt-6 border-t border-gray-200">
                  <h3 class="text-xl font-semibold text-orange-600 mb-6">Informations supplémentaires</h3>
                  <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                      <label class="block text-sm font-medium" style="color: #005BA4">Chiffre d'affaires mensuel estimé</label>
                      <div class="mt-1 text-gray-900">{{ formatMoney(application?.estimatedTurnover) }}</div>
                    </div>
                    <div>
                      <label class="block text-sm font-medium" style="color: #005BA4">Volume de transactions mensuel estimé</label>
                      <div class="mt-1 text-gray-900">{{ formatMoney(application?.estimatedTransactionVolume) }}</div>
                    </div>
                    <div>
                      <label class="block text-sm font-medium" style="color: #005BA4">Date de soumission</label>
                      <div class="mt-1 text-gray-900">{{ formatDate(application?.submitted_at) }}</div>
                    </div>
                  </div>
                </div>
              </div>
            </template>
          </div>

          <!-- Documents -->
          <div class="bg-white rounded-2xl shadow-sm p-6">
            <template v-if="loading">
              <!-- Skeleton pour les documents -->
              <div class="w-32 h-6 bg-gray-200 rounded animate-pulse mb-6"></div>
              
              <div class="space-y-4">
                <div v-for="i in 3" :key="'doc-skeleton-'+i" 
                     class="flex items-center justify-between p-4 border rounded-xl">
                  <div class="flex items-center space-x-4">
                    <div class="w-10 h-10 bg-gray-200 rounded-lg animate-pulse"></div>
                    <div>
                      <div class="w-32 h-4 bg-gray-200 rounded animate-pulse mb-2"></div>
                      <div class="w-16 h-3 bg-gray-200 rounded animate-pulse"></div>
                    </div>
                  </div>
                  <div class="flex items-center space-x-3">
                    <div class="w-20 h-6 bg-gray-200 rounded-full animate-pulse"></div>
                    <div class="w-28 h-8 bg-gray-200 rounded-xl animate-pulse"></div>
                  </div>
                </div>
              </div>
            </template>

            <template v-else>
              <h2 class="text-xl font-semibold text-orange-600 mb-6">Documents</h2>
              
              <div v-if="application?.documents?.length > 0" class="space-y-4">
                <div v-for="doc in application?.documents" :key="doc.id" 
                     class="flex items-center justify-between p-4 border rounded-xl hover:bg-gray-50 transition-colors">
                  <div class="flex items-center space-x-4">
                    <div class="w-10 h-10 bg-orange-100 rounded-lg flex items-center justify-center">
                      <svg class="w-6 h-6 text-orange-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z" />
                      </svg>
                    </div>
                    <div>
                      <h4 class="text-sm font-medium" style="color: #005BA4">{{ doc.type_label }}</h4>
                      <p class="text-sm text-gray-500">{{ formatFileSize(doc.size) }}</p>
                    </div>
                  </div>
                  <div class="flex items-center space-x-3">
                    <span :class="[
                      'inline-flex items-center px-3 py-1 text-xs font-semibold rounded-full',
                      doc.verified ? 'bg-green-100 text-green-800 border border-green-200' : 'bg-gray-100 text-gray-700 border border-gray-200'
                    ]">
                      <svg v-if="doc.verified" class="w-3 h-3 mr-1" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd" />
                      </svg>
                      <svg v-else class="w-3 h-3 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                      </svg>
                      {{ doc.verified ? 'Vérifié' : 'En attente' }}
                    </span>
                    <a :href="getDocumentUrl(doc.id)"
                       target="_blank"
                       class="inline-flex items-center px-4 py-2 bg-gradient-to-r from-orange-50 to-amber-50 border border-orange-200 text-sm font-medium rounded-xl text-orange-700 hover:from-orange-100 hover:to-amber-100 hover:border-orange-300 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-orange-500 transition-all duration-200 shadow-sm hover:shadow-md">
                      <svg class="w-4 h-4 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                      </svg>
                      Consulter
                    </a>
                  </div>
                </div>
              </div>
              
              <div v-else class="text-center py-12">
                <div class="w-16 h-16 bg-gray-100 rounded-lg flex items-center justify-center mx-auto mb-4">
                  <svg class="w-8 h-8 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z" />
                  </svg>
                </div>
                <h3 class="text-lg font-medium text-gray-900 mb-2">Aucun document</h3>
                <p class="text-gray-500">Cette candidature ne contient aucun document pour le moment.</p>
              </div>
            </template>
          </div>
        </div>

        <!-- Timeline et Notes -->
        <div class="space-y-6">
          <div class="bg-white rounded-2xl shadow-sm p-6">
            <template v-if="loading">
              <!-- Skeleton pour Timeline -->
              <div class="w-24 h-6 bg-gray-200 rounded animate-pulse mb-6"></div>
              
              <div class="flow-root">
                <ul class="-mb-8">
                  <li v-for="i in 4" :key="'timeline-skeleton-'+i">
                    <div class="relative pb-8">
                      <span v-if="i !== 4" 
                            class="absolute top-4 left-4 -ml-px h-full w-0.5 bg-gray-200" 
                            aria-hidden="true"></span>
                      <div class="relative flex space-x-3">
                        <div>
                          <div class="h-8 w-8 bg-gray-200 rounded-full animate-pulse"></div>
                        </div>
                        <div class="min-w-0 flex-1">
                          <div>
                            <div class="w-32 h-4 bg-gray-200 rounded animate-pulse mb-2"></div>
                            <div class="w-24 h-3 bg-gray-200 rounded animate-pulse"></div>
                          </div>
                          <div class="mt-2 w-48 h-3 bg-gray-200 rounded animate-pulse"></div>
                        </div>
                      </div>
                    </div>
                  </li>
                </ul>
              </div>
            </template>

            <template v-else>
              <h2 class="text-xl font-semibold text-orange-600 mb-6">Timeline</h2>
              
              <div class="flow-root">
                <ul class="-mb-8">
                  <li v-for="(event, index) in application?.timeline" :key="event.id">
                    <div class="relative pb-8">
                      <span v-if="index !== application.timeline.length - 1" 
                            class="absolute top-4 left-4 -ml-px h-full w-0.5 bg-gray-200" 
                            aria-hidden="true"></span>
                      <div class="relative flex space-x-3">
                        <div>
                          <span :class="[
                            'h-8 w-8 rounded-full flex items-center justify-center ring-8 ring-white',
                            event.type === 'status_change' && 'bg-blue-500',
                            event.type === 'document_added' && 'bg-green-500',
                            event.type === 'note_added' && 'bg-yellow-500'
                          ]">
                            <svg class="h-5 w-5 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                              <path v-if="event.type === 'status_change'" 
                                    stroke-linecap="round" 
                                    stroke-linejoin="round" 
                                    stroke-width="2" 
                                    d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                              <path v-else-if="event.type === 'document_added'" 
                                    stroke-linecap="round" 
                                    stroke-linejoin="round" 
                                    stroke-width="2" 
                                    d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z" />
                              <path v-else
                                    stroke-linecap="round" 
                                    stroke-linejoin="round" 
                                    stroke-width="2" 
                                    d="M7 8h10M7 12h4m1 8l-4-4H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-3l-4 4z" />
                            </svg>
                          </span>
                        </div>
                        <div class="min-w-0 flex-1">
                          <div>
                            <div class="text-sm font-medium text-gray-900">{{ event.title }}</div>
                            <p class="mt-0.5 text-sm text-gray-500">{{ formatDate(event.created_at) }}</p>
                          </div>
                          <div v-if="event.description" class="mt-2 text-sm text-gray-700">
                            {{ event.description }}
                          </div>
                        </div>
                      </div>
                    </div>
                  </li>
                </ul>
              </div>
            </template>
          </div>

          <div v-if="canEdit" class="bg-white rounded-2xl shadow-sm p-6">
            <template v-if="loading">
              <!-- Skeleton pour Notes administrateur -->
              <div class="w-36 h-6 bg-gray-200 rounded animate-pulse mb-6"></div>
              
              <div class="bg-gray-50 rounded-lg p-4">
                <div class="space-y-2">
                  <div class="w-full h-4 bg-gray-200 rounded animate-pulse"></div>
                  <div class="w-4/5 h-4 bg-gray-200 rounded animate-pulse"></div>
                  <div class="w-3/5 h-4 bg-gray-200 rounded animate-pulse"></div>
                </div>
                <div class="mt-2 flex items-center justify-between">
                  <div class="w-24 h-3 bg-gray-200 rounded animate-pulse"></div>
                  <div class="w-20 h-8 bg-gray-200 rounded-lg animate-pulse"></div>
                </div>
              </div>
            </template>

            <template v-else>
              <h2 class="text-xl font-semibold text-orange-600 mb-6">Notes administrateur</h2>
              
              <!-- Note existante -->
              <div v-if="application?.admin_notes && !isEditing" class="bg-gray-50 rounded-lg p-4">
                <div class="text-sm text-gray-700 whitespace-pre-wrap">{{ application.admin_notes }}</div>
                <div class="mt-2 flex items-center justify-between">
                  <div class="text-xs text-gray-500">{{ formatDate(application?.reviewed_at) }}</div>
                  <button
                    @click="startEditing"
                    class="inline-flex items-center px-3 py-2 bg-gradient-to-r from-blue-50 to-indigo-50 border border-blue-200 text-sm font-medium rounded-lg text-blue-700 hover:from-blue-100 hover:to-indigo-100 hover:border-blue-300 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition-all duration-200 shadow-sm"
                  >
                    <svg class="w-4 h-4 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                    </svg>
                    Modifier
                  </button>
                </div>
              </div>
            </template>

            <!-- Mode édition -->
            <form v-if="isEditing" @submit.prevent="updateNote" class="mt-6">
              <div>
                <label for="editedNote" class="sr-only">Modifier la note</label>
                <textarea
                  id="editedNote"
                  v-model="editedNote"
                  rows="3"
                  class="block w-full rounded-xl border-gray-300 shadow-sm focus:border-orange-500 focus:ring-orange-500"
                  placeholder="Modifier la note..."
                ></textarea>
              </div>
              <div class="mt-3 flex items-center justify-end space-x-3">
                <button
                  type="button"
                  @click="cancelEditing"
                  class="inline-flex items-center px-4 py-2 border border-gray-300 text-sm font-medium rounded-xl text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-gray-500 transition-all duration-200 shadow-sm"
                >
                  <svg class="w-4 h-4 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                  </svg>
                  Annuler
                </button>
                <button
                  type="submit"
                  :disabled="!editedNote.trim()"
                  class="inline-flex items-center px-4 py-2 bg-gradient-to-r from-orange-600 to-amber-600 text-white rounded-xl hover:from-orange-700 hover:to-amber-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-orange-500 disabled:opacity-50 disabled:cursor-not-allowed transition-all duration-200 font-medium shadow-lg hover:shadow-xl transform hover:-translate-y-0.5"
                >
                  <svg class="w-4 h-4 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                  </svg>
                  Enregistrer
                </button>
              </div>
            </form>

            <!-- Formulaire d'ajout de note (visible seulement s'il n'y a pas de note ou en mode non-édition) -->
            <form v-if="!application?.admin_notes && !isEditing" @submit.prevent="addNote" class="mt-6">
              <div>
                <label for="note" class="sr-only">Ajouter une note</label>
                <textarea
                  id="note"
                  v-model="newNote"
                  rows="3"
                  class="block w-full rounded-xl border-gray-300 shadow-sm focus:border-orange-500 focus:ring-orange-500"
                  placeholder="Ajouter une note..."
                ></textarea>
              </div>
              <div class="mt-3 flex items-center justify-end">
                <button
                  type="submit"
                  :disabled="!newNote.trim()"
                  class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-xl shadow-sm text-white bg-orange-600 hover:bg-orange-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-orange-500 disabled:opacity-50 disabled:cursor-not-allowed"
                >
                  Ajouter la note
                </button>
              </div>
            </form>
          </div>

          <!-- Section Localisation -->
      <div class="bg-white rounded-2xl shadow-sm overflow-hidden">
        <div class="p-6">
          <template v-if="loading">
            <!-- Skeleton pour Localisation -->
            <div class="w-32 h-6 bg-gray-200 rounded animate-pulse mb-6"></div>
            <div class="space-y-4">
              <div class="flex space-x-4">
                <div class="flex-1">
                  <div class="w-24 h-4 bg-gray-200 rounded animate-pulse mb-2"></div>
                  <div class="w-full h-10 bg-gray-200 rounded animate-pulse"></div>
                </div>
                <div class="flex-1">
                  <div class="w-32 h-4 bg-gray-200 rounded animate-pulse mb-2"></div>
                  <div class="w-full h-10 bg-gray-200 rounded animate-pulse"></div>
                </div>
              </div>
              <div>
                <div class="w-28 h-4 bg-gray-200 rounded animate-pulse mb-2"></div>
                <div class="w-full h-24 bg-gray-200 rounded animate-pulse"></div>
              </div>
            </div>
          </template>

          <template v-else>
            <h2 class="text-xl font-semibold text-orange-600 mb-6">Localisation du commerce</h2>
            
            <div class="space-y-4">
              <!-- Coordonnées GPS -->
              <div class="flex space-x-4">
                <div class="flex-1">
                  <label class="block text-sm font-medium mb-2" style="color: #005BA4">Latitude</label>
                  <div class="bg-gray-50 rounded-lg p-3">
                    <span class="text-sm font-mono text-gray-700">
                      {{ application?.latitude || 'Non renseignée' }}
                    </span>
                  </div>
                </div>
                <div class="flex-1">
                  <label class="block text-sm font-medium mb-2" style="color: #005BA4">Longitude</label>
                  <div class="bg-gray-50 rounded-lg p-3">
                    <span class="text-sm font-mono text-gray-700">
                      {{ application?.longitude || 'Non renseignée' }}
                    </span>
                  </div>
                </div>
              </div>

              <!-- Adresse du commerce -->
              <div>
                <label class="block text-sm font-medium mb-2" style="color: #005BA4">Adresse du commerce</label>
                <div class="bg-gray-50 rounded-lg p-3">
                  <p class="text-sm text-gray-700">
                    {{ application?.shop_address || 'Non renseignée' }}
                  </p>
                </div>
              </div>

              <!-- Description de la localisation -->
              <div v-if="application?.location_description">
                <label class="block text-sm font-medium mb-2" style="color: #005BA4">Description de la localisation</label>
                <div class="bg-gray-50 rounded-lg p-3">
                  <p class="text-sm text-gray-700 whitespace-pre-wrap">
                    {{ application.location_description }}
                  </p>
                </div>
              </div>

              <!-- Carte intégrée -->
              <div v-if="application?.latitude && application?.longitude" class="pt-4">
                <label class="block text-sm font-medium mb-2" style="color: #005BA4">Localisation sur la carte</label>
                <div class="bg-gray-50 rounded-lg p-3">
                  <div
                    ref="mapContainer" 
                    id="location-map"
                    class="w-full h-80 rounded-lg border border-gray-200"
                    style="min-height: 320px;"
                  ></div>
                  <p class="text-xs text-gray-500 mt-2 text-center">
                    📍 {{ application.latitude }}, {{ application.longitude }}
                  </p>
                </div>
              </div>
            </div>
          </template>
        </div>
      </div>

      <!-- Section Signature -->
      <div class="bg-white rounded-2xl shadow-sm overflow-hidden">
        <div class="p-6">
          <template v-if="loading">
            <!-- Skeleton pour Signature -->
            <div class="w-28 h-6 bg-gray-200 rounded animate-pulse mb-6"></div>
            <div class="w-full h-48 bg-gray-200 rounded-lg animate-pulse"></div>
          </template>

          <template v-else>
            <h2 class="text-xl font-semibold text-gray-800 mb-6">Signature du demandeur</h2>
            
            <div v-if="application?.signature" class="bg-gray-50 rounded-lg p-4">
              <div class="flex items-center justify-center">
                <img 
                  :src="application.signature" 
                  alt="Signature du demandeur"
                  class="max-w-full h-auto max-h-48 border border-gray-200 rounded-lg shadow-sm bg-white"
                />
              </div>
              <p class="text-xs text-gray-500 text-center mt-2">
                Signature enregistrée le {{ formatDate(application?.submitted_at) }}
              </p>
            </div>
            
            <div v-else class="bg-yellow-50 border border-yellow-200 rounded-lg p-4">
              <div class="flex items-center">
                <svg class="w-5 h-5 text-yellow-600 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L3.732 16.5c-.77.833.192 2.5 1.732 2.5z" />
                </svg>
                <span class="text-sm text-yellow-800">Aucune signature disponible</span>
              </div>
            </div>
          </template>
        </div>
      </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
import { ref, onMounted, computed, nextTick } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import { useAuthStore } from '../stores/auth'
import { useNotificationStore } from '../stores/notifications'
import ApiService from '../services/ApiService'
import * as L from 'leaflet'
import 'leaflet/dist/leaflet.css'

// Fix pour les icônes Leaflet dans Vite
delete L.Icon.Default.prototype._getIconUrl
L.Icon.Default.mergeOptions({
  iconRetinaUrl: 'https://cdnjs.cloudflare.com/ajax/libs/leaflet/1.9.4/images/marker-icon-2x.png',
  iconUrl: 'https://cdnjs.cloudflare.com/ajax/libs/leaflet/1.9.4/images/marker-icon.png',
  shadowUrl: 'https://cdnjs.cloudflare.com/ajax/libs/leaflet/1.9.4/images/marker-shadow.png',
})

export default {
  name: 'ApplicationDetails',
  
  setup() {
    const route = useRoute()
    const router = useRouter()
    const authStore = useAuthStore()
    const notificationStore = useNotificationStore()
    const loading = ref(true)
    const application = ref(null)
    const newNote = ref('')
    const isEditing = ref(false)
    const editedNote = ref('')
    const mapContainer = ref(null)
    const map = ref(null)

    // Permissions calculées
    const canEdit = computed(() => authStore.canEditApplications)
    const canValidate = computed(() => authStore.canValidateApplications)
    const canReject = computed(() => authStore.canRejectApplications)
    const canDelete = computed(() => authStore.canDeleteApplications)
    
    const loadApplication = async () => {
      loading.value = true
      try {
        const response = await ApiService.getApplication(route.params.id)
        console.log('Response from API:', response.data)
        application.value = response.data.data
        console.log('Documents in application:', application.value?.documents)
        console.log('Number of documents:', application.value?.documents?.length)
      } catch (error) {
        console.error('Erreur chargement candidature:', error)
        // Rediriger vers le dashboard en cas d'erreur
        router.push('/dashboard')
      } finally {
        loading.value = false
      }
    }
    
    const updateStatus = async (status) => {
      try {
        loading.value = true
        
        if (status === 'approved') {
          await ApiService.approveApplication(application.value.id)
        } else if (status === 'rejected') {
          const reason = prompt('Raison du rejet (optionnel):')
          await ApiService.rejectApplication(application.value.id, reason)
        } else {
          // Fallback pour les autres statuts
          await ApiService.updateApplicationStatus(application.value.id, status)
        }
        
        // Notification de succès
        const statusLabels = {
          approved: 'approuvée',
          rejected: 'rejetée'
        }
        notificationStore.success(
          'Statut mis à jour',
          `La candidature a été ${statusLabels[status]} avec succès.`
        )
        
        await loadApplication() // Recharger les données
      } catch (error) {
        console.error('Erreur mise à jour statut:', error)
        notificationStore.error(
          'Erreur',
          error.response?.data?.message || 'Impossible de mettre à jour le statut de la candidature.'
        )
      } finally {
        loading.value = false
      }
    }

    const editApplication = () => {
      // Navigation vers le formulaire d'édition
      router.push(`/applications/${application.value.id}/edit`)
    }

    const confirmDelete = () => {
      if (confirm('Êtes-vous sûr de vouloir supprimer cette candidature ? Cette action est irréversible.')) {
        deleteApplication()
      }
    }

    const deleteApplication = async () => {
      try {
        loading.value = true
        
        // Utiliser la suppression définitive pour les admins
        if (canDelete.value) {
          await ApiService.forceDeleteApplication(application.value.id)
        } else {
          // Archivage pour les autres (si autorisé)
          await ApiService.deleteApplication(application.value.id)
        }
        
        notificationStore.success(
          'Candidature supprimée',
          'La candidature a été supprimée avec succès.'
        )
        
        // Redirection vers le dashboard après suppression
        router.push('/dashboard')
      } catch (error) {
        console.error('Erreur suppression candidature:', error)
        notificationStore.error(
          'Erreur',
          error.response?.data?.message || 'Impossible de supprimer la candidature.'
        )
      } finally {
        loading.value = false
      }
    }
    
    const startEditing = () => {
      editedNote.value = application.value.admin_notes || ''
      isEditing.value = true
    }

    const cancelEditing = () => {
      isEditing.value = false
      editedNote.value = ''
    }

    const updateNote = async () => {
      if (!editedNote.value.trim()) return
      
      try {
        loading.value = true
        await ApiService.updateApplicationNote(application.value.id, editedNote.value)
        isEditing.value = false
        editedNote.value = ''
        await loadApplication() // Recharger les données
      } catch (error) {
        console.error('Erreur mise à jour note:', error)
      } finally {
        loading.value = false
      }
    }

    const addNote = async () => {
      if (!newNote.value.trim()) return
      
      try {
        loading.value = true
        await ApiService.addApplicationNote(application.value.id, newNote.value)
        newNote.value = ''
        await loadApplication() // Recharger les données
      } catch (error) {
        console.error('Erreur ajout note:', error)
      } finally {
        loading.value = false
      }
    }
    
    const getStatusLabel = (status) => {
      const labels = {
        pending: 'En attente',
        approved: 'Approuvée',
        rejected: 'Rejetée'
      }
      return labels[status] || status
    }
    
    const formatDate = (dateString, dateOnly = false) => {
      if (!dateString) return '';
      const options = {
        day: 'numeric',
        month: 'long',
        year: 'numeric',
        ...(dateOnly ? {} : { hour: 'numeric', minute: 'numeric' })
      }
      return new Date(dateString).toLocaleDateString('fr-FR', options)
    }
    
    const formatFileSize = (bytes) => {
      if (!bytes) return '0 B'
      const k = 1024
      const sizes = ['B', 'KB', 'MB', 'GB']
      const i = Math.floor(Math.log(bytes) / Math.log(k))
      return parseFloat((bytes / Math.pow(k, i)).toFixed(1)) + ' ' + sizes[i]
    }

    const formatMoney = (amount) => {
      if (!amount) return '0 FCFA'
      return new Intl.NumberFormat('fr-FR', {
        style: 'currency',
        currency: 'XOF',
        minimumFractionDigits: 0,
        maximumFractionDigits: 0
      }).format(amount)
    }

    const getIdTypeLabel = (type) => {
      const types = {
        cni: "Carte Nationale d'Identité",
        passport: "Passeport",
        residence: "Carte de séjour",
        elector: "Carte d'électeur"
      }
      return types[type] || type
    }

    const getUsageTypeLabel = (type) => {
      const types = {
        personal: "Personnel",
        professional: "Professionnel",
        both: "Personnel et Professionnel"
      }
      return types[type] || type
    }
    
    const getDocumentUrl = (documentId) => {
      return ApiService.getDocumentUrl(documentId)
    }

    const formatBoolean = (value) => {
      return value ? 'Oui' : 'Non'
    }

    const initializeMap = async () => {
      if (!application.value?.latitude || !application.value?.longitude || !mapContainer.value) {
        return
      }

      await nextTick()
      
      // Détruire la carte existante s'il y en a une
      if (map.value) {
        map.value.remove()
      }

      try {
        // Créer la carte
        map.value = L.map('location-map').setView(
          [parseFloat(application.value.latitude), parseFloat(application.value.longitude)], 
          16 // Niveau de zoom approprié pour voir le commerce
        )

        // Ajouter les tuiles OpenStreetMap
        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
          attribution: '© <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> contributors',
          maxZoom: 19
        }).addTo(map.value)

        // Créer une icône personnalisée pour le marqueur
        const customIcon = L.divIcon({
          className: 'custom-marker',
          html: `
            <div class="flex items-center justify-center w-8 h-8 bg-orange-500 rounded-full shadow-lg border-2 border-white">
              <svg class="w-4 h-4 text-white" fill="currentColor" viewBox="0 0 20 20">
                <path fill-rule="evenodd" d="M5.05 4.05a7 7 0 119.9 9.9L10 18.9l-4.95-4.95a7 7 0 010-9.9zM10 11a2 2 0 100-4 2 2 0 000 4z" clip-rule="evenodd" />
              </svg>
            </div>
          `,
          iconSize: [32, 32],
          iconAnchor: [16, 32]
        })

        // Ajouter le marqueur
        L.marker([parseFloat(application.value.latitude), parseFloat(application.value.longitude)], {
          icon: customIcon
        })
        .addTo(map.value)
        .bindPopup(`
          <div class="p-2">
            <h3 class="font-semibold text-sm mb-1">${application.value.business_name || 'Commerce'}</h3>
            <p class="text-xs text-gray-600 mb-1">${application.value.shop_address || 'Adresse non renseignée'}</p>
            <p class="text-xs text-gray-500">📍 ${application.value.latitude}, ${application.value.longitude}</p>
          </div>
        `)
        .openPopup()

        // Permettre le zoom et la navigation
        map.value.scrollWheelZoom.enable()
        map.value.doubleClickZoom.enable()
        map.value.boxZoom.enable()
        map.value.keyboard.enable()
        if (map.value.tap) map.value.tap.enable()

      } catch (error) {
        console.error('Erreur lors de l\'initialisation de la carte:', error)
      }
    }
    
    onMounted(async () => {
      await loadApplication()
      // Initialiser la carte après le chargement de l'application
      await nextTick()
      initializeMap()
    })
    
    return {
      loading,
      application,
      newNote,
      updateStatus,
      addNote,
      getStatusLabel,
      formatDate,
      formatFileSize,
      getDocumentUrl,
      formatMoney,
      getIdTypeLabel,
      getUsageTypeLabel,
      formatBoolean,
      mapContainer,
      initializeMap,
      isEditing,
      editedNote,
      startEditing,
      cancelEditing,
      updateNote,
      editApplication,
      confirmDelete,
      deleteApplication,
      // Permissions
      canEdit,
      canValidate,
      canReject,
      canDelete
    }
  }
}
</script>

<style scoped>
/* Styles pour la carte */
#location-map {
  position: relative;
  z-index: 1;
}

/* Fix pour les icônes Leaflet manquantes */
:global(.leaflet-default-icon-path) {
  background-image: url('https://unpkg.com/leaflet@1.9.4/dist/images/marker-icon.png');
}

/* Style pour le marqueur personnalisé */
:global(.custom-marker) {
  background: transparent !important;
  border: none !important;
}

/* Amélioration du popup */
:global(.leaflet-popup-content) {
  margin: 8px 12px !important;
  line-height: 1.4 !important;
}

:global(.leaflet-popup-content-wrapper) {
  border-radius: 8px !important;
}

/* Responsive pour la carte */
@media (max-width: 640px) {
  #location-map {
    height: 250px !important;
    min-height: 250px !important;
  }
}
</style>