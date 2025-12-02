<template>
  <div class="statistics-view min-h-screen bg-gray-50 dark:bg-gray-900 py-6">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
      <!-- Header -->
      <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm p-6 mb-6">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between space-y-4 sm:space-y-0">
          <div>
            <h1 class="text-xl sm:text-2xl font-bold text-gray-800 dark:text-white">
              Statistiques PDV
            </h1>
            <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">
              Vue d'ensemble des Points de Vente
            </p>
          </div>
          
          <div class="flex space-x-3">
            <button
              @click="exportCsv"
              :disabled="exporting"
              class="flex items-center px-4 py-2 rounded-xl bg-green-500 text-white hover:bg-green-600 transition-colors"
            >
              <svg class="w-5 h-5 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
              </svg>
              Export CSV
            </button>
            <button
              @click="exportXml"
              :disabled="exporting"
              class="flex items-center px-4 py-2 rounded-xl bg-orange-500 text-white hover:bg-orange-600 transition-colors"
            >
              <svg class="w-5 h-5 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 20l4-16m4 4l4 4-4 4M6 16l-4-4 4-4" />
              </svg>
              Export XML
            </button>
            <button
              @click="loadStatistics"
              :disabled="loading"
              class="flex items-center px-4 py-2 rounded-xl bg-gray-100 dark:bg-gray-700 text-gray-700 dark:text-gray-300 hover:bg-gray-200 dark:hover:bg-gray-600 transition-colors"
            >
              <svg class="w-5 h-5" :class="{ 'animate-spin': loading }" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
              </svg>
            </button>
          </div>
        </div>
      </div>

      <!-- Stats Cards -->
      <div class="mb-6">
        <StatsCards :stats="globalStats" :loading="loading" />
      </div>

      <!-- Charts Grid -->
      <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-6">
        <!-- By Region -->
        <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm p-6">
          <h3 class="text-lg font-semibold text-gray-800 dark:text-white mb-4">
            PDV par Région
          </h3>
          <div v-if="loading" class="h-64 flex items-center justify-center">
            <div class="animate-spin w-8 h-8 border-4 border-orange-500 border-t-transparent rounded-full"></div>
          </div>
          <div v-else class="space-y-4">
            <div v-for="region in byRegion" :key="region.region" class="flex items-center">
              <div class="w-24 text-sm font-medium text-gray-700 dark:text-gray-300">
                {{ region.region }}
              </div>
              <div class="flex-1 mx-4">
                <div class="h-4 bg-gray-200 dark:bg-gray-700 rounded-full overflow-hidden">
                  <div
                    class="h-full bg-gradient-to-r from-orange-400 to-orange-600 rounded-full"
                    :style="{ width: `${(region.total / maxRegionTotal) * 100}%` }"
                  ></div>
                </div>
              </div>
              <div class="w-16 text-right text-sm font-semibold text-gray-800 dark:text-white">
                {{ region.total }}
              </div>
            </div>
          </div>
        </div>

        <!-- By Organization -->
        <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm p-6">
          <h3 class="text-lg font-semibold text-gray-800 dark:text-white mb-4">
            Top Dealers
          </h3>
          <div v-if="loading" class="h-64 flex items-center justify-center">
            <div class="animate-spin w-8 h-8 border-4 border-orange-500 border-t-transparent rounded-full"></div>
          </div>
          <div v-else class="space-y-4">
            <div v-for="(org, index) in topOrganizations" :key="org.id" class="flex items-center">
              <div class="w-6 h-6 rounded-full bg-orange-100 dark:bg-orange-900 flex items-center justify-center text-xs font-bold text-orange-600 mr-3">
                {{ index + 1 }}
              </div>
              <div class="flex-1 min-w-0">
                <p class="text-sm font-medium text-gray-800 dark:text-white truncate">
                  {{ org.name }}
                </p>
                <p class="text-xs text-gray-500 dark:text-gray-400">
                  {{ org.validated_pdv || 0 }} validés / {{ org.total_pdv || 0 }} total
                </p>
              </div>
              <div class="ml-4">
                <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-green-100 dark:bg-green-900 text-green-800 dark:text-green-200">
                  {{ org.total_pdv || 0 }}
                </span>
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- Monthly Evolution -->
      <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm p-6 mb-6">
        <h3 class="text-lg font-semibold text-gray-800 dark:text-white mb-4">
          Évolution Mensuelle
        </h3>
        <div v-if="loading" class="h-64 flex items-center justify-center">
          <div class="animate-spin w-8 h-8 border-4 border-orange-500 border-t-transparent rounded-full"></div>
        </div>
        <div v-else class="flex items-end space-x-2 h-48">
          <div
            v-for="month in monthlyEvolution"
            :key="`${month.year}-${month.month}`"
            class="flex-1 flex flex-col items-center"
          >
            <div class="w-full bg-gray-100 dark:bg-gray-700 rounded-t-lg relative" style="height: 180px;">
              <div
                class="absolute bottom-0 left-0 right-0 bg-gradient-to-t from-orange-500 to-orange-400 rounded-t-lg transition-all duration-300"
                :style="{ height: `${(month.total / maxMonthlyTotal) * 100}%` }"
              ></div>
            </div>
            <p class="mt-2 text-xs text-gray-500 dark:text-gray-400">
              {{ month.month }}/{{ month.year.toString().slice(-2) }}
            </p>
          </div>
        </div>
      </div>

      <!-- Trends -->
      <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm p-6">
        <h3 class="text-lg font-semibold text-gray-800 dark:text-white mb-4">
          Activité des 30 derniers jours
        </h3>
        <div v-if="loading" class="h-32 flex items-center justify-center">
          <div class="animate-spin w-8 h-8 border-4 border-orange-500 border-t-transparent rounded-full"></div>
        </div>
        <div v-else class="flex items-end space-x-1 h-24">
          <div
            v-for="day in trends"
            :key="day.date"
            class="flex-1 bg-orange-500 rounded-t transition-all duration-200 hover:bg-orange-600"
            :style="{ height: `${Math.max((day.total / maxDailyTotal) * 100, 5)}%` }"
            :title="`${day.date}: ${day.total} PDV`"
          ></div>
        </div>
        <div class="flex justify-between mt-2 text-xs text-gray-500 dark:text-gray-400">
          <span>Il y a 30 jours</span>
          <span>Aujourd'hui</span>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue'
