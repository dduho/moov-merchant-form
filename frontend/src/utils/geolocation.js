/**
 * Service de géolocalisation optimisé pour mobile
 * - Timeout 30s pour GPS froid
 * - Retry automatique avec backoff exponentiel
 * - Cache de dernière position (24h)
 * - Fallback sur position réseau
 */

const CACHE_KEY = 'last_known_position'
const CACHE_DURATION = 24 * 60 * 60 * 1000 // 24h

/**
 * Options optimisées de géolocalisation
 */
const HIGH_ACCURACY_OPTIONS = {
  enableHighAccuracy: true,
  timeout: 30000, // 30s pour GPS froid
  maximumAge: 0 // Toujours demander position fraîche
}

const LOW_ACCURACY_OPTIONS = {
  enableHighAccuracy: false,
  timeout: 10000,
  maximumAge: 5 * 60 * 1000 // 5 minutes
}

/**
 * Obtenir la position avec retry
 */
export const getCurrentPosition = async (maxRetries = 3) => {
  // Vérifier si la géolocalisation est disponible
  if (!navigator.geolocation) {
    throw new Error('La géolocalisation n\'est pas supportée par ce navigateur')
  }

  // Essayer d'abord avec haute précision
  for (let attempt = 0; attempt < maxRetries; attempt++) {
    try {
      console.log(`[Geolocation] Tentative ${attempt + 1}/${maxRetries}...`)
      
      const position = await getPositionPromise(
        attempt === 0 ? HIGH_ACCURACY_OPTIONS : LOW_ACCURACY_OPTIONS
      )
      
      // Sauvegarder en cache
      cachePosition(position)
      
      console.log(`[Geolocation] Succès! Précision: ${position.coords.accuracy.toFixed(0)}m`)
      return position
    } catch (error) {
      console.warn(`[Geolocation] Échec tentative ${attempt + 1}:`, error.message)
      
      // Si c'est le dernier essai, essayer le cache ou échouer
      if (attempt === maxRetries - 1) {
        const cachedPosition = getCachedPosition()
        if (cachedPosition) {
          console.log('[Geolocation] Utilisation de la position en cache')
          return cachedPosition
        }
        
        throw error
      }
      
      // Attendre avec backoff exponentiel
      const delay = Math.pow(2, attempt) * 1000
      console.log(`[Geolocation] Nouvelle tentative dans ${delay}ms...`)
      await sleep(delay)
    }
  }
}

/**
 * Wrapper promesse pour getCurrentPosition
 */
const getPositionPromise = (options) => {
  return new Promise((resolve, reject) => {
    navigator.geolocation.getCurrentPosition(
      resolve,
      reject,
      options
    )
  })
}

/**
 * Sauvegarder la position en cache
 */
const cachePosition = (position) => {
  try {
    const cached = {
      coords: {
        latitude: position.coords.latitude,
        longitude: position.coords.longitude,
        accuracy: position.coords.accuracy
      },
      timestamp: Date.now()
    }
    localStorage.setItem(CACHE_KEY, JSON.stringify(cached))
  } catch (error) {
    console.error('[Geolocation] Erreur sauvegarde cache:', error)
  }
}

/**
 * Récupérer la position en cache (si < 24h)
 */
const getCachedPosition = () => {
  try {
    const cached = localStorage.getItem(CACHE_KEY)
    if (!cached) return null
    
    const position = JSON.parse(cached)
    const age = Date.now() - position.timestamp
    
    if (age > CACHE_DURATION) {
      console.log('[Geolocation] Cache expiré')
      localStorage.removeItem(CACHE_KEY)
      return null
    }
    
    console.log(`[Geolocation] Cache valide (${(age / 1000 / 60).toFixed(0)} min)`)
    return {
      coords: position.coords,
      timestamp: position.timestamp,
      fromCache: true
    }
  } catch (error) {
    console.error('[Geolocation] Erreur lecture cache:', error)
    return null
  }
}

/**
 * Vérifier la précision de la position
 * @returns {string} 'good' | 'medium' | 'poor'
 */
export const getAccuracyLevel = (accuracy) => {
  if (accuracy < 10) return 'good'
  if (accuracy < 50) return 'medium'
  return 'poor'
}

/**
 * Formater la précision pour l'affichage
 */
export const formatAccuracy = (accuracy) => {
  if (accuracy < 1000) {
    return `${Math.round(accuracy)}m`
  }
  return `${(accuracy / 1000).toFixed(1)}km`
}

/**
 * Surveiller la position en continu
 */
export const watchPosition = (onSuccess, onError, options = HIGH_ACCURACY_OPTIONS) => {
  if (!navigator.geolocation) {
    onError(new Error('Géolocalisation non supportée'))
    return null
  }

  const watchId = navigator.geolocation.watchPosition(
    (position) => {
      cachePosition(position)
      onSuccess(position)
    },
    onError,
    options
  )

  // Retourner une fonction pour arrêter le watch
  return () => {
    navigator.geolocation.clearWatch(watchId)
  }
}

/**
 * Utilitaire: sleep
 */
const sleep = (ms) => new Promise(resolve => setTimeout(resolve, ms))

/**
 * Vider le cache de position
 */
export const clearPositionCache = () => {
  localStorage.removeItem(CACHE_KEY)
  console.log('[Geolocation] Cache vidé')
}

export default {
  getCurrentPosition,
  getAccuracyLevel,
  formatAccuracy,
  watchPosition,
  clearPositionCache
}
