<script setup>
import { ref, onMounted, onBeforeUnmount } from 'vue'
import { useAuthStore } from './stores/auth'
import NotificationContainer from './components/NotificationContainer.vue'
import NotificationBell from './components/NotificationBell.vue'

const authStore = useAuthStore()
const userMenuOpen = ref(false)
const isOnline = ref(navigator.onLine)

// Gestionnaire de connexion
window.addEventListener('online', () => isOnline.value = true)
window.addEventListener('offline', () => isOnline.value = false)

// Fermer le menu au clic en dehors
const closeUserMenu = (e) => {
  const menu = document.querySelector('.user-menu')
  if (menu && !menu.contains(e.target)) {
    userMenuOpen.value = false
  }
}

onMounted(() => {
  setTimeout(() => {
    document.addEventListener('click', closeUserMenu)
  }, 0)
})

onBeforeUnmount(() => {
  document.removeEventListener('click', closeUserMenu)
})

// Méthode de déconnexion
const handleLogout = async () => {
  try {
    userMenuOpen.value = false
    await authStore.logout()
    window.location.href = '/login'  // Force le rechargement complet
  } catch (error) {
    console.error('Erreur lors de la déconnexion:', error)
  }
}
</script>

<template>
  <div id="app" class="min-h-screen">
    <!-- Header avec logo et progression -->
    <header class="bg-white shadow-sm border-b border-gray-100">
      <div class="max-w-4xl mx-auto px-4 py-4">
        <div class="flex items-center justify-between">
          <div class="flex items-center space-x-3">
            <router-link to="/" class="w-12 h-12 rounded-xl flex items-center justify-center hover:opacity-80 transition-opacity">
              <img src="/logo.png" alt="Moov Money Logo" class="w-12 h-12 object-contain">
            </router-link>
            <div>
              <h1 class="text-xl font-bold text-gray-800">Moov Money</h1>
              <p class="text-sm text-gray-500">Recrutement Marchand</p>
            </div>
          </div>
          
          <!-- Boutons de droite -->
          <div class="flex items-center space-x-4">
            <!-- Indicateur de connexion -->
            <div class="flex items-center space-x-1">
              <div :class="isOnline ? 'bg-green-500' : 'bg-red-500'"
                   class="w-2 h-2 rounded-full"></div>
              <span class="text-xs text-gray-500">
                {{ isOnline ? 'En ligne' : 'Hors ligne' }}
              </span>
            </div>

            <!-- Notifications (seulement si authentifié) -->
            <NotificationBell v-if="authStore.isAuthenticated" />

            <!-- Menu utilisateur -->
            <div class="relative user-menu">
              <button
                @click.stop="userMenuOpen = !userMenuOpen"
                class="h-10 px-3 rounded-xl bg-gray-100 hover:bg-gray-200 text-gray-700 font-medium flex items-center space-x-2 focus:outline-none focus:ring-2 focus:ring-orange-500 focus:ring-offset-2 transition-colors"
              >
                <i class="fas fa-user-circle text-lg mr-2"></i>
                <span v-if="authStore.user">{{ authStore.userFullName }}</span>
                <i class="fas fa-chevron-down text-xs ml-2"></i>
              </button>

              <!-- Menu déroulant -->
              <div v-if="userMenuOpen" 
                   class="absolute right-0 mt-2 w-48 rounded-xl overflow-hidden bg-white py-1 shadow-lg ring-1 ring-black ring-opacity-5 focus:outline-none z-50">
                <div v-if="authStore.isAuthenticated">
                  <!-- Dashboard -->
                  <router-link
                    to="/dashboard"
                    class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100"
                    @click="userMenuOpen = false"
                  >
                    <i class="fas fa-tachometer-alt mr-2"></i>
                    Tableau de bord
                  </router-link>
                  
                  <!-- Notifications -->
                  <router-link
                    to="/notifications"
                    class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100"
                    @click="userMenuOpen = false"
                  >
                    <i class="fas fa-bell mr-2"></i>
                    Notifications
                  </router-link>
                  
                  <!-- Créer utilisateur (admin seulement) -->
                  <router-link
                    v-if="authStore.isAdmin"
                    to="/register"
                    class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100"
                    @click="userMenuOpen = false"
                  >
                    <i class="fas fa-user-plus mr-2"></i>
                    Créer un utilisateur
                  </router-link>

                  <!-- Déconnexion -->
                  <button
                    @click="handleLogout"
                    class="w-full text-left px-4 py-2 text-sm text-red-600 hover:bg-gray-100"
                  >
                    <i class="fas fa-sign-out-alt mr-2"></i>
                    Déconnexion
                  </button>
                </div>
                <div v-else>
                  <!-- Connexion -->
                  <router-link
                    to="/login"
                    class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100"
                    @click="userMenuOpen = false"
                  >
                    <i class="fas fa-sign-in-alt mr-2"></i>
                    Connexion
                  </router-link>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </header>

    <!-- Contenu principal -->
    <main>
      <router-view />
    </main>

    <!-- Footer -->
    <footer class="bg-white border-t border-gray-100">
      <div class="max-w-4xl mx-auto px-4 py-6">
        <div class="text-center text-sm text-gray-500">
          <p>&copy; {{ currentYear }} Moov Money. Tous droits réservés. | Crafted with ❤️ by David D.</p>
        </div>
      </div>
    </footer>

    <!-- Conteneur de notifications -->
    <NotificationContainer />
  </div>
</template>

<script>
import { ref, onMounted, onBeforeUnmount } from 'vue'
import { useRouter } from 'vue-router'
import { useAuthStore } from './stores/auth'
import NotificationContainer from './components/NotificationContainer.vue'

export default {
  name: 'App',
  components: {
    NotificationContainer
  },
  setup() {
    const isOnline = ref(navigator.onLine)
    const currentYear = new Date().getFullYear()

    const updateOnlineStatus = () => {
      isOnline.value = navigator.onLine
    }

    onMounted(() => {
      window.addEventListener('online', updateOnlineStatus)
      window.addEventListener('offline', updateOnlineStatus)
    })

    onBeforeUnmount(() => {
      window.removeEventListener('online', updateOnlineStatus)
      window.removeEventListener('offline', updateOnlineStatus)
    })

    return { isOnline, currentYear }
  }
}
</script>