import StatsCards from '../components/StatsCards.vue'
import ExportService from '../services/ExportService'

// State
const loading = ref(false)
const exporting = ref(false)
const statistics = ref(null)

// Computed
const globalStats = computed(() => statistics.value?.global || {
  total_pdv: 0,
  validated_pdv: 0,
  pending_pdv: 0,
  rejected_pdv: 0
})

const byRegion = computed(() => statistics.value?.by_region || [])
const byOrganization = computed(() => statistics.value?.by_organization || [])
const trends = computed(() => statistics.value?.trends || [])
const monthlyEvolution = computed(() => statistics.value?.monthly_evolution || [])

const topOrganizations = computed(() => {
  return byOrganization.value.slice(0, 5)
})

const maxRegionTotal = computed(() => {
  return Math.max(...byRegion.value.map(r => r.total), 1)
})

const maxDailyTotal = computed(() => {
  return Math.max(...trends.value.map(d => d.total), 1)
})

const maxMonthlyTotal = computed(() => {
  return Math.max(...monthlyEvolution.value.map(m => m.total), 1)
})

// Methods
const loadStatistics = async () => {
  loading.value = true
  try {
    const response = await ExportService.getStatistics()
    statistics.value = response.data
  } catch (error) {
    console.error('Error loading statistics:', error)
  } finally {
    loading.value = false
  }
}

const exportCsv = async () => {
  exporting.value = true
  try {
    const blob = await ExportService.exportCsv()
    ExportService.downloadBlob(blob, `pdv_export_${new Date().toISOString().slice(0, 10)}.csv`)
  } catch (error) {
    console.error('Error exporting CSV:', error)
    alert('Erreur lors de l\'export CSV')
  } finally {
    exporting.value = false
  }
}

const exportXml = async () => {
  exporting.value = true
  try {
    const blob = await ExportService.exportXml()
    ExportService.downloadBlob(blob, `pdv_export_${new Date().toISOString().slice(0, 10)}.xml`)
  } catch (error) {
    console.error('Error exporting XML:', error)
    alert('Erreur lors de l\'export XML')
  } finally {
    exporting.value = false
  }
}

onMounted(() => {
  loadStatistics()
})
</script>

<style scoped>
.statistics-view {
  width: 100%;
}
</style>
