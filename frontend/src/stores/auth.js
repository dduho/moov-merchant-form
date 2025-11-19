import { defineStore } from 'pinia'
import axios from 'axios'

export const useAuthStore = defineStore('auth', {
  state: () => ({
    user: JSON.parse(localStorage.getItem('auth_user')) || null,
    loading: false,
    error: null
  }),

  getters: {
    isAuthenticated: (state) => !!state.user,
    isAdmin: (state) => state.user?.roles?.includes('admin') ?? false,
    isCommercial: (state) => state.user?.roles?.includes('commercial') ?? false,
    userFullName: (state) => state.user ? `${state.user.first_name} ${state.user.last_name}` : '',
    mustChangePassword: (state) => state.user?.must_change_password ?? false,
    
    // Permissions pour les candidatures
    canViewApplications: (state) => {
      const roles = state.user?.roles || []
      return roles.includes('admin') || roles.includes('commercial')
    },
    canEditApplications: (state) => {
      const roles = state.user?.roles || []
      return roles.includes('admin') || roles.includes('commercial')
    },
    canValidateApplications: (state) => {
      const roles = state.user?.roles || []
      return roles.includes('admin')
    },
    canRejectApplications: (state) => {
      const roles = state.user?.roles || []
      return roles.includes('admin')
    },
    canDeleteApplications: (state) => {
      const roles = state.user?.roles || []
      return roles.includes('admin')
    },
    canVerifyDocuments: (state) => {
      const roles = state.user?.roles || []
      return roles.includes('admin')
    }
  },

  actions: {
    async init() {
      // Si on a un utilisateur en localStorage, vérifier s'il est toujours connecté
      if (this.user) {
        try {
          const { data } = await axios.get('/auth/me')
          this.setUser(data)
        } catch (error) {
          console.error('Session expirée:', error)
          this.clearUser()
        }
      }
    },

    setUser(userData) {
      this.user = userData
      localStorage.setItem('auth_user', JSON.stringify(userData))
    },

    clearUser() {
      this.user = null
      localStorage.removeItem('auth_user')
    },

    async login(username, password) {
      this.loading = true
      this.error = null
      try {
        // Obtenir le CSRF cookie - utiliser l'URL complète du domaine
        const baseUrl = import.meta.env.VITE_API_URL?.replace('/api', '') || window.location.origin
        await axios.get(`${baseUrl}/sanctum/csrf-cookie`, { withCredentials: true })
        
        // Faire la requête de login
        const { data } = await axios.post('/auth/login', {
          username,
          password
        })
        
        // Mettre à jour l'état et persister
        this.setUser(data.user)
        
        // Si l'utilisateur doit changer son mot de passe, retourner l'info
        if (data.must_change_password) {
          return { must_change_password: true, ...data }
        }
        
        return data
      } catch (error) {
        this.error = error.response?.data?.message || 'Une erreur est survenue'
        throw error
      } finally {
        this.loading = false
      }
    },

    async register(userData) {
      this.loading = true
      this.error = null
      try {
        const { data } = await axios.post('/auth/register', userData)
        return data
      } catch (error) {
        this.error = error.response?.data?.message || 'Une erreur est survenue'
        throw error
      } finally {
        this.loading = false
      }
    },

    async logout() {
      this.loading = true
      try {
        await axios.post('/auth/logout')
        this.clearUser()
      } catch (error) {
        console.error('Erreur lors de la déconnexion:', error)
        // Nettoyer quand même côté client
        this.clearUser()
      } finally {
        this.loading = false
      }
    },

    async fetchUser() {
      this.loading = true
      try {
        const { data } = await axios.get('/auth/me')
        this.setUser(data)
      } catch (error) {
        this.error = error.response?.data?.message || 'Une erreur est survenue'
        // Si erreur 401, déconnexion
        if (error.response?.status === 401) {
          this.clearUser()
        }
        throw error
      } finally {
        this.loading = false
      }
    },

    async updateProfile(profileData) {
      this.loading = true
      this.error = null
      try {
        const { data } = await axios.put('/auth/profile', profileData)
        this.setUser(data.user)
        return data
      } catch (error) {
        this.error = error.response?.data?.message || 'Une erreur est survenue'
        throw error
      } finally {
        this.loading = false
      }
    }
  }
})