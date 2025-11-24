/**
 * Composable pour lazy loading d'images
 * Utilise Intersection Observer pour charger les images à la demande
 */

import { ref } from 'vue'

export function useLazyImage(options = {}) {
  const {
    rootMargin = '50px',
    threshold = 0.01,
    placeholder = 'data:image/svg+xml,%3Csvg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 400 300"%3E%3Crect fill="%23f3f4f6" width="400" height="300"/%3E%3C/svg%3E'
  } = options

  const imageRef = ref(null)
  const isLoaded = ref(false)
  const isError = ref(false)
  const currentSrc = ref(placeholder)

  let observer = null

  /**
   * Charge l'image
   */
  const loadImage = (src) => {
    if (!src) return

    const img = new Image()

    img.onload = () => {
      currentSrc.value = src
      isLoaded.value = true
      isError.value = false
    }

    img.onerror = () => {
      isError.value = true
      isLoaded.value = false
      console.error(`Failed to load image: ${src}`)
    }

    img.src = src
  }

  /**
   * Initialise l'Intersection Observer
   */
  const initObserver = (src) => {
    if (!imageRef.value || !src) return

    observer = new IntersectionObserver(
      (entries) => {
        entries.forEach((entry) => {
          if (entry.isIntersecting) {
            loadImage(src)
            observer.unobserve(entry.target)
          }
        })
      },
      {
        rootMargin,
        threshold
      }
    )

    observer.observe(imageRef.value)
  }

  /**
   * Nettoie l'observer
   */
  const cleanup = () => {
    if (observer && imageRef.value) {
      observer.unobserve(imageRef.value)
      observer = null
    }
  }

  return {
    imageRef,
    currentSrc,
    isLoaded,
    isError,
    initObserver,
    loadImage,
    cleanup
  }
}

/**
 * Directive v-lazy pour utilisation directe dans les templates
 */
export const vLazy = {
  mounted(el, binding) {
    const options = typeof binding.value === 'object' ? binding.value : {}
    const src = typeof binding.value === 'string' ? binding.value : binding.value.src

    if (!src) return

    const observer = new IntersectionObserver(
      (entries) => {
        entries.forEach((entry) => {
          if (entry.isIntersecting) {
            const img = entry.target

            // Ajouter un placeholder pendant le chargement
            if (!img.src || img.src === window.location.href) {
              img.src = options.placeholder || 'data:image/svg+xml,%3Csvg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 400 300"%3E%3Crect fill="%23f3f4f6" width="400" height="300"/%3E%3C/svg%3E'
            }

            // Charger l'image réelle
            const realImg = new Image()
            realImg.onload = () => {
              img.src = src
              img.classList.add('lazy-loaded')
              observer.unobserve(img)
            }
            realImg.onerror = () => {
              img.classList.add('lazy-error')
              observer.unobserve(img)
            }
            realImg.src = src
          }
        })
      },
      {
        rootMargin: options.rootMargin || '50px',
        threshold: options.threshold || 0.01
      }
    )

    observer.observe(el)

    // Stocker l'observer pour le cleanup
    el._lazyObserver = observer
  },

  unmounted(el) {
    if (el._lazyObserver) {
      el._lazyObserver.disconnect()
      delete el._lazyObserver
    }
  }
}
