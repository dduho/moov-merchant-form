<template>
  <div class="bg-gray-100 min-h-screen p-6">
    <!-- Loader overlay -->
    <div v-if="loading" class="fixed inset-0 bg-white/80 backdrop-blur-sm z-50">
      <div class="flex items-center justify-center h-full">
        <div class="text-center">
          <div class="w-16 h-16 border-4 border-orange-500 border-t-transparent rounded-full animate-spin mx-auto"></div>
          <p class="mt-4 text-gray-600 font-medium">Chargement en cours...</p>
        </div>
      </div>
    </div>

    <!-- Content Container -->
    <div class="space-y-8">
      <!-- Header -->
      <div class="max-w-7xl mx-auto">
        <div class="bg-white rounded-2xl shadow-sm p-6 flex items-center justify-between">
          <div>
            <h1 class="text-2xl font-bold text-gray-800">Dashboard Administrateur</h1>
            <p class="text-gray-500 mt-1">Vue d'ensemble de l'activité</p>
          </div>
          <div class="flex items-center space-x-4">
            <button @click="refreshData" 
                    class="flex items-center px-4 py-2 bg-orange-50 text-orange-600 rounded-xl hover:bg-orange-100 transition-colors">
              <svg class="w-5 h-5 mr-2" :class="{ 'animate-spin': loading }" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
              </svg>
              Actualiser
            </button>
            <select v-model="selectedPeriod" 
                    @change="loadData" 
                    class="px-4 py-2 bg-white border border-gray-200 rounded-xl text-gray-700 focus:outline-none focus:ring-2 focus:ring-orange-500">
              <option value="today">Aujourd'hui</option>
              <option value="week">Cette semaine</option>
              <option value="month">Ce mois</option>
              <option value="quarter">Ce trimestre</option>
              <option value="year">Cette année</option>
              <option value="all">Tout</option>
            </select>
          </div>
        </div>
      </div>

      <!-- KPIs -->
      <div class="max-w-7xl mx-auto grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
        <template v-if="loading">
          <div v-for="i in 4" :key="'kpi-skeleton-'+i" class="bg-white rounded-2xl shadow-sm p-6">
            <div class="flex items-center justify-between mb-4">
              <div class="w-12 h-12 bg-gray-200 rounded-xl animate-pulse"></div>
              <div class="w-16 h-6 bg-gray-200 rounded-full animate-pulse"></div>
            </div>
            <div class="space-y-2">
              <div class="h-8 bg-gray-200 rounded w-24 animate-pulse"></div>
              <div class="h-4 bg-gray-200 rounded w-32 animate-pulse"></div>
            </div>
          </div>
        </template>
        
        <template v-else>
          <!-- Candidatures aujourd'hui -->
          <div class="bg-white rounded-2xl shadow-sm p-6">
            <div class="flex items-center justify-between mb-4">
              <div class="w-12 h-12 bg-blue-100 rounded-xl flex items-center justify-center">
                <svg class="w-6 h-6 text-blue-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                </svg>
              </div>
              <span :class="[
                'inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium',
                kpis.daily_activity?.trend === 'up' ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800'
              ]">
                <svg class="w-4 h-4 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                  <path v-if="kpis.daily_activity?.trend === 'up'" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6" />
                  <path v-else stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 17h8m0 0v-8m0 8l-8-8-4 4-6-6" />
                </svg>
                {{ Math.abs(kpis.daily_activity?.growth_percentage || 0) }}%
              </span>
            </div>
            <div>
              <h3 class="text-3xl font-bold text-gray-800">{{ kpis.daily_activity?.today || 0 }}</h3>
              <p class="text-gray-500 mt-1">Candidatures aujourd'hui</p>
            </div>
          </div>

          <!-- Actions urgentes -->
          <div class="bg-white rounded-2xl shadow-sm p-6">
            <div class="flex items-center justify-between mb-4">
              <div class="w-12 h-12 bg-orange-100 rounded-xl flex items-center justify-center">
                <svg class="w-6 h-6 text-orange-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
              </div>
              <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-orange-100 text-orange-800">
                {{ kpis.urgent_actions?.critical_pending_72h || 0 }} critiques
              </span>
            </div>
            <div>
              <h3 class="text-3xl font-bold text-gray-800">{{ kpis.urgent_actions?.total_urgent || 0 }}</h3>
              <p class="text-gray-500 mt-1">Actions urgentes</p>
            </div>
          </div>

          <!-- Taux de conversion -->
          <div class="bg-white rounded-2xl shadow-sm p-6">
            <div class="flex items-center justify-between mb-4">
              <div class="w-12 h-12 bg-green-100 rounded-xl flex items-center justify-center">
                <svg class="w-6 h-6 text-green-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
              </div>
              <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                {{ kpis.conversion?.approved_this_month || 0 }} ce mois
              </span>
            </div>
            <div>
              <h3 class="text-3xl font-bold text-gray-800">{{ kpis.conversion?.rate_percentage || 0 }}%</h3>
              <p class="text-gray-500 mt-1">Taux de conversion</p>
            </div>
          </div>

          <!-- Performance -->
          <div class="bg-white rounded-2xl shadow-sm p-6">
            <div class="flex items-center justify-between mb-4">
              <div class="w-12 h-12 bg-purple-100 rounded-xl flex items-center justify-center">
                <svg class="w-6 h-6 text-purple-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
              </div>
              <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-purple-100 text-purple-800">
                {{ kpis.processing_performance?.processed_today || 0 }} traités aujourd'hui
              </span>
            </div>
            <div>
              <h3 class="text-3xl font-bold text-gray-800">{{ kpis.processing_performance?.avg_response_time_hours || 0 }}h</h3>
              <p class="text-gray-500 mt-1">Temps de traitement moyen</p>
            </div>
          </div>
        </template>
      </div>

      <!-- Graphiques -->
      <div class="max-w-7xl mx-auto">
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
          <template v-if="loading">
            <div v-for="i in 3" :key="'chart-skeleton-'+i" class="bg-white rounded-2xl shadow-sm p-6">
              <div class="h-6 bg-gray-200 rounded w-48 mb-6 animate-pulse"></div>
              <div class="h-64 bg-gray-200 rounded animate-pulse"></div>
            </div>
          </template>
          
          <template v-else>
            <div class="bg-white rounded-2xl shadow-sm p-6">
              <h3 class="text-xl font-semibold text-gray-800 mb-4">Tendance des candidatures</h3>
              <canvas ref="lineChart"></canvas>
            </div>

            <div class="bg-white rounded-2xl shadow-sm p-6">
              <h3 class="text-xl font-semibold text-gray-800 mb-4">Répartition par statut</h3>
              <canvas ref="pieChart"></canvas>
            </div>

            <div class="bg-white rounded-2xl shadow-sm p-6">
              <h3 class="text-xl font-semibold text-gray-800 mb-4">Par type de commerce</h3>
              <canvas ref="barChart"></canvas>
            </div>
          </template>
        </div>
      </div>

      <!-- Candidatures récentes -->
      <div class="max-w-7xl mx-auto">
        <div class="bg-white rounded-2xl shadow-sm p-6">
          <div class="flex items-center justify-between mb-6">
            <div>
              <h3 class="text-xl font-semibold text-gray-800">Candidatures récentes</h3>
              <p class="text-sm text-gray-500 mt-1">Les 10 dernières candidatures reçues</p>
            </div>
            <div class="w-10 h-10 bg-blue-100 rounded-xl flex items-center justify-center">
              <svg class="w-6 h-6 text-blue-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z" />
              </svg>
            </div>
          </div>
          
          <div class="border rounded-xl overflow-hidden">
            <div class="min-w-full divide-y divide-gray-200">
              <template v-if="loading">
                <div v-for="i in 5" :key="'app-skeleton-'+i"
                     class="flex items-center justify-between px-6 py-4">
                  <div class="flex items-center space-x-4">
                    <div class="w-12 h-12 bg-gray-200 rounded-full animate-pulse"></div>
                    <div class="space-y-2">
                      <div class="h-4 bg-gray-200 rounded w-32 animate-pulse"></div>
                      <div class="h-3 bg-gray-200 rounded w-48 animate-pulse"></div>
                    </div>
                  </div>
                  <div class="flex items-center space-x-4">
                    <div class="h-6 bg-gray-200 rounded-full w-20 animate-pulse"></div>
                    <div class="h-8 bg-gray-200 rounded-lg w-24 animate-pulse"></div>
                  </div>
                </div>
              </template>

              <template v-else>
                <div v-for="app in recentApplications" :key="app.id" 
                     class="flex items-center justify-between px-6 py-4 hover:bg-gray-50 transition-colors">
                  <div class="flex items-center space-x-4">
                    <div class="flex-shrink-0">
                      <div class="w-12 h-12 bg-gray-100 rounded-full flex items-center justify-center">
                        <span class="text-lg font-medium text-gray-600">
                          {{ app.full_name?.[0]?.toUpperCase() || '?' }}
                        </span>
                      </div>
                    </div>
                    <div>
                      <h4 class="text-sm font-medium text-gray-900">{{ app.full_name }}</h4>
                      <div class="flex items-center space-x-2 mt-1">
                        <span class="text-sm text-gray-500">{{ app.business_name }}</span>
                        <span class="text-gray-300">•</span>
                        <span class="text-xs text-gray-400">{{ app.reference_number }}</span>
                      </div>
                    </div>
                  </div>
                  
                  <div class="flex items-center space-x-4">
                    <div class="flex flex-col items-end">
                      <span :class="[
                        'px-2 py-1 text-xs font-medium rounded-full',
                        app.status === 'pending' && 'bg-yellow-100 text-yellow-800',
                        app.status === 'approved' && 'bg-green-100 text-green-800',
                        app.status === 'rejected' && 'bg-red-100 text-red-800',
                        app.status === 'under_review' && 'bg-blue-100 text-blue-800'
                      ]">
                        {{ app.status_label }}
                      </span>
                      <span class="text-xs text-gray-400 mt-1">{{ formatDate(app.submitted_at) }}</span>
                    </div>
                    
                    <router-link :to="`/applications/${app.id}`"
                               class="inline-flex items-center px-3 py-1.5 border border-orange-600 text-sm font-medium rounded-lg text-orange-600 bg-white hover:bg-orange-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-orange-500 transition-colors">
                      <svg class="w-4 h-4 mr-1.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                      </svg>
                      Détails
                    </router-link>
                  </div>
                </div>
              </template>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
