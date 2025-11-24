/**
 * Composable pour gérer le mode sombre
 * Détecte la préférence système et permet le toggle manuel
 */

import { ref, watch } from 'vue'

const STORAGE_KEY = 'theme-preference'
const DARK_CLASS = 'dark'

export function useDarkMode() {
  // null = auto (système), 'light' = clair, 'dark' = sombre
  const preference = ref(null)
  const isDark = ref(false)

  /**
   * Détecte la préférence système
   */
  const getSystemPreference = () => {
    if (window.matchMedia && window.matchMedia('(prefers-color-scheme: dark)').matches) {
      return 'dark'
    }
    return 'light'
  }

  /**
   * Applique le thème au document
   */
  const applyTheme = (theme) => {
    const root = document.documentElement
    
    if (theme === 'dark') {
      root.classList.add(DARK_CLASS)
      isDark.value = true
    } else {
      root.classList.remove(DARK_CLASS)
      isDark.value = false
    }

    // Mettre à jour la couleur de la barre d'état mobile
    const metaThemeColor = document.querySelector('meta[name="theme-color"]')
    if (metaThemeColor) {
      metaThemeColor.setAttribute('content', theme === 'dark' ? '#0f172a' : '#ffffff')
    }
  }

  /**
   * Charge la préférence depuis localStorage
   */
  const loadPreference = () => {
    const stored = localStorage.getItem(STORAGE_KEY)
    
    if (stored && ['light', 'dark', 'auto'].includes(stored)) {
      preference.value = stored === 'auto' ? null : stored
    } else {
      preference.value = null // Auto par défaut
    }

    updateTheme()
  }

  /**
   * Sauvegarde la préférence dans localStorage
   */
  const savePreference = (value) => {
    localStorage.setItem(STORAGE_KEY, value === null ? 'auto' : value)
  }

  /**
   * Met à jour le thème selon la préférence
   */
  const updateTheme = () => {
    const theme = preference.value || getSystemPreference()
    applyTheme(theme)
  }

  /**
   * Active le mode sombre
   */
  const enableDark = () => {
    preference.value = 'dark'
    savePreference('dark')
    updateTheme()
  }

  /**
   * Active le mode clair
   */
  const enableLight = () => {
    preference.value = 'light'
    savePreference('light')
    updateTheme()
  }

  /**
   * Active le mode automatique (suit le système)
   */
  const enableAuto = () => {
    preference.value = null
    savePreference('auto')
    updateTheme()
  }

  /**
   * Toggle entre clair et sombre
   */
  const toggle = () => {
    if (isDark.value) {
      enableLight()
    } else {
      enableDark()
    }
  }

  /**
   * Écoute les changements de préférence système
   */
  const watchSystemPreference = () => {
    if (window.matchMedia) {
      const mediaQuery = window.matchMedia('(prefers-color-scheme: dark)')
      
      mediaQuery.addEventListener('change', (e) => {
        if (preference.value === null) {
          // Uniquement si en mode auto
          applyTheme(e.matches ? 'dark' : 'light')
        }
      })
    }
  }

  // Watcher pour réactivité
  watch(preference, () => {
    updateTheme()
  })

  // Initialisation immédiate
  loadPreference()
  watchSystemPreference()

  return {
    preference,
    isDark,
    enableDark,
    enableLight,
    enableAuto,
    toggle,
    isAuto: () => preference.value === null
  }
}
