/**
 * Composable pour gérer les gestures de swipe
 * Basé sur touch events natifs pour meilleure compatibilité mobile
 */

import { ref } from 'vue'

export function useSwipe(target, options = {}) {
  const {
    threshold = 50,        // Distance minimum pour déclencher un swipe (px)
    velocity = 0.3,        // Vitesse minimum (px/ms)
    onSwipeLeft = null,
    onSwipeRight = null,
    onSwipeUp = null,
    onSwipeDown = null,
    onSwipe = null
  } = options

  const isSwiping = ref(false)
  const direction = ref(null)
  
  let startX = 0
  let startY = 0
  let startTime = 0
  let currentX = 0
  let currentY = 0

  const handleTouchStart = (e) => {
    const touch = e.touches[0]
    startX = touch.clientX
    startY = touch.clientY
    startTime = Date.now()
    isSwiping.value = true
    direction.value = null
  }

  const handleTouchMove = (e) => {
    if (!isSwiping.value) return
    
    const touch = e.touches[0]
    currentX = touch.clientX
    currentY = touch.clientY
  }

  const handleTouchEnd = () => {
    if (!isSwiping.value) return

    const deltaX = currentX - startX
    const deltaY = currentY - startY
    const deltaTime = Date.now() - startTime
    
    const absX = Math.abs(deltaX)
    const absY = Math.abs(deltaY)
    const velocityX = absX / deltaTime
    const velocityY = absY / deltaTime

    // Déterminer la direction dominante
    if (Math.max(absX, absY) > threshold && Math.max(velocityX, velocityY) > velocity) {
      if (absX > absY) {
        // Swipe horizontal
        if (deltaX > 0) {
          direction.value = 'right'
          onSwipeRight?.()
        } else {
          direction.value = 'left'
          onSwipeLeft?.()
        }
      } else {
        // Swipe vertical
        if (deltaY > 0) {
          direction.value = 'down'
          onSwipeDown?.()
        } else {
          direction.value = 'up'
          onSwipeUp?.()
        }
      }

      onSwipe?.(direction.value, { deltaX, deltaY, deltaTime })

      // Vibration feedback si supporté
      if (navigator.vibrate) {
        navigator.vibrate(10)
      }
    }

    isSwiping.value = false
  }

  // Initialisation immédiate
  const element = target.value?.$el || target.value || target

  if (element && element.addEventListener) {
    element.addEventListener('touchstart', handleTouchStart, { passive: true })
    element.addEventListener('touchmove', handleTouchMove, { passive: true })
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

  return {
    isSwiping,
    direction,
    cleanup
  }
}
