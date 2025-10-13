<template>
  <div class="min-h-screen bg-gray-50 py-6">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
      <!-- Header -->
      <div class="mb-8">
        <div class="md:flex md:items-center md:justify-between">
          <div class="flex-1 min-w-0">
            <div class="flex items-center mb-2">
              <button
                @click="$router.push('/dashboard')"
                class="mr-3 px-3 py-1.5 text-sm text-gray-600 hover:text-gray-900 hover:bg-gray-100 rounded-md transition-colors duration-200 flex items-center"
              >
                <i class="fas fa-arrow-left mr-2"></i>
                Retour au dashboard
              </button>
            </div>
            <h1 class="text-2xl font-bold leading-7 text-gray-900 sm:text-3xl sm:truncate">
              <i class="fas fa-users text-orange-600 mr-3"></i>
              Gestion des Utilisateurs
            </h1>
            <p class="mt-1 text-sm text-gray-500">
              Gérez les utilisateurs, leurs accès et leurs permissions
            </p>
          </div>
        </div>
      </div>

      <!-- Filters -->
      <div class="bg-white shadow rounded-lg mb-6 p-4">
        <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Rechercher</label>
            <div class="relative">
              <input
                v-model="filters.search"
                type="text"
                class="block w-full pl-10 pr-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-orange-500 focus:border-orange-500"
                placeholder="Nom, email, téléphone..."
              >
              <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                <i class="fas fa-search text-gray-400"></i>
              </div>
            </div>
          </div>

          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Rôle</label>
            <select
              v-model="filters.role"
              class="block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-orange-500 focus:border-orange-500"
            >
              <option value="">Tous les rôles</option>
              <option value="admin">Administrateur</option>
              <option value="commercial">Commercial</option>
            </select>
          </div>

          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Statut</label>
            <select
              v-model="filters.status"
              class="block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-orange-500 focus:border-orange-500"
            >
              <option value="">Tous les statuts</option>
              <option value="active">Actif</option>
              <option value="blocked">Bloqué</option>
              <option value="inactive">Inactif</option>
            </select>
          </div>

          <div class="flex items-end">
            <button
              @click="clearFilters"
              class="w-full px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-md shadow-sm hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-orange-500"
            >
              <i class="fas fa-times mr-2"></i>
              Effacer
            </button>
          </div>
        </div>
      </div>

      <!-- Loading State -->
      <div v-if="isLoading" class="bg-white shadow rounded-lg p-8">
        <div class="text-center">
          <i class="fas fa-spinner fa-spin text-3xl text-gray-400 mb-4"></i>
          <p class="text-gray-500">Chargement des utilisateurs...</p>
        </div>
      </div>

      <!-- Error State -->
      <div v-else-if="error" class="bg-white shadow rounded-lg p-8">
        <div class="text-center">
          <i class="fas fa-exclamation-circle text-3xl text-red-500 mb-4"></i>
          <p class="text-red-600">{{ error }}</p>
          <button
            @click="loadUsers"
            class="mt-4 px-4 py-2 bg-orange-600 text-white rounded-md hover:bg-orange-700 focus:outline-none focus:ring-2 focus:ring-orange-500"
          >
            <i class="fas fa-redo mr-2"></i>
            Réessayer
          </button>
        </div>
      </div>

      <!-- Users Table -->
      <div v-else class="bg-white shadow rounded-lg overflow-hidden">
        <!-- Table Header -->
        <div class="px-6 py-4 border-b border-gray-200 bg-gray-50">
          <div class="flex items-center justify-between">
            <h3 class="text-lg font-medium text-gray-900">
              Utilisateurs ({{ users.length }})
            </h3>
          </div>
        </div>

        <!-- Table Content -->
        <div class="overflow-x-auto">
          <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
              <tr>
                <th class="px-4 sm:px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                  Utilisateur
                </th>
                <th class="hidden sm:table-cell px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                  Rôle
                </th>
                <th class="hidden md:table-cell px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                  Statut
                </th>
                <th class="hidden lg:table-cell px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                  Dernière connexion
                </th>
                <th class="hidden xl:table-cell px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                  Performance
                </th>
                <th class="px-4 sm:px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">
                  Actions
                </th>
              </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
              <tr v-for="user in users" :key="user.id" class="hover:bg-gray-50">
                <!-- User Info -->
                <td class="px-4 sm:px-6 py-4 whitespace-nowrap">
                  <div class="flex items-center">
                    <div class="flex-shrink-0 h-10 w-10">
                      <div class="h-10 w-10 rounded-full bg-orange-100 flex items-center justify-center">
                        <span class="text-sm font-medium text-orange-600">
                          {{ getUserInitials(user) }}
                        </span>
                      </div>
                    </div>
                    <div class="ml-4">
                      <div class="text-sm font-medium text-gray-900">
                        {{ user.first_name }} {{ user.last_name }}
                      </div>
                      <div class="text-xs sm:text-sm text-gray-500">
                        {{ user.email }}
                      </div>
                      <div class="text-xs text-gray-400 hidden sm:block">
                        {{ user.phone }}
                      </div>
                      <!-- Show role on mobile (hidden on larger screens) -->
                      <div class="sm:hidden mt-1">
                        <span
                          v-for="role in user.roles"
                          :key="role.id"
                          class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium mr-1"
                          :class="getRoleColor(role.slug)"
                        >
                          <i :class="getRoleIcon(role.slug)" class="mr-1"></i>
                          {{ getRoleName(role.slug) }}
                        </span>
                      </div>
                    </div>
                  </div>
                </td>

                <!-- Role (hidden on mobile) -->
                <td class="hidden sm:table-cell px-6 py-4 whitespace-nowrap">
                  <span
                    v-for="role in user.roles"
                    :key="role.id"
                    class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium mr-2"
                    :class="getRoleColor(role.slug)"
                  >
                    <i :class="getRoleIcon(role.slug)" class="mr-1"></i>
                    {{ getRoleName(role.slug) }}
                  </span>
                </td>

                <!-- Status (hidden on mobile) -->
                <td class="hidden md:table-cell px-6 py-4 whitespace-nowrap">
                  <span
                    class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium"
                    :class="getUserStatusColor(user)"
                  >
                    <i :class="getUserStatusIcon(user)" class="mr-1"></i>
                    {{ getUserStatusText(user) }}
                  </span>
                  <div v-if="user.must_change_password" class="mt-1">
                    <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-yellow-100 text-yellow-800">
                      <i class="fas fa-key mr-1"></i>
                      Doit changer mot de passe
                    </span>
                  </div>
                </td>

                <!-- Last Login (hidden on tablet and below) -->
                <td class="hidden lg:table-cell px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                  {{ formatLastLogin(user.last_login_at) }}
                </td>

                <!-- Performance (hidden on mobile and tablet) -->
                <td class="hidden xl:table-cell px-6 py-4 whitespace-nowrap">
                  <div v-if="user.stats && isCommercial(user)" class="text-sm">
                    <div class="text-gray-900 font-medium">
                      {{ user.stats.applications_this_month }} ce mois
                    </div>
                    <div class="text-gray-500">
                      {{ user.stats.applications_this_year }} cette année
                    </div>
                    <div v-if="user.stats.objective" class="mt-1">
                      <div class="text-xs text-gray-400">
                        Objectif: {{ user.stats.objective.monthly_target }}/mois
                      </div>
                      <div class="w-full bg-gray-200 rounded-full h-1.5 mt-1">
                        <div
                          class="bg-orange-600 h-1.5 rounded-full"
                          :style="{ width: getProgressPercentage(user.stats) + '%' }"
                        ></div>
                      </div>
                    </div>
                  </div>
                  <span v-else class="text-sm text-gray-400">-</span>
                </td>

                <!-- Actions -->
                <td class="px-4 sm:px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                  <div class="flex items-center justify-end space-x-1 sm:space-x-2">
                    <!-- View Stats (for commercials) -->
                    <button
                      v-if="isCommercial(user)"
                      @click="viewUserStats(user)"
                      class="text-blue-600 hover:text-blue-900 p-1 text-xs sm:text-sm"
                      title="Voir les statistiques"
                    >
                      <i class="fas fa-chart-bar"></i>
                    </button>

                    <!-- Reset Password -->
                    <button
                      @click="resetPassword(user)"
                      class="text-orange-600 hover:text-orange-900 p-1 text-xs sm:text-sm"
                      title="Réinitialiser le mot de passe"
                    >
                      <i class="fas fa-key"></i>
                    </button>

                    <!-- Toggle Block -->
                    <button
                      @click="toggleBlock(user)"
                      :class="user.is_blocked ? 'text-green-600 hover:text-green-900' : 'text-red-600 hover:text-red-900'"
                      class="p-1 text-xs sm:text-sm"
                      :title="user.is_blocked ? 'Débloquer' : 'Bloquer'"
                    >
                      <i :class="user.is_blocked ? 'fas fa-unlock' : 'fas fa-lock'"></i>
                    </button>

                    <!-- Toggle Active -->
                    <button
                      @click="toggleActive(user)"
                      :class="user.is_active ? 'text-gray-600 hover:text-gray-900' : 'text-green-600 hover:text-green-900'"
                      class="p-1 text-xs sm:text-sm"
                      :title="user.is_active ? 'Désactiver' : 'Activer'"
                    >
                      <i :class="user.is_active ? 'fas fa-user-slash' : 'fas fa-user-check'"></i>
                    </button>
                  </div>
                </td>
              </tr>
            </tbody>
          </table>

          <!-- Empty State -->
          <div v-if="users.length === 0" class="text-center py-12">
            <i class="fas fa-users text-4xl text-gray-400 mb-4"></i>
            <h3 class="text-lg font-medium text-gray-900 mb-2">Aucun utilisateur trouvé</h3>
            <p class="text-gray-500">Essayez de modifier vos filtres de recherche.</p>
          </div>
        </div>

        <!-- Pagination -->
        <div v-if="pagination && pagination.total > 0" class="bg-white px-4 py-3 border-t border-gray-200 sm:px-6">
          <div class="flex items-center justify-between">
            <!-- Mobile Pagination -->
            <div class="flex-1 flex justify-between sm:hidden">
              <button
                @click="loadUsers(pagination.current_page - 1)"
                :disabled="pagination.current_page <= 1"
                class="relative inline-flex items-center px-4 py-2 border border-gray-300 text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 disabled:opacity-50 disabled:cursor-not-allowed"
              >
                Précédent
              </button>
              <div class="text-sm text-gray-700 flex items-center">
                Page {{ pagination.current_page }} / {{ pagination.last_page }}
              </div>
              <button
                @click="loadUsers(pagination.current_page + 1)"
                :disabled="pagination.current_page >= pagination.last_page"
                class="ml-3 relative inline-flex items-center px-4 py-2 border border-gray-300 text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 disabled:opacity-50 disabled:cursor-not-allowed"
              >
                Suivant
              </button>
            </div>

            <!-- Desktop Pagination -->
            <div class="hidden sm:flex-1 sm:flex sm:items-center sm:justify-between">
              <div>
                <p class="text-sm text-gray-700">
                  Affichage de
                  <span class="font-medium">{{ (pagination.current_page - 1) * pagination.per_page + 1 }}</span>
                  à
                  <span class="font-medium">{{ Math.min(pagination.current_page * pagination.per_page, pagination.total) }}</span>
                  sur
                  <span class="font-medium">{{ pagination.total }}</span>
                  résultats
                </p>
              </div>
              <div>
                <nav class="relative z-0 inline-flex rounded-md shadow-sm -space-x-px" aria-label="Pagination">
                  <!-- Previous Button -->
                  <button
                    @click="loadUsers(pagination.current_page - 1)"
                    :disabled="pagination.current_page <= 1"
                    class="relative inline-flex items-center px-3 py-2 rounded-l-md border border-gray-300 bg-white text-sm font-medium text-gray-500 hover:bg-gray-50 disabled:opacity-50 disabled:cursor-not-allowed"
                  >
                    <i class="fas fa-chevron-left mr-1"></i>
                    Précédent
                  </button>

                  <!-- Page Numbers -->
                  <template v-for="page in getPageNumbers()" :key="page">
                    <button
                      v-if="page !== '...'"
                      @click="loadUsers(page)"
                      :class="[
                        'relative inline-flex items-center px-4 py-2 border text-sm font-medium',
                        page === pagination.current_page
                          ? 'z-10 bg-orange-50 border-orange-500 text-orange-600'
                          : 'bg-white border-gray-300 text-gray-500 hover:bg-gray-50'
                      ]"
                    >
                      {{ page }}
                    </button>
                    <span
                      v-else
                      class="relative inline-flex items-center px-4 py-2 border border-gray-300 bg-white text-sm font-medium text-gray-700"
                    >
                      ...
                    </span>
                  </template>

                  <!-- Next Button -->
                  <button
                    @click="loadUsers(pagination.current_page + 1)"
                    :disabled="pagination.current_page >= pagination.last_page"
                    class="relative inline-flex items-center px-3 py-2 rounded-r-md border border-gray-300 bg-white text-sm font-medium text-gray-500 hover:bg-gray-50 disabled:opacity-50 disabled:cursor-not-allowed"
                  >
                    Suivant
                    <i class="fas fa-chevron-right ml-1"></i>
                  </button>
                </nav>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Modals -->
    <UserStatsModal
      :show="showStatsModal"
      :user="selectedUser"
      @close="showStatsModal = false"
    />

    <ResetPasswordModal
      :show="showResetPasswordModal"
      :user="selectedUser"
      @close="showResetPasswordModal = false"
      @success="handlePasswordReset"
    />
  </div>
