<template>
  <input
    type="text"
    :value="displayValue"
    @input="handleInput"
    @keydown="handleKeydown"
    style="border-width: 2px !important;"
    class="form-input h-12"
    :placeholder="placeholder"
    :class="{
      'border-red-600 dark:border-red-500': hasError,
      'border-green-600 dark:border-green-500': isValid,
      'border-gray-300 dark:border-gray-600': !hasError && !isValid
    }"
    v-bind="$attrs"
  />
</template>

<script>
import { computed, ref, watch } from 'vue'

export default {
  name: 'IdNumberInput',
  props: {
    modelValue: {
      type: String,
      default: ''
    },
    idType: {
      type: String,
      required: true,
      validator: value => ['cni', 'passport', 'elector', 'residence', 'driving_license', 'foreign_id'].includes(value)
    },
    hasError: {
      type: Boolean,
      default: false
    },
    isValid: {
      type: Boolean,
      default: false
    }
  },
  emits: ['update:modelValue'],
  setup(props, { emit }) {
    const error = ref('')
    
    // Format patterns for each ID type
    const formats = {
      cni: {
        pattern: /^[0-9]{4}-[0-9]{3}-[0-9]{4}$/,
        display: 'XXXX-XXX-XXXX',
        maxLength: 11,
        format: value => {
          const digits = value.replace(/\D/g, '').slice(0, 11)  // Enforce max length here
          const parts = []
          if (digits.length > 0) parts.push(digits.slice(0, 4))
          if (digits.length > 4) parts.push(digits.slice(4, 7))
          if (digits.length > 7) parts.push(digits.slice(7, 11))
          return parts.join('-')
        },
        clean: value => value.replace(/\D/g, '')
      },
      passport: {
        pattern: /^[A-Z0-9]{8}$/,
        display: 'XXXXXXXX',
        maxLength: 8,
        format: value => {
          return value.replace(/[^A-Z0-9]/g, '').toUpperCase().slice(0, 8)  // Enforce max length here
        },
        clean: value => value.replace(/[^A-Z0-9]/g, '').toUpperCase()
      },
      elector: {
        pattern: /^[0-9]{2}-[0-9]{2}-[0-9]{3}-[0-9]{2}-[0-9]{2}-[0-9]{2}-[0-9]{2}-[0-9]{5}$/,
        display: 'XX-XX-XXX-XX-XX-XX-XX-XXXXX',
        maxLength: 20,
        format: value => {
          const digits = value.replace(/\D/g, '').slice(0, 20)  // Enforce max length here
          const parts = []
          if (digits.length > 0) parts.push(digits.slice(0, 2))
          if (digits.length > 2) parts.push(digits.slice(2, 4))
          if (digits.length > 4) parts.push(digits.slice(4, 7))
          if (digits.length > 7) parts.push(digits.slice(7, 9))
          if (digits.length > 9) parts.push(digits.slice(9, 11))
          if (digits.length > 11) parts.push(digits.slice(11, 13))
          if (digits.length > 13) parts.push(digits.slice(13, 15))
          if (digits.length > 15) parts.push(digits.slice(15, 20))
          return parts.join('-')
        },
        clean: value => value.replace(/\D/g, '')
      },
      residence: {
        pattern: /^[A-Z0-9]{1,20}$/,
        display: 'Numéro de carte de séjour (max 20 caractères)',
        maxLength: 20,
        format: value => {
          return value.replace(/[^A-Z0-9]/g, '').toUpperCase().slice(0, 20)  // Enforce max length here
        },
        clean: value => value.replace(/[^A-Z0-9]/g, '').toUpperCase()
      },
      driving_license: {
        pattern: /^[A-Z0-9]{3} [A-Z0-9]{3} [A-Z0-9]{3}$/,
        display: 'XXX XXX XXX',
        maxLength: 9,
        format: value => {
          const chars = value.replace(/[^A-Z0-9]/g, '').toUpperCase().slice(0, 9)
          const parts = []
          if (chars.length > 0) parts.push(chars.slice(0, 3))
          if (chars.length > 3) parts.push(chars.slice(3, 6))
          if (chars.length > 6) parts.push(chars.slice(6, 9))
          return parts.join(' ')
        },
        clean: value => value.replace(/[^A-Z0-9]/g, '').toUpperCase()
      },
      foreign_id: {
        pattern: /^.+$/, // Pas de format spécifique
        display: 'Numéro carte d\'identité étrangère (max 20 caractères)',
        maxLength: 20,
        format: value => {
          return value.slice(0, 20)  // Juste limiter la longueur
        },
        clean: value => value
      }
    }
    
    // Current format configuration
    const currentFormat = computed(() => formats[props.idType] || formats.cni)
    
    // Formatted display value
    const displayValue = computed(() => {
      if (!props.modelValue || !currentFormat.value) return ''
      // Since modelValue is now already formatted, return it as-is
      return props.modelValue
    })
    
    // Placeholder based on ID type
    const placeholder = computed(() => currentFormat.value.display)
    
    // Handle input changes
    const handleInput = (event) => {
      const input = event.target.value
      const cleaned = currentFormat.value.clean(input)
      
      // Enforce maximum length in the cleaned value - prevent input beyond limit
      const truncated = cleaned.slice(0, currentFormat.value.maxLength)
      
      // Format the truncated value
      const formattedValue = currentFormat.value.format(truncated)
      
      // Check if input exceeds maximum length
      if (cleaned.length > currentFormat.value.maxLength) {
        // Truncate the input field value to prevent further typing
        event.target.value = formattedValue
        error.value = `Le numéro est trop long. Maximum ${currentFormat.value.maxLength} caractères.`
      } else {
        error.value = ''
      }
      
      // Only emit if the formatted value has changed
      if (formattedValue !== props.modelValue) {
        emit('update:modelValue', formattedValue)
      }
      
      // Validate format using the formatted value
      validateFormat(formattedValue)
    }

    // Handle keydown to prevent input when max length is reached
    const handleKeydown = (event) => {
      const currentValue = event.target.value
      const cleaned = currentFormat.value.clean(currentValue)
      
      // Allow navigation keys, backspace, delete, etc.
      const allowedKeys = ['Backspace', 'Delete', 'ArrowLeft', 'ArrowRight', 'ArrowUp', 'ArrowDown', 'Tab', 'Enter']
      
      // If max length is reached and the key is not a control key, prevent default
      if (cleaned.length >= currentFormat.value.maxLength && !allowedKeys.includes(event.key) && !event.ctrlKey && !event.metaKey) {
        event.preventDefault()
      }
    }
    
    // Validate the format
    const validateFormat = (formattedValue) => {
      if (!formattedValue || !currentFormat.value) {
        error.value = ''
        return
      }
      
      // Extract cleaned value to check length
      const cleanedValue = currentFormat.value.clean(formattedValue)
      
      if (cleanedValue.length > currentFormat.value.maxLength) {
        error.value = `Le numéro est trop long. Maximum ${currentFormat.value.maxLength} caractères.`
      } else if (!currentFormat.value.pattern.test(formattedValue)) {
        error.value = `Le format doit être ${currentFormat.value.display}`
      } else {
        error.value = ''
      }
    }
    
    // Watch for ID type changes to revalidate
    watch(() => props.idType, () => {
      if (props.modelValue) {
        // Clean the current value and reformat it with the new type
        const cleaned = currentFormat.value.clean(props.modelValue)
        const truncated = cleaned.slice(0, currentFormat.value.maxLength)
        const formattedValue = currentFormat.value.format(truncated)
        emit('update:modelValue', formattedValue)
        validateFormat(formattedValue)
      }
    })
    
    // Initial validation
    if (props.modelValue) {
      validateFormat(props.modelValue)
    }
    
    return {
      displayValue,
      placeholder,
      error,
      handleInput,
      handleKeydown
    }
  }
}
</script>