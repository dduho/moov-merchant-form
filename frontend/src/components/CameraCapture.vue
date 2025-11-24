<template>
  <div class="camera-capture">
    <!-- Boutons de sélection -->
    <div class="flex gap-3 mb-4">
      <button
        @click="openCamera"
        type="button"
        class="flex-1 min-h-[44px] px-6 py-3 bg-orange-500 hover:bg-orange-600 text-white rounded-xl font-medium transition-colors flex items-center justify-center gap-2">
        <i class="fas fa-camera text-lg"></i>
        <span>Prendre une photo</span>
      </button>
      
      <button
        @click="$refs.fileInput.click()"
        type="button"
        class="flex-1 min-h-[44px] px-6 py-3 bg-gray-100 hover:bg-gray-200 text-gray-700 rounded-xl font-medium transition-colors flex items-center justify-center gap-2">
        <i class="fas fa-folder-open text-lg"></i>
        <span>Choisir un fichier</span>
      </button>
    </div>

    <!-- Input caméra (invisible) -->
    <input
      ref="cameraInput"
      type="file"
      accept="image/*"
      capture="environment"
      @change="handleCameraCapture"
      class="hidden">

    <!-- Input fichier (invisible) -->
    <input
      ref="fileInput"
      type="file"
      :accept="accept"
      @change="handleFileSelect"
      class="hidden">

    <!-- Zone de prévisualisation avec overlay pour document -->
    <div v-if="showCamera" class="relative bg-gray-900 rounded-xl overflow-hidden mb-4">
      <video ref="videoElement" autoplay playsinline class="w-full h-auto"></video>
      
      <!-- Overlay de guidage pour documents -->
      <div class="absolute inset-0 pointer-events-none">
        <div class="absolute inset-4 border-2 border-white border-dashed rounded-lg opacity-50"></div>
        <div class="absolute bottom-6 left-0 right-0 text-center text-white text-sm bg-black/50 py-2">
          Placez le document dans le cadre
        </div>
      </div>

      <!-- Contrôles caméra -->
      <div class="absolute top-4 right-4 flex gap-2">
        <!-- Flash toggle -->
        <button
          v-if="hasFlash"
          @click="toggleFlash"
          type="button"
          class="min-h-[44px] min-w-[44px] bg-black/50 hover:bg-black/70 text-white rounded-full transition-colors flex items-center justify-center">
          <i :class="flashEnabled ? 'fas fa-bolt' : 'fas fa-bolt-slash'"></i>
        </button>
        
        <!-- Fermer -->
        <button
          @click="closeCamera"
          type="button"
          class="min-h-[44px] min-w-[44px] bg-black/50 hover:bg-black/70 text-white rounded-full transition-colors flex items-center justify-center">
          <i class="fas fa-times"></i>
        </button>
      </div>

      <!-- Bouton capture -->
      <div class="absolute bottom-6 left-0 right-0 flex justify-center">
        <button
          @click="capturePhoto"
          type="button"
          class="min-h-[60px] min-w-[60px] bg-white rounded-full shadow-lg hover:scale-110 transition-transform">
          <i class="fas fa-camera text-gray-900 text-2xl"></i>
        </button>
      </div>
    </div>

    <!-- Aperçu du fichier capturé -->
    <div v-if="previewUrl && !showCamera" class="relative bg-gray-50 rounded-xl p-4">
      <img 
        v-lazy="previewUrl" 
        alt="Aperçu" 
        class="w-full h-auto rounded-lg shadow-md mb-3">
      
      <div class="flex gap-3">
        <button
          @click="removeFile"
          type="button"
          class="flex-1 min-h-[44px] px-4 py-2 bg-red-500 hover:bg-red-600 text-white rounded-lg font-medium transition-colors">
          <i class="fas fa-trash mr-2"></i>
          Supprimer
        </button>
        
        <button
          @click="openCamera"
          type="button"
          class="flex-1 min-h-[44px] px-4 py-2 bg-orange-500 hover:bg-orange-600 text-white rounded-lg font-medium transition-colors">
          <i class="fas fa-redo mr-2"></i>
          Reprendre
        </button>
      </div>

      <!-- Informations fichier -->
      <div class="mt-3 text-sm text-gray-600">
        <div class="flex items-center gap-2">
          <i class="fas fa-info-circle"></i>
          <span>Taille: {{ formatFileSize(currentFile?.size) }}</span>
          <span v-if="compressionRatio" class="text-green-600">
            (Compressé {{ compressionRatio }}%)
          </span>
        </div>
      </div>
    </div>

    <!-- Message d'aide -->
    <p v-if="!previewUrl && !showCamera" class="text-sm text-gray-500 text-center mt-2">
      <i class="fas fa-info-circle mr-1"></i>
      {{ label || 'Prenez une photo ou choisissez un fichier' }}
    </p>
  </div>
</template>

<script setup>
import { ref, computed, onBeforeUnmount } from 'vue'
import Compressor from 'compressorjs'
import { vLazy } from '../composables/useLazyImage'
import { useNotification } from '../composables/useNotification'

