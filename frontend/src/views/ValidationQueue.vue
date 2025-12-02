<template>
  <div class="validation-queue min-h-screen bg-gray-50 dark:bg-gray-900 py-6">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
      <!-- Header -->
      <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm p-6 mb-6">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between space-y-4 sm:space-y-0">
          <div>
            <h1 class="text-xl sm:text-2xl font-bold text-gray-800 dark:text-white">
              File de Validation
            </h1>
            <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">
              {{ pagination.total }} PDV en attente de validation
            </p>
          </div>
          
          <div class="flex space-x-3">
            <button
              @click="loadQueue"
              :disabled="loading"
              class="flex items-center px-4 py-2 rounded-xl bg-gray-100 dark:bg-gray-700 text-gray-700 dark:text-gray-300 hover:bg-gray-200 dark:hover:bg-gray-600 transition-colors"
            >
              <svg class="w-5 h-5 mr-2" :class="{ 'animate-spin': loading }" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
              </svg>
              Actualiser
            </button>
          </div>
        </div>
      </div>

      <!-- Queue List -->
      <div class="space-y-4">
        <template v-if="loading && queue.length === 0">
          <div v-for="i in 3" :key="i" class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm p-6 animate-pulse">
            <div class="flex items-start space-x-4">
              <div class="w-12 h-12 bg-gray-200 dark:bg-gray-700 rounded-xl"></div>
              <div class="flex-1">
                <div class="h-4 bg-gray-200 dark:bg-gray-700 rounded w-1/3 mb-2"></div>
                <div class="h-3 bg-gray-200 dark:bg-gray-700 rounded w-1/2"></div>
              </div>
            </div>
          </div>
        </template>

        <template v-else-if="queue.length === 0">
          <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm p-12 text-center">
            <svg class="w-16 h-16 mx-auto text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
            </svg>
            <h3 class="mt-4 text-lg font-medium text-gray-900 dark:text-white">
              Aucun PDV en attente
            </h3>
            <p class="mt-2 text-gray-500 dark:text-gray-400">
              Tous les PDV ont été traités.
            </p>
          </div>
        </template>

        <template v-else>
          <div
            v-for="pdv in queue"
            :key="pdv.id"
            class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm overflow-hidden"
          >
            <div class="p-6">
              <div class="flex items-start justify-between">
                <div class="flex items-start space-x-4">
                  <div class="w-12 h-12 rounded-xl bg-yellow-100 dark:bg-yellow-900 flex items-center justify-center flex-shrink-0">
                    <svg class="w-6 h-6 text-yellow-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                  </div>
                  
                  <div>
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-white">
                      {{ pdv.nom_point }}
                    </h3>
                    <p class="text-sm text-gray-500 dark:text-gray-400">
                      {{ pdv.dealer_name }} • {{ pdv.reference_number }}
                    </p>
                    
                    <div class="mt-3 flex flex-wrap gap-2">
                      <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 dark:bg-blue-900 text-blue-800 dark:text-blue-200">
                        {{ pdv.region }}
                      </span>
                      <span v-if="pdv.prefecture" class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-gray-100 dark:bg-gray-700 text-gray-800 dark:text-gray-200">
                        {{ pdv.prefecture }}
                      </span>
                      <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-orange-100 dark:bg-orange-900 text-orange-800 dark:text-orange-200">
                        {{ pdv.organization?.name || 'N/A' }}
                      </span>
                    </div>

                    <div class="mt-3 grid grid-cols-2 sm:grid-cols-4 gap-4 text-sm">
                      <div>
                        <p class="text-gray-500 dark:text-gray-400">Gérant</p>
                        <p class="font-medium text-gray-900 dark:text-white">{{ pdv.firstname }} {{ pdv.lastname }}</p>
                      </div>
                      <div>
                        <p class="text-gray-500 dark:text-gray-400">Téléphone</p>
                        <p class="font-medium text-gray-900 dark:text-white">{{ pdv.numero_proprietaire }}</p>
                      </div>
                      <div>
                        <p class="text-gray-500 dark:text-gray-400">Soumis par</p>
                        <p class="font-medium text-gray-900 dark:text-white">{{ pdv.creator?.first_name || 'N/A' }}</p>
                      </div>
                      <div>
                        <p class="text-gray-500 dark:text-gray-400">Date</p>
                        <p class="font-medium text-gray-900 dark:text-white">{{ formatDate(pdv.created_at) }}</p>
                      </div>
                    </div>
                  </div>
                </div>
              </div>
            </div>

            <!-- Actions -->
            <div class="px-6 py-4 bg-gray-50 dark:bg-gray-750 border-t border-gray-200 dark:border-gray-700 flex flex-col sm:flex-row sm:items-center sm:justify-between space-y-3 sm:space-y-0">
              <div class="flex space-x-3">
                <router-link
                  :to="{ name: 'PdvDetails', params: { id: pdv.id } }"
                  class="px-4 py-2 rounded-xl bg-gray-100 dark:bg-gray-700 text-gray-700 dark:text-gray-300 text-sm font-medium hover:bg-gray-200 dark:hover:bg-gray-600 transition-colors"
                >
                  Voir détails
                </router-link>
              </div>
              
              <div class="flex space-x-3">
                <button
                  @click="openRejectModal(pdv)"
                  :disabled="processingId === pdv.id"
                  class="px-4 py-2 rounded-xl bg-red-100 text-red-700 text-sm font-medium hover:bg-red-200 transition-colors disabled:opacity-50"
                >
                  Rejeter
                </button>
                <button
                  @click="validatePdv(pdv)"
                  :disabled="processingId === pdv.id"
                  class="px-4 py-2 rounded-xl bg-green-500 text-white text-sm font-medium hover:bg-green-600 transition-colors disabled:opacity-50"
                >
                  <span v-if="processingId === pdv.id">...</span>
                  <span v-else>Valider</span>
                </button>
              </div>
            </div>
          </div>
        </template>
      </div>

      <!-- Pagination -->
      <div v-if="pagination.lastPage > 1" class="mt-6 flex justify-center">
        <nav class="flex space-x-2">
          <button
            @click="loadQueue(pagination.currentPage - 1)"
            :disabled="pagination.currentPage === 1"
            class="px-3 py-2 rounded-lg bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 text-gray-700 dark:text-gray-300 disabled:opacity-50"
          >
            Précédent
          </button>
          <span class="px-4 py-2 text-gray-600 dark:text-gray-400">
            {{ pagination.currentPage }} / {{ pagination.lastPage }}
          </span>
          <button
            @click="loadQueue(pagination.currentPage + 1)"
            :disabled="pagination.currentPage === pagination.lastPage"
            class="px-3 py-2 rounded-lg bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 text-gray-700 dark:text-gray-300 disabled:opacity-50"
          >
            Suivant
          </button>
        </nav>
      </div>
    </div>

    <!-- Reject Modal -->
    <Teleport to="body">
      <div v-if="rejectModal.show" class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/50" @click.self="rejectModal.show = false">
        <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-xl w-full max-w-md">
          <div class="p-6">
            <h3 class="text-lg font-bold text-gray-900 dark:text-white mb-4">
              Rejeter le PDV
            </h3>
            
            <p class="text-sm text-gray-600 dark:text-gray-400 mb-4">
              Vous êtes sur le point de rejeter <strong>{{ rejectModal.pdv?.nom_point }}</strong>.
              Veuillez indiquer le motif du rejet.
            </p>

            <textarea
              v-model="rejectModal.reason"
              rows="4"
              class="w-full px-4 py-3 rounded-xl border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:ring-2 focus:ring-orange-500 resize-none"
              placeholder="Motif du rejet..."
            ></textarea>

            <div class="mt-6 flex justify-end space-x-3">
              <button
                @click="rejectModal.show = false"
                class="px-4 py-2 rounded-xl bg-gray-100 dark:bg-gray-700 text-gray-700 dark:text-gray-300 font-medium"
              >
                Annuler
              </button>
              <button
                @click="rejectPdv"
                :disabled="!rejectModal.reason.trim() || processingId"
                class="px-4 py-2 rounded-xl bg-red-500 text-white font-medium hover:bg-red-600 disabled:opacity-50"
              >
                Confirmer le rejet
              </button>
            </div>
          </div>
        </div>
      </div>
    </Teleport>
  </div>
