/**
 * Composable pour le Pull-to-Refresh
 * Détecte le geste de "tirer pour rafraîchir" sur mobile
 */

import { ref } from 'vue'

export function usePullToRefresh(container, onRefresh, options = {}) {
  const {
    threshold = 80,           // Distance minimum pour déclencher le refresh (px)
    resistance = 2.5,         // Résistance du pull (plus élevé = plus difficile)
    maxPullDistance = 120,    // Distance max du pull (px)
    refreshTimeout = 1000     // Durée minimum de l'animation de refresh (ms)
  } = options

  const isPulling = ref(false)
  const isRefreshing = ref(false)
  const pullDistance = ref(0)

  let startY = 0
  let currentY = 0
  let scrollTop = 0

  /**
   * Début du touch
   */
  const handleTouchStart = (e) => {
    const element = container.value?.$el || container.value || container
    scrollTop = element.scrollTop

    // Uniquement si on est en haut de la page
    if (scrollTop === 0) {
      startY = e.touches[0].clientY
      isPulling.value = true
    }
  }

  /**
   * Déplacement du touch
   */
  const handleTouchMove = (e) => {
    if (!isPulling.value || isRefreshing.value) return

    const element = container.value?.$el || container.value || container
    
    // Vérifier qu'on est toujours en haut
    if (element.scrollTop > 0) {
      isPulling.value = false
      pullDistance.value = 0
      return
    }

    currentY = e.touches[0].clientY
    const delta = currentY - startY

    // Pull vers le bas uniquement
    if (delta > 0) {
      // Appliquer la résistance
      const distance = Math.min(delta / resistance, maxPullDistance)
      pullDistance.value = distance

      // Empêcher le scroll par défaut
      if (distance > 10) {
        e.preventDefault()
      }
    }
  }

  /**
   * Fin du touch
   */
  const handleTouchEnd = async () => {
    if (!isPulling.value || isRefreshing.value) return

    isPulling.value = false

    // Déclencher le refresh si seuil atteint
    if (pullDistance.value >= threshold) {
      isRefreshing.value = true

      // Vibration feedback
      if (navigator.vibrate) {
        navigator.vibrate([50, 50, 50])
      }

      try {
        // Exécuter le callback de refresh
        await Promise.all([
          onRefresh(),
          new Promise(resolve => setTimeout(resolve, refreshTimeout))
        ])
      } catch (error) {
        console.error('Pull-to-refresh error:', error)
      } finally {
        isRefreshing.value = false
        pullDistance.value = 0
      }
    } else {
      // Annuler le pull
      pullDistance.value = 0
    }
  }

  // Initialisation immédiate
  const element = container.value?.$el || container.value || container

  if (element && element.addEventListener) {
    element.addEventListener('touchstart', handleTouchStart, { passive: false })
    element.addEventListener('touchmove', handleTouchMove, { passive: false })
    element.addEventListener('touchend', handleTouchEnd, { passive: true })
  }

  // Fonction de cleanup
  const cleanup = () => {
    if (element && element.removeEventListener) {
      element.removeEventListener('touchstart', handleTouchStart)
      element.removeEventListener('touchmove', handleTouchMove)
      element.removeEventListener('touchend', handleTouchEnd)
    }
  }

  /**
   * Déclencher un refresh programmatiquement
   */
  const triggerRefresh = async () => {
    if (isRefreshing.value) return

    isRefreshing.value = true
    pullDistance.value = threshold

    try {
      await onRefresh()
    } finally {
      isRefreshing.value = false
      pullDistance.value = 0
    }
  }

  /**
   * Style pour le conteneur (à appliquer avec :style)
   */
  const containerStyle = () => ({
    transform: isPulling.value || isRefreshing.value 
      ? `translateY(${pullDistance.value}px)` 
      : 'translateY(0)',
    transition: isPulling.value ? 'none' : 'transform 0.3s cubic-bezier(0.4, 0, 0.2, 1)'
  })

  /**
   * Opacité du spinner (0-1)
   */
  const spinnerOpacity = () => {
    return Math.min(pullDistance.value / threshold, 1)
  }

  /**
   * Rotation du spinner (0-360deg)
   */
  const spinnerRotation = () => {
    return (pullDistance.value / threshold) * 360
  }

  return {
    isPulling,
    isRefreshing,
    pullDistance,
    triggerRefresh,
    containerStyle,
    spinnerOpacity,
    spinnerRotation,
    cleanup
  }
}
