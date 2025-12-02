<template>
  <div class="stats-cards grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
    <!-- Total PDV -->
    <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm p-6">
      <div class="flex items-center justify-between mb-4">
        <div class="w-12 h-12 bg-blue-100 dark:bg-blue-900 rounded-xl flex items-center justify-center">
          <svg class="w-6 h-6 text-blue-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" />
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z" />
          </svg>
        </div>
        <span v-if="trend.total" :class="[
          'inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium',
          trend.total >= 0 ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800'
        ]">
          <svg :class="['w-4 h-4 mr-1', trend.total >= 0 ? '' : 'rotate-180']" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6" />
          </svg>
          {{ Math.abs(trend.total) }}%
        </span>
      </div>
      <div>
        <h3 class="text-3xl font-bold text-gray-800 dark:text-white">
          {{ loading ? '...' : formatNumber(stats.total_pdv || 0) }}
        </h3>
        <p class="text-gray-500 dark:text-gray-400 mt-1">Total PDV</p>
      </div>
    </div>

    <!-- Validated PDV -->
    <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm p-6">
      <div class="flex items-center justify-between mb-4">
        <div class="w-12 h-12 bg-green-100 dark:bg-green-900 rounded-xl flex items-center justify-center">
          <svg class="w-6 h-6 text-green-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
          </svg>
        </div>
        <span v-if="stats.total_pdv" class="text-xs text-gray-500 dark:text-gray-400">
          {{ Math.round((stats.validated_pdv / stats.total_pdv) * 100) }}%
        </span>
      </div>
      <div>
        <h3 class="text-3xl font-bold text-gray-800 dark:text-white">
          {{ loading ? '...' : formatNumber(stats.validated_pdv || 0) }}
        </h3>
        <p class="text-gray-500 dark:text-gray-400 mt-1">PDV Validés</p>
      </div>
    </div>

    <!-- Pending PDV -->
    <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm p-6">
      <div class="flex items-center justify-between mb-4">
        <div class="w-12 h-12 bg-yellow-100 dark:bg-yellow-900 rounded-xl flex items-center justify-center">
          <svg class="w-6 h-6 text-yellow-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
          </svg>
        </div>
        <span v-if="stats.pending_pdv > 0" class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-yellow-100 text-yellow-800">
          À traiter
        </span>
      </div>
      <div>
        <h3 class="text-3xl font-bold text-gray-800 dark:text-white">
          {{ loading ? '...' : formatNumber(stats.pending_pdv || 0) }}
        </h3>
        <p class="text-gray-500 dark:text-gray-400 mt-1">En attente</p>
      </div>
    </div>

    <!-- Rejected PDV -->
    <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm p-6">
      <div class="flex items-center justify-between mb-4">
        <div class="w-12 h-12 bg-red-100 dark:bg-red-900 rounded-xl flex items-center justify-center">
          <svg class="w-6 h-6 text-red-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z" />
          </svg>
        </div>
      </div>
      <div>
        <h3 class="text-3xl font-bold text-gray-800 dark:text-white">
          {{ loading ? '...' : formatNumber(stats.rejected_pdv || 0) }}
        </h3>
        <p class="text-gray-500 dark:text-gray-400 mt-1">Rejetés</p>
      </div>
    </div>
  </div>
</template>

<script setup>
const props = defineProps({
  stats: {
    type: Object,
    default: () => ({
      total_pdv: 0,
      validated_pdv: 0,
      pending_pdv: 0,
      rejected_pdv: 0
    })
  },
  trend: {
    type: Object,
    default: () => ({
      total: null
    })
  },
  loading: {
    type: Boolean,
    default: false
  }
})

const formatNumber = (num) => {
  if (num >= 1000000) {
    return (num / 1000000).toFixed(1) + 'M'
  }
  if (num >= 1000) {
    return (num / 1000).toFixed(1) + 'k'
  }
  return num.toLocaleString()
}
</script>

<style scoped>
.stats-cards {
  width: 100%;
}
</style>