</template>

<script>
import { ref, computed, watch, onMounted } from 'vue'
import { useUserManagementStore } from '../stores/userManagement'
import { useNotificationStore } from '../stores/notifications'
import UserStatsModal from '../components/UserStatsModal.vue'
import ResetPasswordModal from '../components/ResetPasswordModal.vue'

export default {
  name: 'UserManagement',
  components: {
    UserStatsModal,
    ResetPasswordModal
  },
  setup() {
    const userStore = useUserManagementStore()
    const notificationStore = useNotificationStore()

    // State
    const showStatsModal = ref(false)
    const showResetPasswordModal = ref(false)
    const selectedUser = ref(null)

    // Computed
    const users = computed(() => userStore.users)
    const pagination = computed(() => userStore.pagination)
    const isLoading = computed(() => userStore.isLoading)
    const error = computed(() => userStore.error)
    const filters = computed(() => userStore.filters)

    // Methods
    const loadUsers = (page = 1) => {
      userStore.fetchUsers({ page })
    }

    const clearFilters = () => {
      userStore.clearFilters()
      loadUsers()
    }

    const getUserInitials = (user) => {
      return `${user.first_name?.charAt(0) || ''}${user.last_name?.charAt(0) || ''}`.toUpperCase()
    }

    const getRoleColor = (roleSlug) => {
      switch (roleSlug) {
        case 'admin':
          return 'bg-purple-100 text-purple-800'
        case 'commercial':
          return 'bg-blue-100 text-blue-800'
        default:
          return 'bg-gray-100 text-gray-800'
      }
    }

    const getRoleIcon = (roleSlug) => {
      switch (roleSlug) {
        case 'admin':
          return 'fas fa-crown'
        case 'commercial':
          return 'fas fa-handshake'
        default:
          return 'fas fa-user'
      }
    }

    const getRoleName = (roleSlug) => {
      switch (roleSlug) {
        case 'admin':
          return 'Administrateur'
        case 'commercial':
          return 'Commercial'
        default:
          return roleSlug
      }
    }

    const getUserStatusColor = (user) => userStore.getUserStatusColor(user)
    const getUserStatusText = (user) => userStore.getUserStatusText(user)

    const getUserStatusIcon = (user) => {
      if (user.is_blocked) return 'fas fa-ban'
      if (!user.is_active) return 'fas fa-user-slash'
      return 'fas fa-check-circle'
    }

    const isCommercial = (user) => {
      return user.roles?.some(role => role.slug === 'commercial')
    }

    const formatLastLogin = (lastLogin) => {
      if (!lastLogin) return 'Jamais'
      const date = new Date(lastLogin)
      return date.toLocaleDateString('fr-FR', {
        day: '2-digit',
        month: '2-digit',
        year: 'numeric',
        hour: '2-digit',
        minute: '2-digit'
      })
    }

    const getProgressPercentage = (stats) => {
      if (!stats.objective || !stats.objective.monthly_target) return 0
      return Math.min(100, (stats.applications_this_month / stats.objective.monthly_target) * 100)
    }

    const getPageNumbers = () => {
      const pages = []
      const current = pagination.value.current_page
      const last = pagination.value.last_page

      if (last <= 7) {
        // Si 7 pages ou moins, afficher toutes les pages
        for (let i = 1; i <= last; i++) {
          pages.push(i)
        }
      } else {
        // Toujours afficher la première page
        pages.push(1)

        if (current > 3) {
          pages.push('...')
        }

        // Pages autour de la page courante
        const start = Math.max(2, current - 1)
        const end = Math.min(last - 1, current + 1)

        for (let i = start; i <= end; i++) {
          pages.push(i)
        }

        if (current < last - 2) {
          pages.push('...')
        }

        // Toujours afficher la dernière page
        pages.push(last)
      }

      return pages
    }

    const viewUserStats = (user) => {
      selectedUser.value = user
      showStatsModal.value = true
    }

    const resetPassword = (user) => {
      selectedUser.value = user
      showResetPasswordModal.value = true
    }

    const handlePasswordReset = () => {
      showResetPasswordModal.value = false
      loadUsers(pagination.value.current_page)
    }

    const toggleBlock = async (user) => {
      try {
        await userStore.toggleUserBlock(user.id)
        notificationStore.success(
          user.is_blocked ? 'Utilisateur débloqué' : 'Utilisateur bloqué',
          `${user.first_name} ${user.last_name} a été ${user.is_blocked ? 'débloqué' : 'bloqué'}.`
        )
      } catch (error) {
        notificationStore.error(
          'Erreur',
          'Une erreur est survenue lors du changement de statut'
        )
      }
    }

    const toggleActive = async (user) => {
      try {
        await userStore.toggleUserActive(user.id)
        notificationStore.success(
          user.is_active ? 'Utilisateur désactivé' : 'Utilisateur activé',
          `${user.first_name} ${user.last_name} a été ${user.is_active ? 'désactivé' : 'activé'}.`
        )
      } catch (error) {
        notificationStore.error(
          'Erreur',
          'Une erreur est survenue lors du changement de statut'
        )
      }
    }

    // Watch filters
    watch(filters, () => {
      loadUsers()
    }, { deep: true })

    // Lifecycle
    onMounted(() => {
      loadUsers()
    })

    return {
      // Data
      users,
      pagination,
      isLoading,
      error,
      filters,
      showStatsModal,
      showResetPasswordModal,
      selectedUser,

      // Methods
      loadUsers,
      clearFilters,
      getUserInitials,
      getRoleColor,
      getRoleIcon,
      getRoleName,
      getUserStatusColor,
      getUserStatusText,
      getUserStatusIcon,
      isCommercial,
      formatLastLogin,
      getProgressPercentage,
      getPageNumbers,
      viewUserStats,
      resetPassword,
      handlePasswordReset,
      toggleBlock,
      toggleActive
    }
  }
}
</script>