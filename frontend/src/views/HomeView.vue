<template>
  <div class="form-container">
    <!-- Cercles décoratifs -->
    <div class="decorative-circle circle-1"></div>
    <div class="decorative-circle circle-2"></div>
    <div class="decorative-circle circle-3"></div>
    <div class="decorative-circle circle-4"></div>
    <div class="decorative-circle circle-5"></div>
    <div class="decorative-circle circle-6"></div>

    <!-- Carte du formulaire -->
    <div class="form-card fade-in">
      <!-- En-tête -->
      <div class="form-header">
        <div class="w-20 h-20 bg-gradient-to-br from-orange-400 to-orange-500 rounded-full flex items-center justify-center mx-auto mb-4 shadow-lg">
          <svg class="w-12 h-12 text-white" fill="currentColor" viewBox="0 0 20 20">
            <path d="M10.707 2.293a1 1 0 00-1.414 0l-7 7a1 1 0 001.414 1.414L4 10.414V17a1 1 0 001 1h2a1 1 0 001-1v-2a1 1 0 011-1h2a1 1 0 011 1v2a1 1 0 001 1h2a1 1 0 001-1v-6.586l.293.293a1 1 0 001.414-1.414l-7-7z" />
          </svg>
        </div>
        
        <h1 class="form-title">Formulaire de Recrutement</h1>
        <p class="form-subtitle">Devenez partenaire marchand Moov Money</p>
      </div>

      <!-- Informations importantes -->
      <div class="bg-orange-50 border-l-4 border-orange-500 rounded-lg p-4 mb-6">
        <h3 class="font-semibold text-gray-800 mb-2 flex items-center gap-2">
          <svg class="w-5 h-5 text-orange-500" fill="currentColor" viewBox="0 0 20 20">
            <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd" />
          </svg>
          Documents à préparer
        </h3>
        <ul class="text-sm text-gray-700 space-y-1">
          <li>• Carte d'identité ou passeport</li>
          <li>• Justificatif de domicile (- 3 mois)</li>
          <li>• Documents d'activité commerciale</li>
          <li>• Photos de votre boutique</li>
        </ul>
      </div>

      <!-- Fonctionnalités -->
      <div class="grid grid-cols-3 gap-4 mb-8">
        <div class="text-center">
          <div class="w-12 h-12 bg-blue-100 rounded-xl flex items-center justify-center mx-auto mb-2">
            <svg class="w-6 h-6 text-blue-600" fill="currentColor" viewBox="0 0 20 20">
              <path d="M2 6a2 2 0 012-2h6a2 2 0 012 2v8a2 2 0 01-2 2H4a2 2 0 01-2-2V6zM14.553 7.106A1 1 0 0014 8v4a1 1 0 00.553.894l2 1A1 1 0 0018 13V7a1 1 0 00-1.447-.894l-2 1z" />
            </svg>
          </div>
          <p class="text-xs font-medium text-gray-700">Simple</p>
        </div>

        <div class="text-center">
          <div class="w-12 h-12 bg-green-100 rounded-xl flex items-center justify-center mx-auto mb-2">
            <svg class="w-6 h-6 text-green-600" fill="currentColor" viewBox="0 0 20 20">
              <path fill-rule="evenodd" d="M5 9V7a5 5 0 0110 0v2a2 2 0 012 2v5a2 2 0 01-2 2H5a2 2 0 01-2-2v-5a2 2 0 012-2zm8-2v2H7V7a3 3 0 016 0z" clip-rule="evenodd" />
            </svg>
          </div>
          <p class="text-xs font-medium text-gray-700">Sécurisé</p>
        </div>

        <div class="text-center">
          <div class="w-12 h-12 bg-purple-100 rounded-xl flex items-center justify-center mx-auto mb-2">
            <svg class="w-6 h-6 text-purple-600" fill="currentColor" viewBox="0 0 20 20">
              <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z" />
            </svg>
          </div>
          <p class="text-xs font-medium text-gray-700">Rapide</p>
        </div>
      </div>

      <!-- Mode hors ligne -->
      <div class="bg-blue-50 rounded-xl p-4 mb-6 flex items-start gap-3">
        <div class="w-10 h-10 bg-blue-500 rounded-full flex items-center justify-center flex-shrink-0">
          <svg class="w-6 h-6 text-white" fill="currentColor" viewBox="0 0 20 20">
            <path fill-rule="evenodd" d="M17.778 8.222c-4.296-4.296-11.26-4.296-15.556 0A1 1 0 01.808 6.808c5.076-5.077 13.308-5.077 18.384 0a1 1 0 01-1.414 1.414zM14.95 11.05a7 7 0 00-9.9 0 1 1 0 01-1.414-1.414 9 9 0 0112.728 0 1 1 0 01-1.414 1.414zM12.12 13.88a3 3 0 00-4.242 0 1 1 0 01-1.415-1.415 5 5 0 017.072 0 1 1 0 01-1.415 1.415zM9 16a1 1 0 011-1h.01a1 1 0 110 2H10a1 1 0 01-1-1z" clip-rule="evenodd" />
          </svg>
        </div>
        <div class="flex-1">
          <h4 class="font-semibold text-gray-800 mb-1 text-sm">Mode hors ligne disponible</h4>
          <p class="text-sm text-gray-600">
            Remplissez le formulaire sans connexion. Vos données seront envoyées automatiquement une fois reconnecté.
          </p>
        </div>
      </div>

      <!-- Bouton CTA -->
      <button v-if="authStore.isAuthenticated" @click="goToForm" class="btn-primary">
        Initier une demande
      </button>

      <!-- Lien aide -->
      <div v-if="!authStore.isAuthenticated" class="text-center mt-6">
        <router-link to="/login" class="bg-transparent hover:bg-blue-500 text-blue-700 font-semibold hover:text-white py-2 px-4 border border-blue-500 hover:border-transparent rounded text-sm transition-colors">
          <span class="font-semibold">Se connecter</span>
        </router-link>
      </div>
    </div>
  </div>
</template>

<script>
import { useAuthStore } from '../stores/auth'
export default {
  name: 'HomeView',
  setup() {
    const authStore = useAuthStore()
    return { authStore }
  },
  methods: {
    goToForm() {
      this.$router.push('/formulaire')
    }
  }
}
</script>

<style scoped>
.btn {
  display: inline-flex;
  align-items: center;
  justify-content: center;
  border-radius: 0.75rem;
  font-weight: 500;
  transition: all 0.2s;
}

.btn-lg {
  padding: 0.875rem 1.5rem;
  font-size: 1rem;
}

.animate-fade-in {
  animation: fadeIn 0.5s ease-out;
}

.animate-slide-up {
  animation: slideUp 0.5s ease-out;
}

@keyframes fadeIn {
  from { opacity: 0; }
  to { opacity: 1; }
}

@keyframes slideUp {
  from { 
    opacity: 0; 
    transform: translateY(20px); 
  }
  to { 
    opacity: 1; 
    transform: translateY(0); 
  }
}
</style>