const CACHE_NAME = 'moov-merchant-v1.0.0'
const DYNAMIC_CACHE = 'moov-merchant-dynamic-v1.0.0'

const STATIC_ASSETS = [
  '/',
  '/index.html',
  '/src/main.js',
  '/src/App.vue',
  '/src/style.css',
]

// Installation du Service Worker
self.addEventListener('install', event => {
  console.log('[SW] Installation...')
  event.waitUntil(
    caches.open(CACHE_NAME)
      .then(cache => {
        console.log('[SW] Mise en cache des assets statiques')
        return cache.addAll(STATIC_ASSETS)
      })
      .then(() => self.skipWaiting())
  )
})

// Activation du Service Worker
self.addEventListener('activate', event => {
  console.log('[SW] Activation...')
  event.waitUntil(
    caches.keys()
      .then(cacheNames => {
        return Promise.all(
          cacheNames
            .filter(name => name !== CACHE_NAME && name !== DYNAMIC_CACHE)
            .map(name => {
              console.log('[SW] Suppression ancien cache:', name)
              return caches.delete(name)
            })
        )
      })
      .then(() => self.clients.claim())
  )
})

// Stratégie de cache: Network First, fallback to Cache
self.addEventListener('fetch', event => {
  const { request } = event
  const url = new URL(request.url)

  // IMPORTANT: Ignorer les extensions Chrome et autres protocoles non HTTP(S)
  if (
    url.protocol !== 'http:' && 
    url.protocol !== 'https:' ||
    url.pathname.startsWith('/api/')
  ) {
    return // Ne pas intercepter ces requêtes
  }

  event.respondWith(
    fetch(request)
      .then(response => {
        // Cloner la réponse
        const responseClone = response.clone()
        
        // Mettre en cache si c'est une requête GET valide
        if (request.method === 'GET' && response.status === 200) {
          caches.open(DYNAMIC_CACHE)
            .then(cache => {
              cache.put(request, responseClone).catch(err => {
                // Ignorer les erreurs de cache silencieusement
                console.warn('Cache put error (ignored):', err.message)
              })
            })
        }
        
        return response
      })
      .catch(() => {
        // Si offline, chercher dans le cache
        return caches.match(request)
          .then(response => {
            if (response) {
              return response
            }
            
            // Si pas trouvé dans le cache, retourner la page offline
            if (request.destination === 'document') {
              return caches.match('/index.html')
            }
            
            return new Response('Contenu non disponible hors ligne', {
              status: 503,
              statusText: 'Service Unavailable',
              headers: new Headers({
                'Content-Type': 'text/plain'
              })
            })
          })
      })
  )
})

// Écouter les messages du client
self.addEventListener('message', event => {
  if (event.data && event.data.type === 'SKIP_WAITING') {
    self.skipWaiting()
  }
  
  if (event.data && event.data.type === 'CACHE_URLS') {
    const urlsToCache = event.data.payload
    caches.open(DYNAMIC_CACHE)
      .then(cache => cache.addAll(urlsToCache))
  }
})

// Background Sync pour les formulaires
self.addEventListener('sync', event => {
  console.log('[SW] Background Sync:', event.tag)
  
  if (event.tag === 'sync-merchant-forms') {
    event.waitUntil(syncMerchantForms())
  }
})

async function syncMerchantForms() {
  try {
    // Récupérer les formulaires en attente depuis IndexedDB
    const db = await openDatabase()
    const forms = await getPendingForms(db)
    
    console.log(`[SW] Synchronisation de ${forms.length} formulaire(s)`)
    
    for (const form of forms) {
      try {
        const response = await fetch('/api/merchants', {
          method: 'POST',
          headers: {
            'Content-Type': 'application/json',
          },
          body: JSON.stringify(form.data)
        })
        
        if (response.ok) {
          // Supprimer le formulaire synchronisé
          await deleteSyncedForm(db, form.id)
          console.log(`[SW] Formulaire ${form.id} synchronisé avec succès`)
        }
      } catch (error) {
        console.error(`[SW] Erreur sync formulaire ${form.id}:`, error)
      }
    }
  } catch (error) {
    console.error('[SW] Erreur synchronisation:', error)
  }
}

function openDatabase() {
  return new Promise((resolve, reject) => {
    const request = indexedDB.open('MoovMerchantDB', 1)
    request.onsuccess = () => resolve(request.result)
    request.onerror = () => reject(request.error)
  })
}

function getPendingForms(db) {
  return new Promise((resolve, reject) => {
    const transaction = db.transaction(['syncQueue'], 'readonly')
    const store = transaction.objectStore('syncQueue')
    const request = store.getAll()
    request.onsuccess = () => resolve(request.result)
    request.onerror = () => reject(request.error)
  })
}

function deleteSyncedForm(db, id) {
  return new Promise((resolve, reject) => {
    const transaction = db.transaction(['syncQueue'], 'readwrite')
    const store = transaction.objectStore('syncQueue')
    const request = store.delete(id)
    request.onsuccess = () => resolve()
    request.onerror = () => reject(request.error)
  })
}