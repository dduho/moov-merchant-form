<template>
  <div class="space-y-4">
    <div class="border-2 border-gray-300 rounded-xl overflow-hidden">
      <div class="bg-gray-50 px-4 py-2 border-b border-gray-200">
        <div class="flex items-center justify-between">
          <span class="text-sm font-medium text-gray-700">Signature électronique</span>
          <div class="flex space-x-2">
            <button @click="clearSignature" 
                    type="button"
                    class="text-xs bg-gray-200 text-gray-700 px-3 py-1 rounded-md hover:bg-gray-300 transition-colors">
              <i class="fas fa-eraser mr-1"></i>
              Effacer
            </button>
          </div>
        </div>
      </div>
      
      <div class="relative bg-white" style="height: 200px;">
        <canvas ref="canvas" 
                class="absolute inset-0 w-full h-full cursor-crosshair"
                @mousedown="startDrawing"
                @mousemove="draw"
                @mouseup="stopDrawing"
                @mouseleave="stopDrawing"
                @touchstart="startDrawing"
                @touchmove="draw"
                @touchend="stopDrawing">
        </canvas>
        
        <!-- Ligne de signature -->
        <div class="absolute bottom-8 left-8 right-8 border-b border-gray-300"></div>
        <div class="absolute bottom-2 right-8 text-xs text-gray-400">
          Signez au-dessus de cette ligne
        </div>
      </div>
    </div>
    
    <!-- Aperçu de la signature -->
    <div v-if="signatureDataUrl" class="bg-gray-50 rounded-xl p-4">
      <p class="text-sm font-medium text-gray-700 mb-2">Aperçu de votre signature :</p>
      <img :src="signatureDataUrl" alt="Signature" class="max-w-xs border border-gray-200 rounded bg-white">
    </div>
    
    <p class="text-xs text-gray-500">
      <i class="fas fa-info-circle mr-1"></i>
      Dessinez votre signature avec votre souris ou votre doigt sur écran tactile.
    </p>
  </div>
</template>

<script>
import { ref, onMounted, watch } from 'vue'
import SignaturePadLib from 'signature_pad'

export default {
  name: 'SignaturePad',
  props: {
    currentSignature: {
      type: String,
      default: null
    }
  },
  emits: ['signature-saved'],
  setup(props, { emit }) {
    const canvas = ref(null)
    const signatureDataUrl = ref(props.currentSignature)
    
    let signaturePad = null
    let isDrawing = false
    
    const initSignaturePad = () => {
      if (!canvas.value) return
      
      // Ajuster la taille du canvas
      const rect = canvas.value.getBoundingClientRect()
      canvas.value.width = rect.width
      canvas.value.height = rect.height
      
      signaturePad = new SignaturePadLib(canvas.value, {
        backgroundColor: 'rgb(255, 255, 255)',
        penColor: 'rgb(0, 0, 0)',
        minWidth: 1,
        maxWidth: 2.5,
        throttle: 16,
        minPointDistance: 3
      })
      
      // Écouteur pour les changements
      signaturePad.addEventListener('endStroke', () => {
        if (!signaturePad.isEmpty()) {
          const dataUrl = signaturePad.toDataURL('image/png')
          signatureDataUrl.value = dataUrl
          emit('signature-saved', dataUrl)
        }
      })
      
      // Charger la signature existante
      if (props.currentSignature) {
        signaturePad.fromDataURL(props.currentSignature)
        signatureDataUrl.value = props.currentSignature
      }
    }
    
    const clearSignature = () => {
      if (signaturePad) {
        signaturePad.clear()
        signatureDataUrl.value = null
        emit('signature-saved', null)
      }
    }
    
    const startDrawing = (e) => {
      isDrawing = true
      e.preventDefault()
    }
    
    const draw = (e) => {
      if (!isDrawing) return
      e.preventDefault()
    }
    
    const stopDrawing = (e) => {
      isDrawing = false
      e.preventDefault()
    }
    
    // Redimensionnement responsive
    const handleResize = () => {
      if (canvas.value && signaturePad) {
        const data = signaturePad.toData()
        const rect = canvas.value.getBoundingClientRect()
        canvas.value.width = rect.width
        canvas.value.height = rect.height
        signaturePad.clear()
        signaturePad.fromData(data)
      }
    }
    
    watch(() => props.currentSignature, (newSignature) => {
      if (newSignature && signaturePad) {
        signaturePad.fromDataURL(newSignature)
        signatureDataUrl.value = newSignature
      }
    })
    
    onMounted(() => {
      initSignaturePad()
      window.addEventListener('resize', handleResize)
    })
    
    return {
      canvas,
      signatureDataUrl,
      clearSignature,
      startDrawing,
      draw,
      stopDrawing
    }
  }
}
</script>