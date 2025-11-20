<template>
  <div
    v-if="show"
    class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full z-50"
    @click="closeModal"
  >
    <div class="relative top-20 mx-auto p-5 border w-full max-w-3xl shadow-lg rounded-md bg-white dark:bg-gray-800" @click.stop>
      <div class="mt-3">
        <!-- Header -->
        <div class="flex items-center justify-between mb-4">
          <div class="flex items-center">
            <div class="mx-auto flex-shrink-0 flex items-center justify-center h-12 w-12 rounded-full bg-green-100 dark:bg-green-900">
              <i class="fas fa-file-import text-green-600 dark:text-green-400"></i>
            </div>
            <div class="ml-4">
              <h3 class="text-lg font-medium text-gray-900 dark:text-white">
                Importer des utilisateurs
              </h3>
              <p class="text-sm text-gray-500 dark:text-gray-400">
                Importez plusieurs utilisateurs à partir d'un fichier CSV
              </p>
            </div>
          </div>
          <button
            @click="closeModal"
            class="text-gray-400 hover:text-gray-600 dark:hover:text-gray-300 focus:outline-none"
          >
            <i class="fas fa-times"></i>
          </button>
        </div>

        <!-- Instructions -->
        <div class="bg-blue-50 dark:bg-blue-900/30 border border-blue-200 dark:border-blue-800 rounded-md p-4 mb-4">
          <div class="flex">
            <div class="flex-shrink-0">
              <i class="fas fa-info-circle text-blue-400"></i>
            </div>
            <div class="ml-3">
              <h3 class="text-sm font-medium text-blue-800 dark:text-blue-300">
                Instructions
              </h3>
              <div class="mt-2 text-sm text-blue-700 dark:text-blue-400">
                <ul class="list-disc list-inside space-y-1">
                  <li>Téléchargez le fichier template ci-dessous</li>
                  <li>Remplissez le fichier avec les informations des utilisateurs</li>
                  <li>Les colonnes requises : prénom, nom, téléphone, username, rôle</li>
                  <li>L'email est optionnel</li>
                  <li>Le mot de passe par défaut sera <strong>123456</strong> pour tous les utilisateurs</li>
                  <li>Les rôles possibles : <strong>admin</strong>, <strong>commercial</strong>, <strong>personnel</strong></li>
                </ul>
              </div>
            </div>
          </div>
        </div>

        <!-- Download Template Button -->
        <div class="mb-4">
          <button
            @click="downloadTemplate"
            type="button"
            class="w-full inline-flex items-center justify-center px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-md shadow-sm text-sm font-medium text-gray-700 dark:text-gray-300 bg-white dark:bg-gray-700 hover:bg-gray-50 dark:hover:bg-gray-600 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-orange-500"
          >
            <i class="fas fa-download mr-2"></i>
            Télécharger le fichier template
          </button>
        </div>

        <!-- Error Message -->
        <div v-if="errorMessage" class="bg-red-50 dark:bg-red-900/30 border border-red-200 dark:border-red-800 rounded-md p-3 mb-4">
          <div class="flex">
            <div class="flex-shrink-0">
              <i class="fas fa-exclamation-circle text-red-400"></i>
            </div>
            <div class="ml-3">
              <p class="text-sm text-red-700 dark:text-red-400">{{ errorMessage }}</p>
            </div>
          </div>
        </div>

        <!-- Success Message -->
        <div v-if="successMessage" class="bg-green-50 dark:bg-green-900/30 border border-green-200 dark:border-green-800 rounded-md p-3 mb-4">
          <div class="flex">
            <div class="flex-shrink-0">
              <i class="fas fa-check-circle text-green-400"></i>
            </div>
            <div class="ml-3">
              <p class="text-sm text-green-700 dark:text-green-400">{{ successMessage }}</p>
            </div>
          </div>
        </div>

        <!-- Import Errors -->
        <div v-if="importErrors.length > 0" class="bg-yellow-50 dark:bg-yellow-900/30 border border-yellow-200 dark:border-yellow-800 rounded-md p-3 mb-4 max-h-60 overflow-y-auto">
          <div class="flex">
            <div class="flex-shrink-0">
              <i class="fas fa-exclamation-triangle text-yellow-400"></i>
            </div>
            <div class="ml-3 flex-1">
              <h4 class="text-sm font-medium text-yellow-800 dark:text-yellow-300 mb-2">
                Erreurs d'importation ({{ importErrors.length }})
              </h4>
              <div class="space-y-2">
                <div v-for="(error, index) in importErrors" :key="index" class="text-sm text-yellow-700 dark:text-yellow-400">
                  <strong>Ligne {{ error.line }}:</strong>
                  <ul class="list-disc list-inside ml-4">
                    <li v-for="(err, idx) in error.errors" :key="idx">{{ err }}</li>
                  </ul>
                </div>
              </div>
            </div>
          </div>
        </div>

        <!-- File Upload -->
        <form @submit.prevent="handleImport">
          <div class="mb-4">
            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
              Fichier CSV <span class="text-red-500">*</span>
            </label>
            <div class="mt-1 flex justify-center px-6 pt-5 pb-6 border-2 border-gray-300 dark:border-gray-600 border-dashed rounded-md hover:border-orange-400 dark:hover:border-orange-500 transition-colors">
              <div class="space-y-1 text-center">
                <i class="fas fa-cloud-upload-alt text-4xl text-gray-400 dark:text-gray-500 mb-2"></i>
                <div class="flex text-sm text-gray-600 dark:text-gray-400">
                  <label
                    for="file-upload"
                    class="relative cursor-pointer rounded-md font-medium text-orange-600 dark:text-orange-500 hover:text-orange-500 dark:hover:text-orange-400 focus-within:outline-none focus-within:ring-2 focus-within:ring-offset-2 focus-within:ring-orange-500"
                  >
                    <span>Choisir un fichier</span>
                    <input
                      id="file-upload"
                      ref="fileInput"
                      type="file"
                      accept=".csv"
                      @change="handleFileSelect"
                      class="sr-only"
                    />
                  </label>
                  <p class="pl-1">ou glisser-déposer</p>
                </div>
                <p class="text-xs text-gray-500 dark:text-gray-400">
                  CSV uniquement (max 2MB)
                </p>
                <p v-if="selectedFile" class="text-sm text-gray-900 dark:text-white font-medium mt-2">
                  <i class="fas fa-file-csv text-green-500 mr-2"></i>
                  {{ selectedFile.name }}
                </p>
              </div>
            </div>
          </div>

          <!-- Actions -->
          <div class="flex items-center justify-end space-x-3 pt-4 border-t dark:border-gray-700">
            <button
              type="button"
              @click="closeModal"
              :disabled="isLoading"
              class="px-4 py-2 text-sm font-medium text-gray-700 dark:text-gray-300 bg-white dark:bg-gray-700 border border-gray-300 dark:border-gray-600 rounded-md shadow-sm hover:bg-gray-50 dark:hover:bg-gray-600 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-orange-500 disabled:opacity-50 disabled:cursor-not-allowed"
            >
              Annuler
            </button>
            <button
              type="submit"
              :disabled="!selectedFile || isLoading"
              class="px-4 py-2 text-sm font-medium text-white bg-orange-600 border border-transparent rounded-md shadow-sm hover:bg-orange-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-orange-500 disabled:opacity-50 disabled:cursor-not-allowed"
            >
              <span v-if="isLoading">
                <i class="fas fa-spinner fa-spin mr-2"></i>
                Importation en cours...
              </span>
              <span v-else>
                <i class="fas fa-upload mr-2"></i>
                Importer
              </span>
            </button>
          </div>
        </form>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref } from 'vue'
