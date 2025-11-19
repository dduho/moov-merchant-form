<template>
  <div class="completion-indicator bg-white dark:bg-gray-800 rounded-xl shadow-lg p-4 transition-all duration-300">
    <div class="flex items-center justify-between mb-2">
      <h3 class="text-sm font-semibold text-gray-700 dark:text-gray-200">
        Complétude du formulaire
      </h3>
      <span class="text-lg font-bold" :class="scoreColor">
        {{ score }}%
      </span>
    </div>

    <!-- Barre de progression -->
    <div class="w-full bg-gray-200 dark:bg-gray-700 rounded-full h-3 overflow-hidden">
      <div 
        class="h-full rounded-full transition-all duration-500 ease-out"
        :class="progressBarColor"
        :style="{ width: `${score}%` }"
      ></div>
    </div>

    <!-- Stats détaillées -->
    <div v-if="showDetails" class="mt-3 grid grid-cols-2 gap-2 text-xs">
      <div class="flex items-center space-x-2">
        <i class="fas fa-check-circle text-green-500"></i>
        <span class="text-gray-600 dark:text-gray-400">Valides: {{ stats.valid }}</span>
      </div>
      <div class="flex items-center space-x-2">
        <i class="fas fa-times-circle text-red-500"></i>
        <span class="text-gray-600 dark:text-gray-400">Invalides: {{ stats.invalid }}</span>
      </div>
      <div class="flex items-center space-x-2">
        <i class="fas fa-spinner fa-spin text-blue-500"></i>
        <span class="text-gray-600 dark:text-gray-400">En cours: {{ stats.validating }}</span>
      </div>
      <div class="flex items-center space-x-2">
        <i class="fas fa-circle text-gray-400"></i>
        <span class="text-gray-600 dark:text-gray-400">Restants: {{ stats.idle }}</span>
      </div>
    </div>

    <!-- Message encouragement -->
    <p v-if="score === 100" class="mt-3 text-sm text-green-600 dark:text-green-400 font-medium">
      <i class="fas fa-trophy mr-1"></i>
      Formulaire complet ! Vous pouvez le soumettre.
    </p>
    <p v-else-if="score >= 75" class="mt-3 text-sm text-blue-600 dark:text-blue-400">
      <i class="fas fa-thumbs-up mr-1"></i>
      Presque terminé ! Plus que {{ 100 - score }}%
    </p>
    <p v-else-if="score >= 50" class="mt-3 text-sm text-orange-600 dark:text-orange-400">
      <i class="fas fa-info-circle mr-1"></i>
      Vous êtes à mi-chemin !
    </p>
    <p v-else class="mt-3 text-sm text-gray-600 dark:text-gray-400">
      <i class="fas fa-pencil-alt mr-1"></i>
      Continuez à remplir le formulaire
    </p>
  </div>
</template>

<script setup>
import { computed } from 'vue'

const props = defineProps({
  score: {
    type: Number,
    required: true,
    default: 0
  },
  stats: {
    type: Object,
    default: () => ({
      total: 0,
      validating: 0,
      valid: 0,
      invalid: 0,
      idle: 0
    })
  },
  showDetails: {
    type: Boolean,
    default: true
  }
})

const scoreColor = computed(() => {
  if (props.score === 100) return 'text-green-600 dark:text-green-400'
  if (props.score >= 75) return 'text-blue-600 dark:text-blue-400'
  if (props.score >= 50) return 'text-orange-600 dark:text-orange-400'
  return 'text-gray-600 dark:text-gray-400'
})

const progressBarColor = computed(() => {
  if (props.score === 100) return 'bg-gradient-to-r from-green-500 to-green-600'
  if (props.score >= 75) return 'bg-gradient-to-r from-blue-500 to-blue-600'
  if (props.score >= 50) return 'bg-gradient-to-r from-orange-500 to-orange-600'
  return 'bg-gradient-to-r from-gray-400 to-gray-500'
})
</script>

<style scoped>
.completion-indicator {
  position: sticky;
  top: 80px;
  z-index: 30;
}

@media (max-width: 640px) {
  .completion-indicator {
    position: relative;
    top: 0;
  }
}
</style>
