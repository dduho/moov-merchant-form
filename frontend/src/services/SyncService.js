/**
 * Service de synchronisation pour la gestion de la queue hors ligne
 * Utilise IndexedDB pour persister les requêtes en attente
 */

const DB_NAME = 'moov_sync_db'
const DB_VERSION = 1
const STORE_NAME = 'pending_requests'
const RETRY_INTERVAL = 30000 // 30 secondes
const MAX_RETRIES = 5

class SyncService {
  constructor() {
    this.db = null
    this.retryTimer = null
    this.isProcessing = false
    this.listeners = []
  }

  /**
   * Initialise la base de données IndexedDB
   */
  async init() {
    return new Promise((resolve, reject) => {
      const request = indexedDB.open(DB_NAME, DB_VERSION)

      request.onerror = () => reject(request.error)
      request.onsuccess = () => {
        this.db = request.result
        this.startPeriodicSync()
        resolve()
      }

      request.onupgradeneeded = (event) => {
        const db = event.target.result

        // Créer le store si nécessaire
        if (!db.objectStoreNames.contains(STORE_NAME)) {
          const objectStore = db.createObjectStore(STORE_NAME, { 
            keyPath: 'id', 
            autoIncrement: true 
          })
          
          objectStore.createIndex('timestamp', 'timestamp', { unique: false })
          objectStore.createIndex('retryCount', 'retryCount', { unique: false })
          objectStore.createIndex('status', 'status', { unique: false })
        }
      }
    })
  }

  /**
   * Ajoute une requête à la queue
   */
  async addToQueue(request) {
    if (!this.db) {
      await this.init()
    }

    const queueItem = {
      url: request.url,
      method: request.method,
      headers: request.headers || {},
      body: request.body,
      timestamp: Date.now(),
      retryCount: 0,
      status: 'pending',
      lastError: null
    }

    return new Promise((resolve, reject) => {
      const transaction = this.db.transaction([STORE_NAME], 'readwrite')
      const store = transaction.objectStore(STORE_NAME)
      const addRequest = store.add(queueItem)

      addRequest.onsuccess = () => {
        console.log('✅ Requête ajoutée à la queue:', queueItem.url)
        this.notifyListeners('added', { id: addRequest.result, ...queueItem })
        
        // Tenter la synchronisation immédiatement si en ligne
        if (navigator.onLine) {
          this.processQueue()
        }
        
        resolve(addRequest.result)
      }

      addRequest.onerror = () => reject(addRequest.error)
    })
  }

  /**
   * Récupère toutes les requêtes en attente
   */
  async getPendingRequests() {
    if (!this.db) {
      await this.init()
    }

    return new Promise((resolve, reject) => {
      const transaction = this.db.transaction([STORE_NAME], 'readonly')
      const store = transaction.objectStore(STORE_NAME)
      const getAllRequest = store.getAll()

      getAllRequest.onsuccess = () => {
        const pending = getAllRequest.result.filter(item => item.status === 'pending')
        resolve(pending)
      }

      getAllRequest.onerror = () => reject(getAllRequest.error)
    })
  }

  /**
   * Traite la queue de requêtes en attente
   */
  async processQueue() {
    if (!navigator.onLine || this.isProcessing) {
      return
    }

    this.isProcessing = true
    console.log('🔄 Traitement de la queue de synchronisation...')

    try {
      const pendingRequests = await this.getPendingRequests()

      if (pendingRequests.length === 0) {
        console.log('✅ Aucune requête en attente')
        this.isProcessing = false
        return
      }

      console.log(`📋 ${pendingRequests.length} requête(s) en attente`)

      for (const request of pendingRequests) {
        try {
          await this.retryRequest(request)
        } catch (error) {
          console.error('❌ Erreur lors du retry:', error)
        }
      }
    } catch (error) {
      console.error('❌ Erreur lors du traitement de la queue:', error)
    } finally {
      this.isProcessing = false
    }
  }

  /**
   * Retente une requête spécifique
   */
  async retryRequest(request) {
    try {
      console.log(`🔄 Retry requête #${request.id}:`, request.url)

      const response = await fetch(request.url, {
        method: request.method,
        headers: request.headers,
        body: request.body
      })

      if (response.ok) {
        // Succès - supprimer de la queue
        await this.removeFromQueue(request.id)
        console.log(`✅ Requête #${request.id} synchronisée avec succès`)
        this.notifyListeners('synced', request)
        
        // Vibration de succès (si supporté)
        if (navigator.vibrate) {
          navigator.vibrate([100, 50, 100])
        }
      } else {
        // Échec - incrémenter le compteur
        await this.incrementRetryCount(request.id, `HTTP ${response.status}`)
      }
    } catch (error) {
      // Erreur réseau - incrémenter le compteur
      await this.incrementRetryCount(request.id, error.message)
    }
  }

