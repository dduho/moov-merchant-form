<script setup>
import { ref, computed, onMounted, watch } from 'vue'

const props = defineProps({
  show: {
    type: Boolean,
    default: false
  },
  type: {
    type: String,
    default: 'success', // success, error, warning, info
    validator: value => ['success', 'error', 'warning', 'info'].includes(value)
  },
  title: {
    type: String,
    required: true
  },
  message: {
    type: String,
    default: ''
  },
  duration: {
    type: Number,
    default: 5000 // 5 secondes
  },
  persistent: {
    type: Boolean,
    default: false
  }
})

const emit = defineEmits(['close'])

const visible = ref(false)
const timeoutId = ref(null)

const typeConfig = {
  success: {
    icon: 'fas fa-check-circle',
    bgColor: 'bg-green-50',
    borderColor: 'border-green-200',
    iconColor: 'text-green-400',
    titleColor: 'text-green-800',
    textColor: 'text-green-700'
  },
  error: {
    icon: 'fas fa-times-circle',
    bgColor: 'bg-red-50',
    borderColor: 'border-red-200',
    iconColor: 'text-red-400',
    titleColor: 'text-red-800',
    textColor: 'text-red-700'
  },
  warning: {
    icon: 'fas fa-exclamation-triangle',
    bgColor: 'bg-yellow-50',
    borderColor: 'border-yellow-200',
    iconColor: 'text-yellow-400',
    titleColor: 'text-yellow-800',
    textColor: 'text-yellow-700'
  },
  info: {
    icon: 'fas fa-info-circle',
    bgColor: 'bg-blue-50',
    borderColor: 'border-blue-200',
    iconColor: 'text-blue-400',
    titleColor: 'text-blue-800',
    textColor: 'text-blue-700'
  }
}

const config = computed(() => typeConfig[props.type])

const close = () => {
  visible.value = false
  if (timeoutId.value) {
    clearTimeout(timeoutId.value)
    timeoutId.value = null
  }
  setTimeout(() => {
    emit('close')
  }, 300) // Attendre la fin de l'animation
}

const startAutoClose = () => {
  if (!props.persistent && props.duration > 0) {
    timeoutId.value = setTimeout(() => {
      close()
    }, props.duration)
  }
}

watch(() => props.show, (newValue) => {
  if (newValue) {
    visible.value = true
    startAutoClose()
  } else {
    close()
  }
})

onMounted(() => {
  if (props.show) {
    visible.value = true
    startAutoClose()
  }
})
</script>

<template>
  <Transition
    enter-active-class="transform ease-out duration-300 transition"
    enter-from-class="translate-y-2 opacity-0 sm:translate-y-0 sm:translate-x-2"
    enter-to-class="translate-y-0 opacity-100 sm:translate-x-0"
    leave-active-class="transition ease-in duration-100"
    leave-from-class="opacity-100"
    leave-to-class="opacity-0"
  >
    <div
      v-if="visible"
      class="w-full"
    >
      <div
        :class="[
          'notification-toast rounded-lg p-4 shadow-lg border',
          config.bgColor,
          config.borderColor
        ]"
      >
        <div class="flex">
          <div class="flex-shrink-0">
            <i :class="[config.icon, config.iconColor]"></i>
          </div>
          <div class="ml-3 flex-1">
            <h3 :class="['text-sm font-medium', config.titleColor]">
              {{ title }}
            </h3>
            <div v-if="message" :class="['mt-1 text-sm', config.textColor]">
              {{ message }}
            </div>
          </div>
          <div class="ml-4 flex-shrink-0 flex">
            <button
              @click="close"
              :class="[
                'inline-flex text-gray-400 hover:text-gray-500 focus:outline-none focus:text-gray-500 transition ease-in-out duration-150',
                config.textColor
              ]"
            >
              <i class="fas fa-times text-sm"></i>
            </button>
          </div>
        </div>
      </div>
    </div>
  </Transition>
</template>

<style scoped>
/* Styles pour s'assurer que les notifications sont bien visibles */
.notification-toast {
  backdrop-filter: blur(10px);
  box-shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.1), 0 10px 10px -5px rgba(0, 0, 0, 0.04);
}
</style>