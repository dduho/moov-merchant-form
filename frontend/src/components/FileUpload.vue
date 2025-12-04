<template>
  <div class="border-2 border-dashed border-gray-300 rounded-xl p-6 text-center hover:border-orange-400 transition-colors">
    <div v-if="(!files.length) && !previewUrl">
      <i class="fas fa-cloud-upload-alt text-4xl text-gray-400 mb-4"></i>
      <p class="text-gray-600 mb-2">{{ label || 'Cliquez pour sélectionner un fichier' }}</p>
      <p class="text-sm text-gray-400">Formats acceptés: JPG, PNG, PDF (Max: 5MB)</p>
    </div>
    
    <!-- Aperçu des fichiers -->
    <div v-if="files.length" class="relative space-y-2">
      <div v-for="(f, idx) in files" :key="idx" class="relative">
        <img v-if="isImageFile(f)" :src="f.dataUrl || f.url" alt="Aperçu" class="max-w-full h-48 object-cover mx-auto rounded-lg">
        <div v-else class="flex items-center justify-center h-48 bg-gray-100 rounded-lg">
          <i class="fas fa-file-alt text-4xl text-gray-400"></i>
          <p class="ml-2 text-gray-600">{{ f.name }}</p>
        </div>

        <!-- Bouton supprimer par fichier -->
        <button @click="removeFileAt(idx)" 
                type="button"
                class="absolute top-2 right-2 bg-red-500 text-white rounded-full w-8 h-8 flex items-center justify-center hover:bg-red-600 transition-colors">
          <i class="fas fa-times text-sm"></i>
        </button>
      </div>
    </div>
    
        <input ref="fileInput"
          type="file" 
          :accept="accept"
          @change="handleFileSelect"
          :multiple="multiple"
          class="hidden">
    
    <button @click="$refs.fileInput.click()" 
            type="button"
            class="btn-primary mt-4">
      <i class="fas fa-plus mr-2"></i>
      {{ files.length ? (files.length + ' fichier(s) sélectionné(s) - Modifier') : 'Sélectionner un fichier' }}
    </button>
  </div>
</template>

<script setup>
import { ref, watch, computed } from 'vue'
import Compressor from 'compressorjs'
import { vLazy } from '../composables/useLazyImage'
import { useNotification } from '../composables/useNotification'

const props = defineProps({
  accept: {
    type: String,
    default: 'image/*'
  },
  currentFile: {
    type: [Object, Array],
    default: null
  },
  label: {
    type: String,
    default: ''
  },
  multiple: {
    type: Boolean,
    default: false
  },
  maxFiles: {
    type: Number,
    default: 3
  }
})

const emit = defineEmits(['file-uploaded', 'file-remove'])

const { error } = useNotification()

const fileInput = ref(null)
const previewUrl = ref('')
const files = ref([])
    
    const isImage = computed(() => {
      return files.value.length > 0
    })

    const isImageFile = (f) => {
      if (!f) return false
      if (f instanceof File || f instanceof Blob) return f.type?.startsWith('image/')
      return (f.type && f.type.startsWith('image/')) || (f.url && f.url.match(/\.(jpg|jpeg|png|gif|webp)$/i))
    }
    
    const handleFileSelect = (event) => {
      const selected = Array.from(event.target.files || [])
      if (!selected.length) return

      // Respecter la limite de fichiers
      if ((files.value.length + selected.length) > props.maxFiles) {
        error(`Vous pouvez sélectionner au maximum ${props.maxFiles} fichiers`, 5000)
        fileInput.value.value = ''
        return
      }

      // Pour chaque fichier, valider et éventuellement compresser
      selected.forEach((file) => {
        if (!file) return
        if (file.size > 5 * 1024 * 1024) {
          error('Le fichier est trop volumineux. Taille maximum: 5MB', 5000)
          return
        }

        if (file.type.startsWith('image/')) {
          const connection = navigator.connection || navigator.mozConnection || navigator.webkitConnection
          const connectionType = connection?.effectiveType || '4g'
          const qualityMap = { 'slow-2g': 0.6, '2g': 0.65, '3g': 0.75, '4g': 0.85, 'wifi': 0.9 }
          const quality = qualityMap[connectionType] || 0.8

          new Compressor(file, {
            quality,
            maxWidth: 1920,
            maxHeight: 1920,
            success: (compressedFile) => {
              processFile(compressedFile)
            },
            error: () => processFile(file)
          })
        } else {
          processFile(file)
        }
      })
    }
    
    const processFile = (file) => {
      const fileData = {
        name: file.name,
        type: file.type,
        size: file.size,
        lastModified: file.lastModified || Date.now(),
        file: file
      }

      if (file.type.startsWith('image/')) {
        const reader = new FileReader()
        reader.onload = (e) => {
          fileData.dataUrl = e.target.result
          files.value.push(fileData)
          emit('file-uploaded', files.value.slice())
        }
        reader.readAsDataURL(file)
      } else {
        files.value.push(fileData)
        emit('file-uploaded', files.value.slice())
      }
    }
    
    const removeFileAt = (idx) => {
      const fileToRemove = files.value[idx]

      // Si le fichier a un ID, demander au parent de le supprimer via l'API
      if (fileToRemove && fileToRemove.id) {
        emit('file-remove', fileToRemove.id)
      }

      // Retirer le fichier de l'affichage local
      files.value.splice(idx, 1)
      fileInput.value.value = ''

      // Émettre la liste mise à jour au parent
      emit('file-uploaded', files.value.slice())
    }
    
    // Charger l'aperçu du fichier existant
    watch(() => props.currentFile, (newFile) => {
      // Supports both single object and array
      files.value = []
      if (!newFile) return
      const arr = Array.isArray(newFile) ? newFile : [newFile]
      arr.forEach((f) => {
        if (!f) return
        const fileData = {
          id: f.id || null,  // ID du document en base de données
          name: f.name || f.original_name || 'document',
          type: f.type || f.mime_type || '',
          size: f.size || f.file_size || 0,
          lastModified: f.lastModified || Date.now(),
          file: f.file || null,
          url: f.url || f.file_url || null,
          dataUrl: f.dataUrl || null,
          uploaded: !!f.uploaded
        }
        files.value.push(fileData)
      })
    }, { immediate: true })
</script>