  /**
   * Incrémente le compteur de retry d'une requête
   */
  async incrementRetryCount(id, errorMessage) {
    if (!this.db) return

    return new Promise((resolve, reject) => {
      const transaction = this.db.transaction([STORE_NAME], 'readwrite')
      const store = transaction.objectStore(STORE_NAME)
      const getRequest = store.get(id)

      getRequest.onsuccess = () => {
        const request = getRequest.result
        
        if (!request) {
          resolve()
          return
        }

        request.retryCount++
        request.lastError = errorMessage

        // Si trop de tentatives, marquer comme échoué
        if (request.retryCount >= MAX_RETRIES) {
          request.status = 'failed'
          console.error(`❌ Requête #${id} échouée après ${MAX_RETRIES} tentatives`)
          this.notifyListeners('failed', request)
        } else {
          console.warn(`⚠️ Requête #${id} échouée (tentative ${request.retryCount}/${MAX_RETRIES})`)
        }

        const updateRequest = store.put(request)
        updateRequest.onsuccess = () => resolve()
        updateRequest.onerror = () => reject(updateRequest.error)
      }

      getRequest.onerror = () => reject(getRequest.error)
    })
  }

  /**
   * Supprime une requête de la queue
   */
  async removeFromQueue(id) {
    if (!this.db) return

    return new Promise((resolve, reject) => {
      const transaction = this.db.transaction([STORE_NAME], 'readwrite')
      const store = transaction.objectStore(STORE_NAME)
      const deleteRequest = store.delete(id)

      deleteRequest.onsuccess = () => {
        console.log(`🗑️ Requête #${id} supprimée de la queue`)
        resolve()
      }

      deleteRequest.onerror = () => reject(deleteRequest.error)
    })
  }

  /**
   * Vide toutes les requêtes échouées
   */
  async clearFailedRequests() {
    if (!this.db) return

    return new Promise((resolve, reject) => {
      const transaction = this.db.transaction([STORE_NAME], 'readwrite')
      const store = transaction.objectStore(STORE_NAME)
      const getAllRequest = store.getAll()

      getAllRequest.onsuccess = () => {
        const failed = getAllRequest.result.filter(item => item.status === 'failed')
        
        const deletePromises = failed.map(item => {
          return new Promise((res, rej) => {
            const delReq = store.delete(item.id)
            delReq.onsuccess = () => res()
            delReq.onerror = () => rej(delReq.error)
          })
        })

        Promise.all(deletePromises)
          .then(() => {
            console.log(`🗑️ ${failed.length} requête(s) échouée(s) supprimée(s)`)
            resolve(failed.length)
          })
          .catch(reject)
      }

      getAllRequest.onerror = () => reject(getAllRequest.error)
    })
  }

  /**
   * Démarre la synchronisation périodique
   */
  startPeriodicSync() {
    if (this.retryTimer) {
      clearInterval(this.retryTimer)
    }

    this.retryTimer = setInterval(() => {
      if (navigator.onLine) {
        this.processQueue()
      }
    }, RETRY_INTERVAL)

    // Écouter le retour en ligne
    window.addEventListener('online', () => {
      console.log('🌐 Connexion rétablie - synchronisation en cours...')
      this.processQueue()
    })
  }

  /**
   * Arrête la synchronisation périodique
   */
  stopPeriodicSync() {
    if (this.retryTimer) {
      clearInterval(this.retryTimer)
      this.retryTimer = null
    }
  }

  /**
   * Ajoute un listener pour les événements de sync
   */
  addListener(callback) {
    this.listeners.push(callback)
  }

  /**
   * Supprime un listener
   */
  removeListener(callback) {
    this.listeners = this.listeners.filter(cb => cb !== callback)
  }

  /**
   * Notifie tous les listeners
   */
  notifyListeners(event, data) {
    this.listeners.forEach(callback => {
      try {
        callback(event, data)
      } catch (error) {
        console.error('❌ Erreur dans listener:', error)
      }
    })
  }

  /**
   * Obtient les statistiques de la queue
   */
  async getStats() {
    if (!this.db) {
      await this.init()
    }

    return new Promise((resolve, reject) => {
      const transaction = this.db.transaction([STORE_NAME], 'readonly')
      const store = transaction.objectStore(STORE_NAME)
      const getAllRequest = store.getAll()

      getAllRequest.onsuccess = () => {
        const all = getAllRequest.result
        const stats = {
          total: all.length,
          pending: all.filter(r => r.status === 'pending').length,
          failed: all.filter(r => r.status === 'failed').length,
          oldest: all.length > 0 ? Math.min(...all.map(r => r.timestamp)) : null
        }
        resolve(stats)
      }

      getAllRequest.onerror = () => reject(getAllRequest.error)
    })
  }
}

// Export singleton instance
export default new SyncService()