import { ref, onMounted } from 'vue'
import ApiService from '../services/ApiService'
import Chart from 'chart.js/auto'

export default {
  name: 'Dashboard',
  setup() {
    const loading = ref(true)
    const selectedPeriod = ref('month')
    const stats = ref({})
    const kpis = ref({})
    const alerts = ref([])
    const recentApplications = ref([])
    const charts = ref({})
    
    const lineChart = ref(null)
    const pieChart = ref(null)
    const barChart = ref(null)
    
    let lineChartInstance = null
    let pieChartInstance = null
    let barChartInstance = null
    
    const loadData = async () => {
      loading.value = true
      try {
        // Charger en parallèle
        const [statsRes, kpisRes, alertsRes, recentRes, chartsRes] = await Promise.all([
          ApiService.getDashboardStats(selectedPeriod.value),
          ApiService.getDashboardKpis(),
          ApiService.getDashboardAlerts(),
          ApiService.getDashboardRecent(10),
          ApiService.getDashboardCharts(selectedPeriod.value)
        ])
        
        stats.value = statsRes.data.data
        kpis.value = kpisRes.data.data
        alerts.value = alertsRes.data.data
        recentApplications.value = recentRes.data.data
        charts.value = chartsRes.data.data
        
        // Créer les graphiques
        createCharts()
      } catch (error) {
        console.error('Erreur chargement dashboard:', error)
      } finally {
        loading.value = false
      }
    }
    
    const createCharts = () => {
      // Détruire les graphiques existants
      if (lineChartInstance) lineChartInstance.destroy()
      if (pieChartInstance) pieChartInstance.destroy()
      if (barChartInstance) barChartInstance.destroy()
      
      // Attendre le prochain tick pour que les refs soient montées
      setTimeout(() => {
        // Graphique en ligne
        if (lineChart.value && charts.value.line_chart) {
          lineChartInstance = new Chart(lineChart.value.getContext('2d'), {
            type: 'line',
            data: charts.value.line_chart,
            options: { 
              responsive: true, 
              maintainAspectRatio: false,
              plugins: {
                legend: { position: 'bottom' }
              }
            }
          })
        }
        
        // Graphique circulaire
        if (pieChart.value && charts.value.pie_chart) {
          pieChartInstance = new Chart(pieChart.value.getContext('2d'), {
            type: 'pie',
            data: charts.value.pie_chart,
            options: { 
              responsive: true, 
              maintainAspectRatio: false,
              plugins: {
                legend: { position: 'right' }
              }
            }
          })
        }
        
        // Graphique en barres
        if (barChart.value && charts.value.bar_chart) {
          barChartInstance = new Chart(barChart.value.getContext('2d'), {
            type: 'bar',
            data: charts.value.bar_chart,
            options: { 
              responsive: true, 
              maintainAspectRatio: false,
              plugins: {
                legend: { position: 'bottom' }
              }
            }
          })
        }
      }, 0)
    }
    
    const refreshData = () => {
      loadData()
    }
    
    const formatDate = (dateString) => {
      if (!dateString) return '';
      const date = new Date(dateString);
      const now = new Date();
      const diff = now - date;
      const days = Math.floor(diff / (1000 * 60 * 60 * 24));
      const hours = Math.floor(diff / (1000 * 60 * 60));
      const minutes = Math.floor(diff / (1000 * 60));

      if (minutes < 60) {
        return `il y a ${minutes} minute${minutes > 1 ? 's' : ''}`;
      } else if (hours < 24) {
        return `il y a ${hours} heure${hours > 1 ? 's' : ''}`;
      } else if (days < 7) {
        return `il y a ${days} jour${days > 1 ? 's' : ''}`;
      } else {
        return date.toLocaleDateString('fr-FR', {
          day: 'numeric',
          month: 'short',
          year: 'numeric'
        });
      }
    }
    
    onMounted(() => {
      loadData()
    })
    
    return {
      loading,
      selectedPeriod,
      stats,
      kpis,
      alerts,
      recentApplications,
      lineChart,
      pieChart,
      barChart,
      loadData,
      refreshData,
      formatDate
    }
  }
}
</script>

<style scoped>
.charts-section {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
  gap: 24px;
  margin-bottom: 30px;
}

.chart-card {
  background: white;
  border-radius: 16px;
  padding: 24px;
  box-shadow: 0 1px 3px rgba(0,0,0,0.1);
  position: relative;
  min-height: 300px;
}

.chart-card h3 {
  font-size: 1.1rem;
  font-weight: 600;
  margin-bottom: 1rem;
  color: #374151;
}

.chart-card canvas {
  width: 100% !important;
  height: 250px !important;
}
</style>