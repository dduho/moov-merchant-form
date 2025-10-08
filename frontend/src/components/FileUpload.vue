<template>
  <div class="border-2 border-dashed border-gray-300 rounded-xl p-6 text-center hover:border-orange-400 transition-colors">
    <div v-if="!currentFile && !previewUrl">
      <i class="fas fa-cloud-upload-alt text-4xl text-gray-400 mb-4"></i>
      <p class="text-gray-600 mb-2">{{ label || 'Cliquez pour sélectionner un fichier' }}</p>
      <p class="text-sm text-gray-400">Formats acceptés: JPG, PNG, PDF (Max: 5MB)</p>
    </div>
    
    <!-- Aperçu du fichier -->
    <div v-if="previewUrl" class="relative">
      <img v-if="isImage" :src="previewUrl" alt="Aperçu" class="max-w-full h-48 object-cover mx-auto rounded-lg">
      <div v-else class="flex items-center justify-center h-48 bg-gray-100 rounded-lg">
        <i class="fas fa-file-alt text-4xl text-gray-400"></i>
        <p class="ml-2 text-gray-600">{{ currentFile?.name }}</p>
      </div>
      
      <!-- Bouton supprimer -->
      <button @click="removeFile" 
              type="button"
              class="absolute top-2 right-2 bg-red-500 text-white rounded-full w-8 h-8 flex items-center justify-center hover:bg-red-600 transition-colors">
        <i class="fas fa-times text-sm"></i>
      </button>
    </div>
    
    <input ref="fileInput"
           type="file" 
           :accept="accept"
           @change="handleFileSelect"
           class="hidden">
    
    <button @click="$refs.fileInput.click()" 
            type="button"
            class="btn-primary mt-4">
      <i class="fas fa-plus mr-2"></i>
      {{ currentFile ? 'Changer le fichier' : 'Sélectionner un fichier' }}
    </button>
  </div>
</template>

<script>
import { ref, watch, computed } from 'vue'
import Compressor from 'compressorjs'

export default {
  name: 'FileUpload',
  props: {
    accept: {
      type: String,
      default: 'image/*'
    },
    currentFile: {
      type: Object,
      default: null
    },
    label: {
      type: String,
      default: ''
    }
  },
  emits: ['file-uploaded'],
  setup(props, { emit }) {
    const fileInput = ref(null)
    const previewUrl = ref('')
    
    const isImage = computed(() => {
      if (!props.currentFile) return false
      if (props.currentFile instanceof Blob || props.currentFile instanceof File) {
        return props.currentFile.type?.startsWith('image/')
      }
      return props.currentFile.type?.startsWith('image/') || props.currentFile.url?.match(/\.(jpg|jpeg|png|gif|webp)$/i)
    })
    
    const handleFileSelect = (event) => {
      const file = event.target.files[0]
      if (!file) return
      
      // Vérification de la taille (5MB max)
      if (file.size > 5 * 1024 * 1024) {
        alert('Le fichier est trop volumineux. Taille maximum: 5MB')
        return
      }
      
      // Compression des images
      if (file.type.startsWith('image/')) {
        new Compressor(file, {
          quality: 0.8,
          maxWidth: 1200,
          maxHeight: 1200,
          success: (compressedFile) => {
            processFile(compressedFile)
          },
          error: (error) => {
            console.error('Erreur compression:', error)
            processFile(file)
          }
        })
      } else {
        processFile(file)
      }
    }
    
    const processFile = (file) => {
      // Créer l'aperçu
      if (file.type.startsWith('image/')) {
        const reader = new FileReader()
        reader.onload = (e) => {
          previewUrl.value = e.target.result
        }
        reader.readAsDataURL(file)
      }
      
      // Émettre le fichier
      emit('file-uploaded', file)
    }
    
    const removeFile = () => {
      previewUrl.value = ''
      fileInput.value.value = ''
      emit('file-uploaded', null)
    }
    
    // Charger l'aperçu du fichier existant
    watch(() => props.currentFile, (newFile) => {
      if (newFile && newFile.type?.startsWith('image/')) {
        // Vérifier si c'est un vrai fichier (Blob/File)
        if (newFile instanceof Blob || newFile instanceof File) {
          const reader = new FileReader()
          reader.onload = (e) => {
            previewUrl.value = e.target.result
          }
          reader.readAsDataURL(newFile)
        } else if (typeof newFile === 'object' && newFile.dataUrl) {
          // Si c'est un objet avec dataUrl (fichier sauvegardé avec aperçu)
          previewUrl.value = newFile.dataUrl
        } else if (typeof newFile === 'object' && newFile.url) {
          // Si c'est un objet avec une URL (fichier déjà uploadé)
          previewUrl.value = newFile.url
        } else {
          previewUrl.value = ''
        }
      } else {
        previewUrl.value = ''
      }
    }, { immediate: true })
    
    return {
      fileInput,
      previewUrl,
      isImage,
      handleFileSelect,
      removeFile
    }
  }
}
</script>