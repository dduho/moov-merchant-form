import { createApp } from 'vue'
import { createPinia } from 'pinia'
import axios from 'axios'
import '@fortawesome/fontawesome-free/css/all.min.css'
import router from './router'
import { useAuthStore } from './stores/auth'
import App from './App.vue'
import './style.css'

// Create the app and pinia instance first
const app = createApp(App)
const pinia = createPinia()
app.use(pinia)
app.use(router)

// Gestionnaire d'erreurs global pour ignorer les erreurs d'extensions
app.config.errorHandler = (err, instance, info) => {
  // Ignorer les erreurs liées aux extensions de navigateur
  if (err.message && err.message.includes('message channel closed')) {
    console.debug('Extension browser error ignored:', err.message)
    return
  }
  
  // Logger les autres erreurs
  console.error('Vue Error:', err, info)
}

// Gestionnaire d'erreurs non capturées
window.addEventListener('unhandledrejection', (event) => {
  if (event.reason && event.reason.message && event.reason.message.includes('message channel closed')) {
    console.debug('Extension promise rejection ignored:', event.reason.message)
    event.preventDefault()
    return
  }
  console.error('Unhandled promise rejection:', event.reason)
})

// Configuration axios
axios.defaults.baseURL = import.meta.env.VITE_API_URL || 'http://localhost:8000'
axios.defaults.withCredentials = true
axios.defaults.headers.common['Accept'] = 'application/json'
axios.defaults.headers.common['X-Requested-With'] = 'XMLHttpRequest'

// Now we can safely use the auth store in interceptors
axios.interceptors.response.use(
  response => response,
  error => {
    if (error.response?.status === 401 || error.response?.status === 419) {
      const authStore = useAuthStore()
      authStore.clearUser()
      router.push('/login')
    }
    console.error('Erreur API:', error.response?.data || error.message)
    return Promise.reject(error)
  }
)

// Obtenir le CSRF token au démarrage
axios.get('/sanctum/csrf-cookie').catch(error => {
  console.error('Erreur lors de l\'obtention du CSRF token:', error)
})


if ('serviceWorker' in navigator) {
  window.addEventListener('load', () => {
    navigator.serviceWorker.register('/sw.js')
      .then(registration => console.log('SW registered:', registration))
      .catch(error => console.log('SW registration failed:', error))
  })
}

// Initialize auth store
const authStore = useAuthStore()

// Wait for initialization before mounting the app
await authStore.init()

// Mount the app
app.mount('#app')

console.log('✅ Application Moov Money démarrée')