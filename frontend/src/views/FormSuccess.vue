<template>
  <div class="form-container">
    <!-- Cercles décoratifs -->
    <div class="decorative-circle circle-1"></div>
    <div class="decorative-circle circle-2"></div>
    <div class="decorative-circle circle-3"></div>
    <div class="decorative-circle circle-4"></div>
    <div class="decorative-circle circle-5"></div>
    <div class="decorative-circle circle-6"></div>

    <!-- Carte de succès -->
    <div class="form-card fade-in">
      <div class="text-center">
        <!-- Icône de succès -->
        <div class="mx-auto w-24 h-24 bg-green-100 rounded-full flex items-center justify-center mb-6 animate-bounce">
          <i class="fas fa-check-circle text-4xl text-green-500"></i>
        </div>
        
        <!-- Message principal -->
        <h1 class="text-3xl font-bold text-gray-900 mb-4">
          Demande envoyée avec succès !
        </h1>
        
        <p class="text-lg text-gray-600 mb-8">
          Candidature bien reçue. 
          Notre équipe l'examinera dans les plus brefs délais.
        </p>
        
        <!-- Informations sur les prochaines étapes -->
        <div class="bg-orange-50 border-2 border-orange-200 rounded-2xl p-6 mb-8 text-left">
          <h2 class="text-lg font-semibold text-orange-900 mb-4">
            <i class="fas fa-info-circle mr-2"></i>
            Prochaines étapes
          </h2>
          
          <div class="space-y-3 text-orange-800">
            <div class="flex items-start space-x-3">
              <span class="flex-shrink-0 w-6 h-6 bg-orange-200 rounded-full flex items-center justify-center text-xs font-bold">1</span>
              <p>Vérification du dossier (1-2 jours ouvrables)</p>
            </div>
            <div class="flex items-start space-x-3">
              <span class="flex-shrink-0 w-6 h-6 bg-orange-200 rounded-full flex items-center justify-center text-xs font-bold">2</span>
              <p>Contact par notre équipe pour validation</p>
            </div>
            <div class="flex items-start space-x-3">
              <span class="flex-shrink-0 w-6 h-6 bg-orange-200 rounded-full flex items-center justify-center text-xs font-bold">3</span>
              <p>Formation et activation de votre compte marchand</p>
            </div>
          </div>
        </div>
        
        <!-- Boutons d'action -->
        <div class="flex flex-col sm:flex-row gap-4 justify-center">
          <router-link 
            v-if="applicationId" 
            :to="`/applications/${applicationId}`" 
            class="btn-primary">
            <i class="fas fa-eye mr-2"></i>
            Voir ma candidature
          </router-link>
          
          <router-link to="/formulaire" class="btn-secondary">
            <i class="fas fa-plus mr-2"></i>
            Nouvelle candidature
          </router-link>
          
          <a href="tel:+22899990111" class="btn-secondary">
            <i class="fas fa-phone mr-2"></i>
            Nous contacter
          </a>
        </div>
        
        <!-- Référence de la demande -->
        <div class="mt-8 p-4 bg-gray-50 rounded-lg">
          <p class="text-sm text-gray-600">
            Référence de votre demande: <span class="font-mono font-semibold">#{{ applicationRef }}</span>
          </p>
          <p class="text-xs text-gray-500 mt-1">
            Conservez cette référence pour le suivi de votre dossier
          </p>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import { useRoute } from 'vue-router'

const route = useRoute()
const applicationRef = ref('')
const applicationId = ref(null)

onMounted(() => {
  // Récupérer l'ID de l'application depuis la query
  applicationId.value = route.query.applicationId
  
  // Générer une référence de demande
  const timestamp = new Date().getTime()
  const random = Math.random().toString(36).substr(2, 5).toUpperCase()
  applicationRef.value = `MM${timestamp.toString().slice(-6)}${random}`
})
</script>

<style scoped>
@keyframes bounce {
  0%, 100% {
    transform: translateY(0);
  }
  50% {
    transform: translateY(-10px);
  }
}

.animate-bounce {
  animation: bounce 1s ease-in-out 3;
}
</style>