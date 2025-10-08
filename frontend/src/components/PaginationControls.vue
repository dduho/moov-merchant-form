<template>
  <div class="flex flex-col sm:flex-row items-center justify-between gap-4 mt-6">
    <!-- Informations sur la pagination -->
    <div class="text-sm text-gray-700">
      <span class="font-medium">{{ startItem }} - {{ endItem }}</span>
      sur
      <span class="font-medium">{{ total }}</span>
      {{ total > 1 ? 'résultats' : 'résultat' }}
    </div>

    <!-- Contrôles de navigation -->
    <div class="flex items-center space-x-2">
      <!-- Page précédente -->
      <button
        @click="goToPrevious"
        :disabled="!hasPrev"
        class="relative inline-flex items-center px-3 py-2 rounded-lg text-sm font-medium transition-colors"
        :class="hasPrev 
          ? 'text-gray-700 bg-white border border-gray-300 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-orange-500' 
          : 'text-gray-400 bg-gray-100 border border-gray-200 cursor-not-allowed'"
      >
        <svg class="w-4 h-4 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
        </svg>
        Précédent
      </button>

      <!-- Numéros de page -->
      <div class="flex items-center space-x-1">
        <template v-for="page in visiblePages" :key="page">
          <button
            v-if="page !== '...'"
            @click="goToPage(page)"
            class="relative inline-flex items-center px-3 py-2 text-sm font-medium rounded-lg transition-colors"
            :class="page === currentPage
              ? 'bg-orange-600 text-white border border-orange-600 shadow-sm'
              : 'text-gray-700 bg-white border border-gray-300 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-orange-500'"
          >
            {{ page }}
          </button>
          <span v-else class="relative inline-flex items-center px-3 py-2 text-sm font-medium text-gray-700">
            ...
          </span>
        </template>
      </div>

      <!-- Page suivante -->
      <button
        @click="goToNext"
        :disabled="!hasNext"
        class="relative inline-flex items-center px-3 py-2 rounded-lg text-sm font-medium transition-colors"
        :class="hasNext 
          ? 'text-gray-700 bg-white border border-gray-300 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-orange-500' 
          : 'text-gray-400 bg-gray-100 border border-gray-200 cursor-not-allowed'"
      >
        Suivant
        <svg class="w-4 h-4 ml-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
        </svg>
      </button>
    </div>
  </div>
</template>

<script setup>
import { computed } from 'vue'

const props = defineProps({
  currentPage: {
    type: Number,
    required: true
  },
  totalPages: {
    type: Number,
    required: true
  },
  total: {
    type: Number,
    required: true
  },
  perPage: {
    type: Number,
    required: true
  },
  hasNext: {
    type: Boolean,
    required: true
  },
  hasPrev: {
    type: Boolean,
    required: true
  }
})

const emit = defineEmits(['page-change'])

// Calculs pour l'affichage des informations
const startItem = computed(() => {
  if (props.total === 0) return 0
  return (props.currentPage - 1) * props.perPage + 1
})

const endItem = computed(() => {
  return Math.min(props.currentPage * props.perPage, props.total)
})

// Calcul des pages visibles avec des ellipses intelligentes
const visiblePages = computed(() => {
  const pages = []
  const { currentPage, totalPages } = props
  
  if (totalPages <= 7) {
    // Afficher toutes les pages si moins de 7
    for (let i = 1; i <= totalPages; i++) {
      pages.push(i)
    }
  } else {
    // Logique d'ellipses pour plus de 7 pages
    pages.push(1)
    
    if (currentPage <= 4) {
      // Début : 1 2 3 4 5 ... dernier
      for (let i = 2; i <= 5; i++) {
        pages.push(i)
      }
      pages.push('...')
      pages.push(totalPages)
    } else if (currentPage >= totalPages - 3) {
      // Fin : 1 ... avant-dernier-3 avant-dernier-2 avant-dernier-1 avant-dernier dernier
      pages.push('...')
      for (let i = totalPages - 4; i <= totalPages; i++) {
        pages.push(i)
      }
    } else {
      // Milieu : 1 ... current-1 current current+1 ... dernier
      pages.push('...')
      for (let i = currentPage - 1; i <= currentPage + 1; i++) {
        pages.push(i)
      }
      pages.push('...')
      pages.push(totalPages)
    }
  }
  
  return pages
})

// Actions de navigation
const goToPage = (page) => {
  if (page !== props.currentPage && page !== '...') {
    emit('page-change', page)
  }
}

const goToPrevious = () => {
  if (props.hasPrev) {
    emit('page-change', props.currentPage - 1)
  }
}

const goToNext = () => {
  if (props.hasNext) {
    emit('page-change', props.currentPage + 1)
  }
}
</script>