const props = defineProps({
  accept: {
    type: String,
    default: 'image/*'
  },
  label: {
    type: String,
    default: ''
  },
  maxSize: {
    type: Number,
    default: 5 * 1024 * 1024 // 5MB
  }
})

const emit = defineEmits(['file-captured'])

const { error } = useNotification()

const cameraInput = ref(null)
const fileInput = ref(null)
    const videoElement = ref(null)
    const previewUrl = ref('')
    const currentFile = ref(null)
    const showCamera = ref(false)
    const stream = ref(null)
    const hasFlash = ref(false)
    const flashEnabled = ref(false)
    const originalSize = ref(0)
    const compressedSize = ref(0)

    const compressionRatio = computed(() => {
      if (!originalSize.value || !compressedSize.value) return null
      const ratio = ((originalSize.value - compressedSize.value) / originalSize.value * 100).toFixed(0)
      return ratio > 0 ? ratio : null
    })

    const formatFileSize = (bytes) => {
      if (!bytes) return '0 KB'
      const kb = bytes / 1024
      const mb = kb / 1024
      return mb >= 1 ? `${mb.toFixed(2)} MB` : `${kb.toFixed(2)} KB`
    }

    // Ouvrir la caméra native (mobile)
    const openCamera = () => {
      if (cameraInput.value) {
        cameraInput.value.click()
      }
    }

    // Activer/désactiver le flash
    const toggleFlash = async () => {
      if (!stream.value) return
      
      const track = stream.value.getVideoTracks()[0]
      const capabilities = track.getCapabilities()
      
      if (capabilities.torch) {
        flashEnabled.value = !flashEnabled.value
        await track.applyConstraints({
          advanced: [{ torch: flashEnabled.value }]
        })
      }
    }

    // Fermer la caméra
    const closeCamera = () => {
      if (stream.value) {
        stream.value.getTracks().forEach(track => track.stop())
        stream.value = null
      }
      showCamera.value = false
      flashEnabled.value = false
    }

    // Capturer une photo depuis le flux vidéo
    const capturePhoto = () => {
      if (!videoElement.value) return

      const canvas = document.createElement('canvas')
      canvas.width = videoElement.value.videoWidth
      canvas.height = videoElement.value.videoHeight
      
      const context = canvas.getContext('2d')
      context.drawImage(videoElement.value, 0, 0)

      canvas.toBlob((blob) => {
        const file = new File([blob], `photo-${Date.now()}.jpg`, {
          type: 'image/jpeg',
          lastModified: Date.now()
        })
        
        closeCamera()
        processFile(file)
      }, 'image/jpeg', 0.95)
    }

    // Gérer la capture depuis input caméra
    const handleCameraCapture = (event) => {
      const file = event.target.files[0]
      if (!file) return
      processFile(file)
    }

    // Gérer la sélection de fichier
    const handleFileSelect = (event) => {
      const file = event.target.files[0]
      if (!file) return
      processFile(file)
    }

    // Traiter et compresser le fichier
    const processFile = (file) => {
      // Vérifier la taille
      if (file.size > props.maxSize) {
        error(`Le fichier est trop volumineux. Taille maximum: ${formatFileSize(props.maxSize)}`, 5000)
        return
      }

      originalSize.value = file.size

      // Détecter le type de connexion pour compression adaptative
      const connection = navigator.connection || navigator.mozConnection || navigator.webkitConnection
      const connectionType = connection?.effectiveType || '4g'
      
      const qualityMap = {
        'slow-2g': 0.6,
        '2g': 0.65,
        '3g': 0.75,
        '4g': 0.85,
        'wifi': 0.9
      }
      
      const quality = qualityMap[connectionType] || 0.8

      // Compresser les images
      if (file.type.startsWith('image/')) {
        new Compressor(file, {
          quality,
          maxWidth: 1920,
          maxHeight: 1920,
          // Ne pas convertir en WebP - le backend n'accepte que JPG/PNG/PDF
          // convertSize: 500000,
          // convertTypes: ['image/png', 'image/jpeg'],
          success: (compressedFile) => {
            compressedSize.value = compressedFile.size
            
            // Créer preview
            const reader = new FileReader()
            reader.onload = (e) => {
              previewUrl.value = e.target.result
              currentFile.value = compressedFile
              emit('file-captured', compressedFile)
            }
            reader.readAsDataURL(compressedFile)
          },
          error: (error) => {
            console.error('Erreur compression:', error)
            // Utiliser le fichier original si compression échoue
            createPreview(file)
          }
        })
      } else {
        createPreview(file)
      }
    }

    const createPreview = (file) => {
      compressedSize.value = file.size
      const reader = new FileReader()
      reader.onload = (e) => {
        previewUrl.value = e.target.result
        currentFile.value = file
        emit('file-captured', file)
      }
      reader.readAsDataURL(file)
    }

    const removeFile = () => {
      previewUrl.value = ''
      currentFile.value = null
      originalSize.value = 0
      compressedSize.value = 0
      if (cameraInput.value) cameraInput.value.value = ''
      if (fileInput.value) fileInput.value.value = ''
      emit('file-captured', null)
    }
    
    onBeforeUnmount(() => {
      closeCamera()
    })
</script>

<style scoped>
video {
  max-height: 400px;
  object-fit: cover;
}
</style>