import axios from 'axios'

const props = defineProps({
  show: {
    type: Boolean,
    required: true
  }
})

const emit = defineEmits(['close', 'import-success'])

const selectedFile = ref(null)
const isLoading = ref(false)
const errorMessage = ref('')
const successMessage = ref('')
const importErrors = ref([])
const fileInput = ref(null)

const handleFileSelect = (event) => {
  const file = event.target.files[0]
  if (file) {
    if (file.size > 2 * 1024 * 1024) { // 2MB
      errorMessage.value = 'Le fichier est trop volumineux (max 2MB)'
      selectedFile.value = null
      return
    }
    if (!file.name.endsWith('.csv')) {
      errorMessage.value = 'Seuls les fichiers CSV sont acceptés'
      selectedFile.value = null
      return
    }
    selectedFile.value = file
    errorMessage.value = ''
    successMessage.value = ''
    importErrors.value = []
  }
}

const downloadTemplate = async () => {
  try {
    const response = await axios.get('/api/users/import-template', {
      responseType: 'blob'
    })
    
    const url = window.URL.createObjectURL(new Blob([response.data]))
    const link = document.createElement('a')
    link.href = url
    link.setAttribute('download', 'template_import_utilisateurs.csv')
    document.body.appendChild(link)
    link.click()
    link.remove()
    window.URL.revokeObjectURL(url)
  } catch (error) {
    console.error('Erreur lors du téléchargement du template:', error)
    errorMessage.value = 'Erreur lors du téléchargement du template'
  }
}

const handleImport = async () => {
  if (!selectedFile.value) {
    errorMessage.value = 'Veuillez sélectionner un fichier'
    return
  }

  isLoading.value = true
  errorMessage.value = ''
  successMessage.value = ''
  importErrors.value = []

  try {
    const formData = new FormData()
    formData.append('file', selectedFile.value)

    const response = await axios.post('/api/users/bulk-import', formData, {
      headers: {
        'Content-Type': 'multipart/form-data'
      }
    })

    if (response.data.success) {
      successMessage.value = response.data.message
      
      if (response.data.data.errors && response.data.data.errors.length > 0) {
        importErrors.value = response.data.data.errors
      } else {
        // Tous les utilisateurs ont été importés avec succès
        setTimeout(() => {
          emit('import-success')
          closeModal()
        }, 2000)
      }
    }
  } catch (error) {
    console.error('Erreur lors de l\'importation:', error)
    
    if (error.response?.data?.errors) {
      const errors = error.response.data.errors
      if (errors.file) {
        errorMessage.value = errors.file[0]
      } else {
        errorMessage.value = error.response.data.message || 'Erreur lors de l\'importation'
      }
    } else {
      errorMessage.value = 'Erreur lors de l\'importation des utilisateurs'
    }
  } finally {
    isLoading.value = false
  }
}

const closeModal = () => {
  if (!isLoading.value) {
    selectedFile.value = null
    errorMessage.value = ''
    successMessage.value = ''
    importErrors.value = []
    if (fileInput.value) {
      fileInput.value.value = ''
    }
    emit('close')
  }
}
</script>
