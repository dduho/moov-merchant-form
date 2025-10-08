<template>
  <input
    type="tel"
    :value="displayValue"
    @input="handleInput"
    @keydown="handleKeydown"
    @blur="handleBlur"
    @focus="handleFocus"
    class="form-input h-12"
    placeholder="+228 XX XX XX XX"
    :class="{ 'border-red-500': error }"
    inputmode="numeric"
    v-bind="$attrs"
  />
</template>

<script>
import { computed, ref, nextTick } from 'vue'

export default {
  name: 'PhoneInput',
  props: {
    modelValue: {
      type: String,
      default: ''
    },
    error: {
      type: Boolean,
      default: false
    }
  },
  emits: ['update:modelValue'],
  setup(props, { emit }) {
    const focused = ref(false)
    
    // Format the phone number for display
    const formatPhoneNumber = (digits) => {
      const groups = []
      for (let i = 0; i < Math.min(digits.length, 8); i += 2) {
        groups.push(digits.slice(i, i + 2))
      }
      return '+228 ' + groups.join(' ')
    }

    const displayValue = computed(() => {
      // Don't show anything when not focused and empty
      if (!props.modelValue && !focused.value) return ''
      
      // Just show prefix when focused and empty
      if (!props.modelValue && focused.value) return '+228 '
      
      const digits = props.modelValue ? props.modelValue.replace(/\D/g, '') : ''
      return formatPhoneNumber(digits)
    })
    
    const handleInput = (event) => {
      const input = event.target.value
      const currentPos = event.target.selectionStart || 0
      const prefixLength = 5 // length of "+228 "
      
      // Special handling for empty or prefix-only input
      if (input.length <= prefixLength || input === '+228' || input === '+22') {
        emit('update:modelValue', '')
        nextTick(() => {
          event.target.value = '+228 '
          event.target.setSelectionRange(prefixLength, prefixLength)
        })
        return
      }
      
      // Get the digits from the input, excluding the prefix
      let digits = input.replace(/\D/g, '')
      if (digits.startsWith('228')) {
        digits = digits.slice(3)
      }
      
      // Check if we already have 8 digits and trying to add more
      const currentDigits = props.modelValue || ''
      if (currentDigits.length >= 8 && digits.length > 8) {
        // Prevent further input by restoring the previous value
        nextTick(() => {
          event.target.value = displayValue.value
          event.target.setSelectionRange(currentPos, currentPos)
        })
        return
      }
      
      // Limit to 8 digits
      digits = digits.slice(0, 8)
      
      // Update the model with just the digits
      emit('update:modelValue', digits)
      
      // Calculate the new cursor position
      nextTick(() => {
        let newPos = currentPos
        
        // If we're before the prefix, move to after it
        if (newPos < prefixLength) {
          newPos = prefixLength
        } else {
          // Calculate spaces before the cursor
          const spacesBeforeCursor = input
            .slice(0, currentPos)
            .split('')
            .filter(char => char === ' ').length
            
          // Calculate the position relative to the digits
          const digitsBeforeCursor = input
            .slice(0, currentPos)
            .replace(/\D/g, '').length
            
          // New position is: prefix length + digits + spaces
          newPos = prefixLength + digitsBeforeCursor + Math.floor(digitsBeforeCursor / 2)
        }
        
        // Ensure the position is within bounds and not beyond the 8 digits + spaces
        const maxPos = prefixLength + Math.min(digits.length, 8) + Math.floor(Math.min(digits.length, 8) / 2)
        newPos = Math.min(newPos, maxPos)
        
        event.target.setSelectionRange(newPos, newPos)
      })
    }
    
    const handleFocus = (event) => {
      focused.value = true
      
      // Position cursor after prefix if empty
      if (!props.modelValue) {
        nextTick(() => {
          event.target.setSelectionRange(5, 5)
        })
      }
    }
    
    const handleBlur = () => {
      focused.value = false
      
      // Clear if incomplete and empty
      if (props.modelValue && props.modelValue.length === 0) {
        emit('update:modelValue', '')
      }
    }
    
    const handleKeydown = (event) => {
      const digits = (props.modelValue || '').replace(/\D/g, '')
      const cursorPos = event.target.selectionStart || 0
      const prefixLength = 5 // length of "+228 "
      
      // Special handling for backspace/delete in prefix area
      if ((event.keyCode === 8 || event.keyCode === 46) && // backspace or delete
          (cursorPos <= prefixLength || !digits.length)) {
        event.preventDefault()
        // If no digits, ensure we keep the prefix
        if (!digits.length) {
          nextTick(() => {
            event.target.value = '+228 '
            event.target.setSelectionRange(prefixLength, prefixLength)
          })
        }
        return
      }
      
      // Allow: backspace, delete, tab, escape, enter
      if ([46, 8, 9, 27, 13].indexOf(event.keyCode) !== -1 ||
        // Allow: Ctrl+A, Command+A
        (event.keyCode === 65 && (event.ctrlKey === true || event.metaKey === true)) ||
        // Allow: Ctrl+C, Command+C
        (event.keyCode === 67 && (event.ctrlKey === true || event.metaKey === true)) ||
        // Allow: Ctrl+V, Command+V
        (event.keyCode === 86 && (event.ctrlKey === true || event.metaKey === true)) ||
        // Allow: home, end, left, right, up, down
        (event.keyCode >= 35 && event.keyCode <= 40)) {
        return
      }
      
      // Block any input if we already have 8 digits and it's a number key
      if (digits.length >= 8 && 
          ((event.keyCode >= 48 && event.keyCode <= 57) || 
           (event.keyCode >= 96 && event.keyCode <= 105))) {
        event.preventDefault()
        return false
      }
    }

    return {
      displayValue,
      handleInput,
      handleFocus,
      handleBlur,
      handleKeydown
    }
  }
}
</script>