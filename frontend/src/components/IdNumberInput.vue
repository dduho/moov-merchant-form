<template>
  <div>
    <input
      type="text"
      :value="displayValue"
      @input="handleInput"
      class="form-input h-12"
      :placeholder="placeholder"
      :class="{ 'border-red-500': !!error }"
      v-bind="$attrs"
    />
    <p v-if="error" class="mt-1 text-sm text-red-600">
      {{ error }}
    </p>
  </div>
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
      validator: value => ['cni', 'passport', 'elector', 'residence'].includes(value)
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
        pattern: /^[A-Z0-9]{10}$/,
        display: 'XXXXXXXXXX',
        maxLength: 10,
        format: value => {
          return value.replace(/[^A-Z0-9]/g, '').toUpperCase().slice(0, 10)  // Enforce max length here
        },
        clean: value => value.replace(/[^A-Z0-9]/g, '').toUpperCase()
      }
    }
    
    // Current format configuration
    const currentFormat = computed(() => formats[props.idType] || formats.cni)
    
    // Formatted display value
    const displayValue = computed(() => {
      if (!props.modelValue || !currentFormat.value) return ''
      return currentFormat.value.format(props.modelValue)
    })
    
    // Placeholder based on ID type
    const placeholder = computed(() => currentFormat.value.display)
    
    // Handle input changes
    const handleInput = (event) => {
      const input = event.target.value
      const cleaned = currentFormat.value.clean(input)
      
      // Check if input exceeds maximum length
      if (cleaned.length > currentFormat.value.maxLength) {
        error.value = `Le numéro est trop long. Maximum ${currentFormat.value.maxLength} caractères.`
        return
      } else {
        error.value = ''
      }
      
      // Enforce maximum length in the cleaned value
      const truncated = cleaned.slice(0, currentFormat.value.maxLength)
      
      // Only emit if the value has changed
      if (truncated !== props.modelValue) {
        emit('update:modelValue', truncated)
      }
      
      // Validate format
      validateFormat(cleaned)
    }
    
    // Validate the format
    const validateFormat = (value) => {
      if (!value || !currentFormat.value) {
        error.value = ''
        return
      }
      
      if (value.length > currentFormat.value.maxLength) {
        error.value = `Le numéro est trop long. Maximum ${currentFormat.value.maxLength} caractères.`
      } else if (!currentFormat.value.pattern.test(currentFormat.value.format(value))) {
        error.value = `Le format doit être ${currentFormat.value.display}`
      } else {
        error.value = ''
      }
    }
    
    // Watch for ID type changes to revalidate
    watch(() => props.idType, () => {
      if (props.modelValue) {
        const cleaned = currentFormat.value.clean(props.modelValue)
        emit('update:modelValue', cleaned)
        validateFormat(cleaned)
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
      handleInput
    }
  }
}
</script>