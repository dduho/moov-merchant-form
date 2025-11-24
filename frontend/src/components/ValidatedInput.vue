<template>
  <div class="form-group">
    <label v-if="label" class="form-label flex items-center justify-between">
      <span>
        {{ label }}
        <span v-if="required" class="text-red-500">*</span>
      </span>
      
      <!-- Icône de statut -->
      <span v-if="fieldIcon" class="transition-all duration-200">
        <i :class="['fas', fieldIcon.icon, fieldIcon.color, 'text-sm']"></i>
      </span>
    </label>

    <div class="relative">
      <!-- Select -->
      <select
        v-if="type === 'select'"
        :id="inputId"
        v-model="localValue"
        :required="required"
        :disabled="disabled"
        :class="inputClasses"
        class="form-input h-12 w-full"
        @change="handleInput"
        @blur="handleBlur"
        @focus="handleFocus"
      >
        <slot></slot>
      </select>

      <!-- Textarea -->
      <textarea
        v-else-if="type === 'textarea'"
        :id="inputId"
        v-model="localValue"
        :placeholder="placeholder"
        :required="required"
        :disabled="disabled"
        :readonly="readonly"
        :autocomplete="autocomplete"
        :maxlength="maxlength"
        :rows="rows"
        :class="inputClasses"
        class="form-input w-full min-h-[100px]"
        @input="handleInput"
        @blur="handleBlur"
        @focus="handleFocus"
      ></textarea>

      <!-- Input classique -->
      <input
        v-else
        :id="inputId"
        v-model="localValue"
        :type="type"
        :placeholder="placeholder"
        :required="required"
        :disabled="disabled"
        :readonly="readonly"
        :autocomplete="autocomplete"
        :inputmode="inputmode"
        :maxlength="maxlength"
        :min="min"
        :max="max"
        :pattern="pattern"
        :class="inputClasses"
        class="form-input h-12 w-full pr-10"
        @input="handleInput"
        @blur="handleBlur"
        @focus="handleFocus"
      />

      <!-- Icône de statut dans l'input (sauf pour select et textarea) -->
      <div v-if="fieldIcon && showIconInInput && type !== 'select' && type !== 'textarea'" class="absolute right-3 top-1/2 -translate-y-1/2 pointer-events-none">
        <i :class="['fas', fieldIcon.icon, fieldIcon.color]"></i>
      </div>
    </div>

    <!-- Message de validation -->
    <transition name="fade">
      <p v-if="validationMessage" :class="messageClasses" class="mt-1 text-sm flex items-center space-x-1">
        <i v-if="fieldIcon" :class="['fas', fieldIcon.icon, 'text-xs']"></i>
        <span>{{ validationMessage }}</span>
      </p>
    </transition>

    <!-- Message d'aide optionnel -->
    <p v-if="helpText && !validationMessage" class="mt-1 text-xs text-gray-500 dark:text-gray-400">
      {{ helpText }}
    </p>
  </div>
</template>

<script setup>
import { ref, computed, watch } from 'vue'
import { useValidation } from '../composables/useValidation'

const props = defineProps({
  modelValue: {
    type: [String, Number],
    default: ''
  },
  label: {
    type: String,
    default: ''
  },
  type: {
    type: String,
    default: 'text'
  },
  placeholder: {
    type: String,
    default: ''
  },
  required: {
    type: Boolean,
    default: false
  },
  disabled: {
    type: Boolean,
    default: false
  },
  readonly: {
    type: Boolean,
    default: false
  },
  autocomplete: {
    type: String,
    default: 'off'
  },
  inputmode: {
    type: String,
    default: 'text'
  },
  maxlength: {
    type: [String, Number],
    default: null
  },
  rows: {
    type: [String, Number],
    default: 3
  },
  min: {
    type: [String, Number],
    default: null
  },
  max: {
    type: [String, Number],
    default: null
  },
  pattern: {
    type: String,
    default: null
  },
  fieldName: {
    type: String,
    required: true
  },
  validationFn: {
    type: Function,
    default: null
  },
  validateOnInput: {
    type: Boolean,
    default: true
  },
  validateOnBlur: {
    type: Boolean,
    default: true
  },
  debounceDelay: {
    type: Number,
    default: 500
  },
  showIconInInput: {
    type: Boolean,
    default: true
  },
  helpText: {
    type: String,
    default: ''
  },
  error: {
    type: String,
    default: ''
  }
})

const emit = defineEmits(['update:modelValue', 'validation-change'])

const { 
  validateField, 
  getFieldState, 
  getFieldMessage, 
  getInputClasses, 
  getFieldIcon,
  ValidationState 
} = useValidation()

