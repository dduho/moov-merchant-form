<script setup>
import { ref, onMounted, onBeforeUnmount } from 'vue'
import { useAuthStore } from './stores/auth'
import NotificationContainer from './components/NotificationContainer.vue'
import NotificationBell from './components/NotificationBell.vue'
import { useDarkMode } from './composables/useDarkMode'
import { useHaptic } from './composables/useHaptic'

const authStore = useAuthStore()
const userMenuOpen = ref(false)
const isOnline = ref(navigator.onLine)
const currentYear = new Date().getFullYear()

// Mode sombre
const { isDark, toggle: toggleDarkMode } = useDarkMode()
const haptic = useHaptic()

// Gestionnaires de connexion
const updateOnlineStatus = () => {
  isOnline.value = navigator.onLine
}

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
  window.addEventListener('online', updateOnlineStatus)
  window.addEventListener('offline', updateOnlineStatus)
})

onBeforeUnmount(() => {
  document.removeEventListener('click', closeUserMenu)
  window.removeEventListener('online', updateOnlineStatus)
  window.removeEventListener('offline', updateOnlineStatus)
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
  <div id="app" class="min-h-screen flex flex-col dark:bg-dark-bg-primary transition-colors duration-300">
    <!-- Header moderne et responsive -->
    <header class="bg-white dark:bg-dark-bg-secondary shadow-sm border-b py-2 border-gray-100 dark:border-dark-border sticky top-0 z-40 flex-shrink-0 transition-colors duration-300">
      <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex items-center justify-between h-16">
          <!-- Logo et branding -->
          <div class="flex items-center space-x-3 flex-shrink-0">
            <router-link to="/" class="group flex items-center space-x-3">
              <div class="w-10 h-10 rounded-xl bg-gradient-to-br from-orange-500 to-orange-600 flex items-center justify-center shadow-lg group-hover:shadow-xl transition-all duration-200 group-hover:scale-105">
                <img src="/logo.png" alt="Moov Money Logo" class="w-12 h-12 object-contain">
              </div>
              <div class="hidden sm:block">
                <h1 class="text-lg font-bold text-gray-900 group-hover:text-orange-600 transition-colors">Moov Money</h1>
                <p class="text-xs text-gray-500 font-medium">Recrutement Marchand</p>
              </div>
            </router-link>
          </div>
          
          <!-- Actions de droite -->
          <div class="flex items-center space-x-2 sm:space-x-4">
            <!-- Indicateur de connexion - visible sur tous les écrans -->
            <div class="flex items-center space-x-1.5 px-2 py-1 rounded-full bg-gray-50">
              <div :class="isOnline ? 'bg-green-500' : 'bg-red-500'"
                   class="w-3 h-3 rounded-full animate-pulse"></div>
              <span class="text-xs text-gray-600 font-medium hidden sm:inline">
                {{ isOnline ? 'En ligne' : 'Hors ligne' }}
              </span>
              <!-- Version mobile - juste l'icône -->
              <span class="sm:hidden" :title="isOnline ? 'En ligne' : 'Hors ligne'">
                <svg v-if="isOnline" class="w-3 h-3 text-green-600" fill="currentColor" viewBox="0 0 20 20">
                  <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"></path>
                </svg>
                <svg v-else class="w-3 h-3 text-red-600" fill="currentColor" viewBox="0 0 20 20">
                  <path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd"></path>
                </svg>
              </span>
            </div>

            <!-- Notifications (seulement si authentifié) -->
            <NotificationBell v-if="authStore.isAuthenticated" />

            <!-- Menu utilisateur moderne -->
            <div class="relative user-menu">
              <button
                @click.stop="userMenuOpen = !userMenuOpen"
                class="px-3 rounded-xl bg-gray-50 hover:bg-gray-100 border border-gray-200 hover:border-gray-300 text-gray-700 font-medium flex items-center space-x-2 focus:outline-none focus:ring-2 focus:ring-orange-500 focus:ring-offset-2 transition-all duration-200 shadow-sm hover:shadow"
                :class="{ 'bg-gray-100 border-gray-300': userMenuOpen }"
                style="min-height: 44px;"
              >
                <div class="flex items-center space-x-2">
                  <div class="w-6 h-6 rounded-full bg-gradient-to-br from-orange-500 to-orange-600 flex items-center justify-center">
                    <i class="fas fa-user text-white text-xs"></i>
                  </div>
                  <span v-if="authStore.user" class="hidden sm:inline text-sm font-medium">{{ authStore.userFullName }}</span>
                </div>
                <svg class="w-4 h-4 ml-1 transition-transform duration-200" :class="{ 'rotate-180': userMenuOpen }" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                </svg>
              </button>

              <!-- Menu déroulant moderne avec animations -->
              <transition
                enter-active-class="transition ease-out duration-200"
                enter-from-class="transform opacity-0 scale-95"
                enter-to-class="transform opacity-100 scale-100"
                leave-active-class="transition ease-in duration-75"
                leave-from-class="transform opacity-100 scale-100"
                leave-to-class="transform opacity-0 scale-95">
                <div v-if="userMenuOpen" 
                     class="absolute right-0 mt-3 w-56 rounded-2xl bg-white py-2 shadow-xl border border-gray-200 focus:outline-none z-50 backdrop-blur-sm">
                  <div v-if="authStore.isAuthenticated">
                    <!-- Info utilisateur -->
                    <div class="px-4 py-3 border-b border-gray-100">
                      <p class="text-sm font-medium text-gray-900">{{ authStore.userFullName }}</p>
                      <p class="text-xs text-gray-500">{{ authStore.user?.email }}</p>
                    </div>

                    <div class="py-1">
                      <!-- Formulaire de candidature -->
                      <router-link
                        to="/formulaire"
                        class="flex items-center px-4 py-3 text-sm text-gray-700 hover:bg-orange-50 hover:text-orange-700 transition-colors group"
                        @click="userMenuOpen = false"
                      >
                        <div class="w-8 h-8 rounded-lg bg-gray-100 group-hover:bg-orange-100 flex items-center justify-center mr-3 transition-colors">
                          <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                          </svg>
                        </div>
                        <div>
                          <p class="font-medium">Nouvelle candidature</p>
                          <p class="text-xs text-gray-500 group-hover:text-orange-600">Créer une demande</p>
                        </div>
                      </router-link>

                      <!-- Dashboard -->
                      <router-link
                        to="/dashboard"
                        class="flex items-center px-4 py-3 text-sm text-gray-700 hover:bg-orange-50 hover:text-orange-700 transition-colors group"
                        @click="userMenuOpen = false"
                      >
                        <div class="w-8 h-8 rounded-lg bg-gray-100 group-hover:bg-orange-100 flex items-center justify-center mr-3 transition-colors">
                          <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
                          </svg>
                        </div>
                        <div>
                          <p class="font-medium">Tableau de bord</p>
                          <p class="text-xs text-gray-500 group-hover:text-orange-600">Vue d'ensemble des candidatures</p>
                        </div>
                      </router-link>
                      
                      <!-- Notifications -->
                      <router-link
                        to="/notifications"
                        class="flex items-center px-4 py-3 text-sm text-gray-700 hover:bg-orange-50 hover:text-orange-700 transition-colors group"
                        @click="userMenuOpen = false"
                      >
                        <div class="w-8 h-8 rounded-lg bg-gray-100 group-hover:bg-orange-100 flex items-center justify-center mr-3 transition-colors">
                          <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-5 5v-5zM11 17H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V11" />
                          </svg>
                        </div>
                        <div>
                          <p class="font-medium">Notifications</p>
                          <p class="text-xs text-gray-500 group-hover:text-orange-600">Messages et alertes</p>
                        </div>
                      </router-link>
                      
                      <!-- Créer utilisateur (admin seulement) -->
                      <router-link
                        v-if="authStore.isAdmin"
                        to="/register"
                        class="flex items-center px-4 py-3 text-sm text-gray-700 hover:bg-orange-50 hover:text-orange-700 transition-colors group"
                        @click="userMenuOpen = false"
                      >
                        <div class="w-8 h-8 rounded-lg bg-gray-100 group-hover:bg-orange-100 flex items-center justify-center mr-3 transition-colors">
                          <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6" />
                          </svg>
                        </div>
                        <div>
                          <p class="font-medium">Créer un utilisateur</p>
                          <p class="text-xs text-gray-500 group-hover:text-orange-600">Administration</p>
                        </div>
                      </router-link>
                    </div>

                    <!-- Séparateur -->
                    <div class="border-t border-gray-100 my-1"></div>

                    <!-- Mode sombre -->
                    <button
                      @click="toggleDarkMode(); haptic.light()"
                      class="w-full flex items-center px-4 py-3 text-sm text-gray-700 hover:bg-orange-50 hover:text-orange-700 transition-colors group"
                    >
                      <div class="w-8 h-8 rounded-lg bg-gray-100 group-hover:bg-orange-100 flex items-center justify-center mr-3 transition-colors">
                        <svg v-if="isDark" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 3v1m0 16v1m9-9h-1M4 12H3m15.364 6.364l-.707-.707M6.343 6.343l-.707-.707m12.728 0l-.707.707M6.343 17.657l-.707.707M16 12a4 4 0 11-8 0 4 4 0 018 0z" />
                        </svg>
                        <svg v-else class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20.354 15.354A9 9 0 018.646 3.646 9.003 9.003 0 0012 21a9.003 9.003 0 008.354-5.646z" />
                        </svg>
                      </div>
                      <div>
                        <p class="font-medium">{{ isDark ? 'Mode clair' : 'Mode sombre' }}</p>
                        <p class="text-xs text-gray-500 group-hover:text-orange-600">Changer le thème</p>
                      </div>
                    </button>

                    <!-- Déconnexion -->
                    <button
                      @click="handleLogout"
                      class="w-full flex items-center px-4 py-3 text-sm text-red-600 hover:bg-red-50 transition-colors group"
                    >
                      <div class="w-8 h-8 rounded-lg bg-red-100 flex items-center justify-center mr-3">
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1" />
                        </svg>
                      </div>
                      <div>
                        <p class="font-medium">Déconnexion</p>
                        <p class="text-xs text-red-500">Fermer la session</p>
                      </div>
                    </button>
                  </div>
                  <div v-else>
                    <!-- Connexion -->
                    <router-link
                      to="/login"
                      class="flex items-center px-4 py-3 text-sm text-gray-700 hover:bg-orange-50 hover:text-orange-700 transition-colors group"
                      @click="userMenuOpen = false"
                    >
                      <div class="w-8 h-8 rounded-lg bg-gray-100 group-hover:bg-orange-100 flex items-center justify-center mr-3 transition-colors">
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 16l-4-4m0 0l4-4m-4 4h14m-5 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h7a3 3 0 013 3v1" />
                        </svg>
                      </div>
                      <div>
                        <p class="font-medium">Connexion</p>
                        <p class="text-xs text-gray-500 group-hover:text-orange-600">Accéder à votre compte</p>
                      </div>
                    </router-link>
                  </div>
                </div>
              </transition>
            </div>
          </div>
        </div>
      </div>
    </header>

    <!-- Bannière mode hors ligne -->
    <transition
      enter-active-class="transition ease-out duration-300"
      enter-from-class="transform -translate-y-full opacity-0"
      enter-to-class="transform translate-y-0 opacity-100"
      leave-active-class="transition ease-in duration-200"
      leave-from-class="transform translate-y-0 opacity-100"
      leave-to-class="transform -translate-y-full opacity-0">
      <div v-if="!isOnline" class="bg-yellow-50 border-b border-yellow-200 sticky top-16 z-30">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-3">
          <div class="flex items-center justify-center space-x-3">
            <svg class="w-5 h-5 text-yellow-600" fill="currentColor" viewBox="0 0 20 20">
              <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd"></path>
            </svg>
            <div class="text-sm">
              <span class="font-medium text-yellow-800">Mode hors ligne</span>
              <span class="text-yellow-700 ml-2">• Vos données seront sauvegardées localement et envoyées une fois reconnecté</span>
            </div>
          </div>
        </div>
      </div>
    </transition>

    <!-- Contenu principal -->
    <main class="flex-1 flex flex-col">
      <router-view />
    </main>

    <!-- Footer moderne -->
    <footer class="bg-gray-50 border-t border-gray-200 mt-auto">
      <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="py-8">
          <div class="flex flex-col md:flex-row items-center justify-between space-y-4 md:space-y-0">
            <!-- Logo et info -->
            <div class="flex items-center space-x-3">
              <div class="w-8 h-8 rounded-lg bg-gradient-to-br from-orange-500 to-orange-600 flex items-center justify-center">
                <img src="/logo.png" alt="Moov Money Logo" class="w-12 h-12 object-contain">
              </div>
              <div>
                <h3 class="text-sm font-semibold text-gray-900">Moov Money</h3>
                <p class="text-xs text-gray-500">Recrutement Marchand</p>
              </div>
            </div>
            
            <!-- Copyright et crédit -->
            <div class="text-center md:text-right">
              <p class="text-sm text-gray-600">
                &copy; {{ currentYear }} Moov Money Togo. Tous droits réservés.
              </p>
              <p class="text-xs text-gray-500 mt-1">
                Crafted with <span class="text-red-500">❤️</span> by David D.
              </p>
            </div>
          </div>
        </div>
      </div>
    </footer>

    <!-- Conteneur de notifications -->
    <NotificationContainer />
  </div>
</template>

