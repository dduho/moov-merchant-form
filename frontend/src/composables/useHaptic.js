/**
 * Composable pour gérer le feedback haptique (vibrations)
 * Améliore l'expérience tactile sur mobile
 */

export function useHaptic() {
  /**
   * Vérifie si l'API Vibration est supportée
   */
  const isSupported = () => {
    return 'vibrate' in navigator
  }

  /**
   * Vibration légère (tap, click)
   */
  const light = () => {
    if (isSupported()) {
      navigator.vibrate(10)
    }
  }

  /**
   * Vibration moyenne (sélection, toggle)
   */
  const medium = () => {
    if (isSupported()) {
      navigator.vibrate(20)
    }
  }

  /**
   * Vibration forte (action importante, erreur)
   */
  const heavy = () => {
    if (isSupported()) {
      navigator.vibrate(50)
    }
  }

  /**
   * Double vibration (succès, validation)
   */
  const success = () => {
    if (isSupported()) {
      navigator.vibrate([100, 50, 100])
    }
  }

  /**
   * Triple vibration (erreur, échec)
   */
  const error = () => {
    if (isSupported()) {
      navigator.vibrate([50, 50, 50, 50, 50])
    }
  }

  /**
   * Vibration personnalisée
   * @param {number|number[]} pattern - Durée en ms ou pattern [vibrer, pause, vibrer, ...]
   */
  const custom = (pattern) => {
    if (isSupported()) {
      navigator.vibrate(pattern)
    }
  }

  /**
   * Arrête toute vibration en cours
   */
  const stop = () => {
    if (isSupported()) {
      navigator.vibrate(0)
    }
  }

  /**
   * Vibration de notification (longue attention)
   */
  const notification = () => {
    if (isSupported()) {
      navigator.vibrate([200, 100, 200])
    }
  }

  /**
   * Vibration de sélection (liste, scroll)
   */
  const selection = () => {
    if (isSupported()) {
      navigator.vibrate(5)
    }
  }

  return {
    isSupported,
    light,
    medium,
    heavy,
    success,
    error,
    custom,
    stop,
    notification,
    selection
  }
}