const localValue = ref(props.modelValue)
const isFocused = ref(false)
const inputId = computed(() => `input-${props.fieldName}`)

// Synchroniser avec v-model et valider si déjà rempli
watch(() => props.modelValue, (newVal) => {
  // Éviter les boucles infinies en vérifiant si la valeur a réellement changé
  if (localValue.value !== newVal) {
    localValue.value = newVal
    // Si la valeur est remplie (données chargées), valider
    if (newVal && props.validationFn) {
      validateField(props.fieldName, newVal, props.validationFn, 0)
    }
  }
})

watch(localValue, (newVal) => {
  emit('update:modelValue', newVal)
})

// Validation automatique
const handleInput = () => {
  if (props.validateOnInput && props.validationFn) {
    validateField(props.fieldName, localValue.value, props.validationFn, props.debounceDelay)
  }

  // Émettre l'état de validation
  emitValidationState()
}

const handleBlur = () => {
  isFocused.value = false
  
  // Toujours valider au blur
  if (props.validationFn) {
    validateField(props.fieldName, localValue.value, props.validationFn, 0)
  }

  emitValidationState()
}

// Méthode pour forcer la validation (appelée depuis le parent)
const forceValidate = () => {
  if (props.validationFn) {
    validateField(props.fieldName, localValue.value, props.validationFn, 0)
  }
}

const handleFocus = () => {
  isFocused.value = true
}

// Exposer la méthode forceValidate
defineExpose({
  forceValidate
})

const emitValidationState = () => {
  const state = getFieldState(props.fieldName)
  const message = getFieldMessage(props.fieldName)
  
  emit('validation-change', {
    fieldName: props.fieldName,
    state,
    message,
    isValid: state === ValidationState.VALID
  })
}

// Classes dynamiques
const inputClasses = computed(() => {
  const state = getFieldState(props.fieldName)
  const hasError = props.error || state === ValidationState.INVALID

  // Base dark mode visual requirements requested:
  // dark:border-red-500, dark:border-green-500, dark:border-gray-600
  // dark:bg-gray-700 dark:text-white dark:placeholder:text-gray-500
  // Apply conditional border colors matching validation states while keeping external error priority.
  return {
    // Background & text (always applied so component looks correct in dark mode)
    'dark:bg-gray-700 dark:text-white dark:placeholder:text-gray-500': true,

    // Neutral / idle state
    'border-gray-300 dark:border-gray-600': state === ValidationState.IDLE && !hasError,
    // Validating state (spinner shown)
    'border-blue-500 dark:border-blue-400': state === ValidationState.VALIDATING && !hasError,
    // Valid state
    'border-green-600 dark:border-green-500': state === ValidationState.VALID && !hasError,
    // Error state (external or validation)
    'border-red-600 dark:border-red-500': hasError,

    // Disabled appearance
    'opacity-50 cursor-not-allowed': props.disabled,

    // Focus ring only when idle (not yet validated and no error)
    'ring-2 ring-orange-500/20': isFocused.value && state === ValidationState.IDLE && !hasError,

    // Smooth transition for color/border changes
    'transition-colors duration-200': true
  }
})

const messageClasses = computed(() => {
  // If external error, always show red
  if (props.error) {
    return {
      'text-red-600 dark:text-red-400': true
    }
  }
  
  const state = getFieldState(props.fieldName)
  
  return {
    'text-blue-600 dark:text-blue-400': state === ValidationState.VALIDATING,
    'text-green-600 dark:text-green-400': state === ValidationState.VALID,
    'text-red-600 dark:text-red-400': state === ValidationState.INVALID
  }
})

const fieldIcon = computed(() => {
  // If external error, show error icon
  if (props.error) {
    return 'exclamation-circle'
  }
  return getFieldIcon(props.fieldName)
})

const validationMessage = computed(() => {
  // Prioritize external error message
  return props.error || getFieldMessage(props.fieldName)
})
</script>

<style scoped>
.fade-enter-active,
.fade-leave-active {
  transition: opacity 0.2s ease;
}

.fade-enter-from,
.fade-leave-to {
  opacity: 0;
}
/* Label styling (previously only defined in parent component and lost due to scoped boundaries)
   Ensures consistent dark mode appearance for all labels, including those inside ValidatedInput. */
.form-label {
  margin-bottom: 0.25rem; /* mb-1 */
  font-size: 0.875rem; /* text-sm */
  line-height: 1.25rem;
  font-weight: 500; /* font-medium */
  color: rgb(17 24 39 / 1); /* text-gray-900 */
}
.dark .form-label {
  color: rgb(255 255 255 / 1); /* text-white in dark mode */
}
</style>