</template>

<script setup>
import { ref, reactive, computed, onMounted } from 'vue'
import { usePointOfSaleStore } from '../stores/pointOfSale'

const pdvStore = usePointOfSaleStore()

// State
const loading = computed(() => pdvStore.loading)
const queue = computed(() => pdvStore.validationQueue)
const pagination = computed(() => pdvStore.pagination)
const processingId = ref(null)

const rejectModal = reactive({
  show: false,
  pdv: null,
  reason: ''
})

// Methods
const loadQueue = async (page = 1) => {
  try {
    await pdvStore.fetchValidationQueue(page)
  } catch (error) {
    console.error('Error loading validation queue:', error)
  }
}

const validatePdv = async (pdv) => {
  processingId.value = pdv.id
  try {
    await pdvStore.validatePdv(pdv.id)
  } catch (error) {
    console.error('Error validating PDV:', error)
    alert('Erreur lors de la validation du PDV')
  } finally {
    processingId.value = null
  }
}

const openRejectModal = (pdv) => {
  rejectModal.pdv = pdv
  rejectModal.reason = ''
  rejectModal.show = true
}

const rejectPdv = async () => {
  if (!rejectModal.pdv || !rejectModal.reason.trim()) return

  processingId.value = rejectModal.pdv.id
  try {
    await pdvStore.rejectPdv(rejectModal.pdv.id, rejectModal.reason)
    rejectModal.show = false
  } catch (error) {
    console.error('Error rejecting PDV:', error)
    alert('Erreur lors du rejet du PDV')
  } finally {
    processingId.value = null
  }
}

const formatDate = (date) => {
  if (!date) return '-'
  return new Date(date).toLocaleDateString('fr-FR', {
    day: '2-digit',
    month: '2-digit',
    year: 'numeric',
    hour: '2-digit',
    minute: '2-digit'
  })
}

onMounted(() => {
  loadQueue()
})
</script>

<style scoped>
.validation-queue {
  width: 100%;
}
</style>
