/**
 * Composable pour validation en temps réel avec indicateurs visuels
 * Fournit des méthodes de validation debounced et des états visuels
 */

import { ref, computed } from 'vue'

export function useValidation() {
  const validations = ref({})
  const validationTimers = ref({})

  /**
   * États de validation possibles
   */
  const ValidationState = {
    IDLE: null,         // Pas encore validé
    VALIDATING: 'validating', // En cours de validation
    VALID: 'valid',     // Valide
    INVALID: 'invalid'  // Invalide
  }

  /**
   * Debounce une fonction
   */
  const debounce = (fn, delay = 500) => {
    let timeoutId
    return (...args) => {
      clearTimeout(timeoutId)
      timeoutId = setTimeout(() => fn(...args), delay)
    }
  }

  /**
   * Valide un champ avec debounce
   */
  const validateField = (fieldName, value, validationFn, delay = 500) => {
    // Annuler la validation précédente
    if (validationTimers.value[fieldName]) {
      clearTimeout(validationTimers.value[fieldName])
    }

    // État en cours de validation
    validations.value[fieldName] = {
      state: ValidationState.VALIDATING,
      message: ''
    }

    // Validation debounced
    validationTimers.value[fieldName] = setTimeout(async () => {
      try {
        const result = await validationFn(value)
        
        if (result === true || result?.valid === true) {
          validations.value[fieldName] = {
            state: ValidationState.VALID,
            message: result?.message || ''
          }
        } else {
          validations.value[fieldName] = {
            state: ValidationState.INVALID,
            message: result?.message || result || 'Champ invalide'
          }
        }
      } catch (error) {
        validations.value[fieldName] = {
          state: ValidationState.INVALID,
          message: error.message || 'Erreur de validation'
        }
      }
    }, delay)
  }

  /**
   * Réinitialise la validation d'un champ
   */
  const resetField = (fieldName) => {
    if (validationTimers.value[fieldName]) {
      clearTimeout(validationTimers.value[fieldName])
      delete validationTimers.value[fieldName]
    }
    delete validations.value[fieldName]
  }

  /**
   * Réinitialise toutes les validations
   */
  const resetAll = () => {
    Object.keys(validationTimers.value).forEach(key => {
      clearTimeout(validationTimers.value[key])
    })
    validationTimers.value = {}
    validations.value = {}
  }

  /**
   * Obtient l'état d'un champ
   */
  const getFieldState = (fieldName) => {
    return validations.value[fieldName]?.state || ValidationState.IDLE
  }

  /**
   * Obtient le message d'un champ
   */
  const getFieldMessage = (fieldName) => {
    return validations.value[fieldName]?.message || ''
  }

  /**
   * Classes CSS pour les inputs selon l'état
   */
  const getInputClasses = (fieldName) => {
    const state = getFieldState(fieldName)
    
    return {
      'border-gray-300 dark:border-gray-600': state === ValidationState.IDLE,
      'border-blue-500 dark:border-blue-400': state === ValidationState.VALIDATING,
      'border-green-500 dark:border-green-400': state === ValidationState.VALID,
      'border-red-500 dark:border-red-400': state === ValidationState.INVALID
    }
  }

  /**
   * Icône selon l'état
   */
  const getFieldIcon = (fieldName) => {
    const state = getFieldState(fieldName)
    
    switch (state) {
      case ValidationState.VALIDATING:
        return { icon: 'fa-spinner fa-spin', color: 'text-blue-500' }
      case ValidationState.VALID:
        return { icon: 'fa-check-circle', color: 'text-green-500' }
      case ValidationState.INVALID:
        return { icon: 'fa-times-circle', color: 'text-red-500' }
      default:
        return null
    }
  }

  /**
   * Vérifie si tous les champs sont valides
   */
  const isAllValid = computed(() => {
    const states = Object.values(validations.value)
    return states.length > 0 && states.every(v => v.state === ValidationState.VALID)
  })

  /**
   * Compte des champs par état
   */
  const validationStats = computed(() => {
    const stats = {
      total: 0,
      validating: 0,
      valid: 0,
      invalid: 0,
      idle: 0
    }

    Object.values(validations.value).forEach(v => {
      stats.total++
      if (v.state === ValidationState.VALIDATING) stats.validating++
      else if (v.state === ValidationState.VALID) stats.valid++
      else if (v.state === ValidationState.INVALID) stats.invalid++
      else stats.idle++
    })

    return stats
  })

  /**
   * Score de complétude (0-100)
   */
  const completionScore = computed(() => {
    if (validationStats.value.total === 0) return 0
    return Math.round((validationStats.value.valid / validationStats.value.total) * 100)
  })

  // ===== VALIDATEURS PRÉDÉFINIS =====

  /**
   * Valide un email
   */
  const validateEmail = (email) => {
    if (!email) return { valid: false, message: 'Email requis' }
    
    const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/
    if (!emailRegex.test(email)) {
      return { valid: false, message: 'Format email invalide' }
    }
    
    return { valid: true, message: 'Email valide' }
  }

  /**
   * Valide un numéro de téléphone (Togo)
   */
  const validatePhone = (phone) => {
    if (!phone) return { valid: false, message: 'Téléphone requis' }
    
    // Format Togo: +228 XX XX XX XX ou 228XXXXXXXX ou XXXXXXXX
    const cleanPhone = phone.replace(/[\s\-\(\)]/g, '')
    const togoRegex = /^(\+?228)?[79]\d{7}$/
    
    if (!togoRegex.test(cleanPhone)) {
      return { valid: false, message: 'Format téléphone invalide (ex: 90123456)' }
    }
    
    return { valid: true, message: 'Téléphone valide' }
  }

  /**
   * Valide un NIF (Numéro d'Identification Fiscale)
   */
  const validateNIF = async (nif) => {
    if (!nif) return { valid: false, message: 'NIF requis' }
    
    // Format basique (à adapter selon le format réel)
    if (nif.length < 8) {
      return { valid: false, message: 'NIF trop court (min 8 caractères)' }
    }
    
    // TODO: Appel API pour vérifier le NIF
    // const response = await fetch(`/api/validate-nif/${nif}`)
    // return response.json()
    
    return { valid: true, message: 'NIF valide' }
  }

  /**
   * Valide un CFE (Centre de Formalités des Entreprises)
   */
  const validateCFE = async (cfe) => {
    if (!cfe) return { valid: false, message: 'CFE requis' }
    
    // Format basique (à adapter selon le format réel)
    if (cfe.length < 6) {
      return { valid: false, message: 'CFE trop court (min 6 caractères)' }
    }
    
    // TODO: Appel API pour vérifier le CFE
    // const response = await fetch(`/api/validate-cfe/${cfe}`)
    // return response.json()
    
    return { valid: true, message: 'CFE valide' }
  }

  /**
   * Valide qu'un champ n'est pas vide
   */
  const validateRequired = (value, fieldName = 'Ce champ') => {
    if (!value || (typeof value === 'string' && value.trim() === '')) {
      return { valid: false, message: `${fieldName} est requis` }
    }
    return { valid: true }
  }

  /**
   * Valide une longueur minimale
   */
  const validateMinLength = (value, min, fieldName = 'Ce champ') => {
    if (!value || value.length < min) {
      return { valid: false, message: `${fieldName} doit contenir au moins ${min} caractères` }
    }
    return { valid: true }
  }

  /**
   * Valide une longueur maximale
   */
  const validateMaxLength = (value, max, fieldName = 'Ce champ') => {
    if (value && value.length > max) {
      return { valid: false, message: `${fieldName} ne peut pas dépasser ${max} caractères` }
    }
    return { valid: true }
  }

  /**
   * Valide un format de date
   */
  const validateDate = (dateString) => {
    if (!dateString) return { valid: false, message: 'Date requise' }
    
    const date = new Date(dateString)
    if (isNaN(date.getTime())) {
      return { valid: false, message: 'Format de date invalide' }
    }
    
    return { valid: true, message: 'Date valide' }
  }

  /**
   * Valide qu'une date est dans le passé
   */
  const validatePastDate = (dateString, fieldName = 'Cette date') => {
    const validation = validateDate(dateString)
    if (!validation.valid) return validation
    
    const date = new Date(dateString)
    const now = new Date()
    
    if (date > now) {
      return { valid: false, message: `${fieldName} doit être dans le passé` }
    }
    
    return { valid: true, message: 'Date valide' }
  }

  /**
   * Valide qu'une date est dans le futur
   */
  const validateFutureDate = (dateString, fieldName = 'Cette date') => {
    const validation = validateDate(dateString)
    if (!validation.valid) return validation
    
    const date = new Date(dateString)
    const now = new Date()
    
    if (date < now) {
      return { valid: false, message: `${fieldName} doit être dans le futur` }
    }
    
    return { valid: true, message: 'Date valide' }
  }

  /**
   * Valide l'âge minimum (pour date de naissance)
   */
  const validateMinAge = (dateString, minAge = 18) => {
    const validation = validateDate(dateString)
    if (!validation.valid) return validation
    
    const birthDate = new Date(dateString)
    const today = new Date()
    let age = today.getFullYear() - birthDate.getFullYear()
    const monthDiff = today.getMonth() - birthDate.getMonth()
    
    if (monthDiff < 0 || (monthDiff === 0 && today.getDate() < birthDate.getDate())) {
      age--
    }
    
    if (age < minAge) {
      return { valid: false, message: `Âge minimum requis: ${minAge} ans` }
    }
    
    return { valid: true, message: `Âge valide (${age} ans)` }
  }

  return {
    // États
    validations,
    ValidationState,
    
    // Méthodes principales
    validateField,
    resetField,
    resetAll,
    
    // Getters
    getFieldState,
    getFieldMessage,
    getInputClasses,
    getFieldIcon,
    
    // Computed
    isAllValid,
    validationStats,
    completionScore,
    
    // Validateurs prédéfinis
    validateEmail,
    validatePhone,
    validateNIF,
    validateCFE,
    validateRequired,
    validateMinLength,
    validateMaxLength,
    validateDate,
    validatePastDate,
    validateFutureDate,
    validateMinAge
  }
}
