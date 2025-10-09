<template>
  <div class="bg-gray-100 min-h-screen p-6">
    <!-- Content Container -->
    <div class="space-y-8">
      <!-- Header -->
      <div class="max-w-7xl mx-auto">
        <div class="bg-white rounded-2xl shadow-sm p-6 flex items-center justify-between">
          <div>
            <h1 class="text-2xl font-bold text-gray-800">Tableau de bord</h1>
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
          <!-- Candidatures -->
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
              <h3 class="text-3xl font-bold text-gray-800">
                {{ selectedPeriod === 'today' ? (kpis.daily_activity?.today || 0) :
                   selectedPeriod === 'week' ? (kpis.daily_activity?.this_week || 0) :
                   selectedPeriod === 'month' ? (kpis.daily_activity?.this_month || 0) :
                   selectedPeriod === 'quarter' ? (kpis.daily_activity?.this_quarter || 0) :
                   selectedPeriod === 'year' ? (kpis.daily_activity?.this_year || 0) :
                   (kpis.daily_activity?.total || 0) }}
              </h3>
              <p class="text-gray-500 mt-1">Candidatures {{ getPeriodText() }}</p>
            </div>
          </div>

          <!-- Candidatures Validées -->
          <div class="bg-white rounded-2xl shadow-sm p-6">
            <div class="flex items-center justify-between mb-4">
              <div class="w-12 h-12 bg-green-100 rounded-xl flex items-center justify-center">
                <svg class="w-6 h-6 text-green-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
              </div>
              <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                {{ selectedPeriod === 'today' ? (kpis.validated_applications?.approved_today || 0) :
                   selectedPeriod === 'week' ? (kpis.validated_applications?.approved_this_week || 0) :
                   selectedPeriod === 'month' ? (kpis.validated_applications?.approved_this_month || 0) :
                   selectedPeriod === 'quarter' ? (kpis.validated_applications?.approved_this_quarter || 0) :
                   selectedPeriod === 'year' ? (kpis.validated_applications?.approved_this_year || 0) :
                   (kpis.validated_applications?.total_approved || 0) }} {{ getPeriodText() }}
              </span>
            </div>
            <div>
              <h3 class="text-3xl font-bold text-gray-800">
                {{ selectedPeriod === 'today' ? (kpis.validated_applications?.approved_today || 0) :
                   selectedPeriod === 'week' ? (kpis.validated_applications?.approved_this_week || 0) :
                   selectedPeriod === 'month' ? (kpis.validated_applications?.approved_this_month || 0) :
                   selectedPeriod === 'quarter' ? (kpis.validated_applications?.approved_this_quarter || 0) :
                   selectedPeriod === 'year' ? (kpis.validated_applications?.approved_this_year || 0) :
                   (kpis.validated_applications?.total_approved || 0) }}
              </h3>
              <p class="text-gray-500 mt-1">Candidatures Validées {{ getPeriodText() }}</p>
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
                {{ selectedPeriod === 'today' ? (kpis.conversion?.approved_today || 0) :
                   selectedPeriod === 'week' ? (kpis.conversion?.approved_this_week || 0) :
                   selectedPeriod === 'month' ? (kpis.conversion?.approved_this_month || 0) :
                   selectedPeriod === 'quarter' ? (kpis.conversion?.approved_this_quarter || 0) :
                   selectedPeriod === 'year' ? (kpis.conversion?.approved_this_year || 0) :
                   (kpis.conversion?.total_approved || 0) }} {{ getPeriodText() }}
              </span>
            </div>
            <div>
              <h3 class="text-3xl font-bold text-gray-800">
                {{ selectedPeriod === 'today' ? (kpis.conversion?.rate_today || 0) :
                   selectedPeriod === 'week' ? (kpis.conversion?.rate_this_week || 0) :
                   selectedPeriod === 'month' ? (kpis.conversion?.rate_this_month || 0) :
                   selectedPeriod === 'quarter' ? (kpis.conversion?.rate_this_quarter || 0) :
                   selectedPeriod === 'year' ? (kpis.conversion?.rate_this_year || 0) :
                   (kpis.conversion?.rate_percentage || 0) }}%
              </h3>
              <p class="text-gray-500 mt-1">Taux de conversion {{ getPeriodText() }}</p>
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
                {{ selectedPeriod === 'today' ? (kpis.processing_performance?.processed_today || 0) :
                   selectedPeriod === 'week' ? (kpis.processing_performance?.processed_this_week || 0) :
                   selectedPeriod === 'month' ? (kpis.processing_performance?.processed_this_month || 0) :
                   selectedPeriod === 'quarter' ? (kpis.processing_performance?.processed_this_quarter || 0) :
                   selectedPeriod === 'year' ? (kpis.processing_performance?.processed_this_year || 0) :
                   (kpis.processing_performance?.processed_total || 0) }} traités {{ getPeriodText() }}
              </span>
            </div>
            <div>
              <h3 class="text-3xl font-bold text-gray-800">
                {{ selectedPeriod === 'today' ? (kpis.processing_performance?.avg_time_today || 0) :
                   selectedPeriod === 'week' ? (kpis.processing_performance?.avg_time_this_week || 0) :
                   selectedPeriod === 'month' ? (kpis.processing_performance?.avg_time_this_month || 0) :
                   selectedPeriod === 'quarter' ? (kpis.processing_performance?.avg_time_this_quarter || 0) :
                   selectedPeriod === 'year' ? (kpis.processing_performance?.avg_time_this_year || 0) :
                   (kpis.processing_performance?.avg_response_time_hours || 0) }}h
              </h3>
              <p class="text-gray-500 mt-1">Temps de traitement moyen {{ getPeriodText() }}</p>
            </div>
          </div>
        </template>
      </div>

      <!-- Graphiques -->
      <div class="max-w-7xl mx-auto">
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
          <template v-if="loading">
            <div v-for="i in 3" :key="'chart-skeleton-'+i" class="chart-card">
              <h3 class="text-xl font-semibold text-gray-800">Chargement...</h3>
              <div class="flex-1 bg-gray-200 rounded animate-pulse"></div>
            </div>
          </template>
          
          <template v-else>
            <div class="chart-card">
              <h3 class="text-xl font-semibold text-gray-800">Tendance des candidatures</h3>
              <div class="relative flex-1">
                <canvas ref="lineChart"></canvas>
              </div>
            </div>

            <div class="chart-card">
              <h3 class="text-xl font-semibold text-gray-800">Répartition par statut</h3>
              <div class="relative flex-1">
                <canvas ref="pieChart"></canvas>
              </div>
            </div>

            <div class="chart-card">
              <h3 class="text-xl font-semibold text-gray-800">Par type de commerce</h3>
              <div class="relative flex-1">
                <canvas ref="barChart"></canvas>
              </div>
            </div>
          </template>
        </div>
      </div>

      <!-- Statistiques par utilisateur (Admin uniquement) -->
      <div v-if="authStore.isAdmin" class="max-w-7xl mx-auto">
        <div class="bg-white rounded-2xl shadow-sm p-6">
          <div class="flex items-center justify-between mb-6">
            <div>
              <h3 class="text-xl font-semibold text-gray-800">Performance des utilisateurs</h3>
              <p class="text-sm text-gray-500 mt-1">Candidatures soumises, validées et taux de conversion par utilisateur {{ getPeriodText() }}</p>
            </div>
            <div class="w-10 h-10 bg-purple-100 rounded-xl flex items-center justify-center">
              <svg class="w-6 h-6 text-purple-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />
              </svg>
            </div>
          </div>

          <!-- Contrôles de recherche et pagination -->
          <div class="flex flex-col sm:flex-row gap-4 mb-6 p-4 bg-gray-50 rounded-xl">
            <!-- Champ de recherche -->
            <div class="flex-1">
              <div class="relative">
                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                  <svg class="h-5 w-5 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                  </svg>
                </div>
                <input
                  v-model="userSearchQuery"
                  @input="debouncedUserSearch"
                  type="text"
                  class="block w-full pl-10 pr-3 py-2 border border-gray-300 rounded-md leading-5 bg-white placeholder-gray-500 focus:outline-none focus:placeholder-gray-400 focus:ring-1 focus:ring-blue-500 focus:border-blue-500 sm:text-sm"
                  placeholder="Rechercher par nom, prénom, username, email ou téléphone..."
                />
                <div v-if="userSearchQuery" class="absolute inset-y-0 right-0 pr-3 flex items-center">
                  <button
                    @click="clearUserSearch"
                    class="text-gray-400 hover:text-gray-600"
                  >
                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                  </button>
                </div>
              </div>
            </div>

            <!-- Sélecteur de nombre par page -->
            <div class="flex items-center space-x-2">
              <label class="text-sm text-gray-600 whitespace-nowrap">Lignes par page:</label>
              <select
                v-model="userPerPage"
                @change="loadUserStats"
                class="block w-20 px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm"
              >
                <option :value="5">5</option>
                <option :value="10">10</option>
                <option :value="25">25</option>
                <option :value="50">50</option>
              </select>
            </div>
          </div>
          
          <template v-if="loading">
            <div class="space-y-4">
              <div v-for="i in 3" :key="'user-skeleton-'+i" class="bg-gray-100 rounded-xl p-4 animate-pulse">
                <div class="flex items-center justify-between">
                  <div class="flex items-center space-x-3">
                    <div class="w-10 h-10 bg-gray-200 rounded-full"></div>
                    <div class="space-y-2">
                      <div class="h-4 bg-gray-200 rounded w-24"></div>
                      <div class="h-3 bg-gray-200 rounded w-16"></div>
                    </div>
                  </div>
                  <div class="flex space-x-8">
                    <div class="text-center">
                      <div class="h-6 bg-gray-200 rounded w-8 mb-1"></div>
                      <div class="h-3 bg-gray-200 rounded w-12"></div>
                    </div>
                    <div class="text-center">
                      <div class="h-6 bg-gray-200 rounded w-8 mb-1"></div>
                      <div class="h-3 bg-gray-200 rounded w-12"></div>
                    </div>
                    <div class="text-center">
                      <div class="h-6 bg-gray-200 rounded w-8 mb-1"></div>
                      <div class="h-3 bg-gray-200 rounded w-12"></div>
                    </div>
                    <div class="text-center">
                      <div class="h-6 bg-gray-200 rounded w-12 mb-1"></div>
                      <div class="h-3 bg-gray-200 rounded w-16"></div>
                    </div>
                  </div>
                </div>
              </div>
            </div>
          </template>
          
          <template v-else-if="userStats.length === 0">
            <div class="text-center py-8">
              <div class="w-16 h-16 bg-gray-100 rounded-full flex items-center justify-center mx-auto mb-4">
                <svg class="w-8 h-8 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.172 16.172a4 4 0 015.656 0M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                </svg>
              </div>
              <p class="text-gray-500">
                {{ userSearchQuery ? 'Aucun utilisateur trouvé pour cette recherche' : 'Aucune activité utilisateur ' + getPeriodText() }}
              </p>
            </div>
          </template>
          
          <template v-else>
            <div class="space-y-3">
              <div v-for="user in userStats" :key="user.user_id" 
                   class="bg-gray-50 rounded-xl p-4 hover:bg-gray-100 transition-colors">
                <div class="flex items-center justify-between">
                  <!-- Informations utilisateur -->
                  <div class="flex items-center space-x-3">
                    <div class="w-10 h-10 bg-gradient-to-br from-blue-500 to-purple-600 rounded-full flex items-center justify-center text-white font-semibold">
                      {{ user.full_name.split(' ').map(n => n[0]).join('').substring(0, 2).toUpperCase() }}
                    </div>
                    <div>
                      <h4 class="font-semibold text-gray-800">{{ user.full_name }}</h4>
                      <div class="flex items-center space-x-2 text-sm text-gray-500">
                        <span>{{ user.username }}</span>
                        <span v-if="user.email" class="text-gray-400">•</span>
                        <span v-if="user.email" class="truncate max-w-40">{{ user.email }}</span>
                        <span v-if="user.roles.length > 0" class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-blue-100 text-blue-800">
                          {{ user.roles.join(', ') }}
                        </span>
                      </div>
                    </div>
                  </div>
                  
                  <!-- Statistiques -->
                  <div class="flex items-center space-x-8">
                    <!-- Total soumis -->
                    <div class="text-center">
                      <div class="text-lg font-bold text-gray-800">{{ user.stats.total_submitted }}</div>
                      <div class="text-xs text-gray-500">Soumises</div>
                    </div>
                    
                    <!-- Validées -->
                    <div class="text-center">
                      <div class="text-lg font-bold text-green-600">{{ user.stats.total_approved }}</div>
                      <div class="text-xs text-gray-500">Validées</div>
                    </div>
                    
                    <!-- Rejetées -->
                    <div class="text-center">
                      <div class="text-lg font-bold text-red-600">{{ user.stats.total_rejected }}</div>
                      <div class="text-xs text-gray-500">Rejetées</div>
                    </div>
                    
                    <!-- En attente -->
                    <div class="text-center">
                      <div class="text-lg font-bold text-yellow-600">{{ user.stats.total_pending }}</div>
                      <div class="text-xs text-gray-500">En attente</div>
                    </div>
                    
                    <!-- Taux de conversion -->
                    <div class="text-center">
                      <div class="text-lg font-bold" :class="user.stats.conversion_rate >= 70 ? 'text-green-600' : user.stats.conversion_rate >= 40 ? 'text-yellow-600' : 'text-red-600'">
                        {{ user.stats.conversion_rate }}%
                      </div>
                      <div class="text-xs text-gray-500">Conversion</div>
                    </div>
                  </div>
                </div>
              </div>
            </div>

            <!-- Pagination -->
            <div v-if="userPagination && userPagination.total_pages > 1" class="flex items-center justify-between mt-6 pt-4 border-t border-gray-200">
              <div class="text-sm text-gray-500">
                Affichage {{ ((userPagination.current_page - 1) * userPagination.per_page) + 1 }} à 
                {{ Math.min(userPagination.current_page * userPagination.per_page, userPagination.total) }} 
                de {{ userPagination.total }} utilisateurs
              </div>
              
              <div class="flex items-center space-x-2">
                <!-- Bouton précédent -->
                <button
                  @click="loadUserStatsPage(userPagination.current_page - 1)"
                  :disabled="!userPagination.has_prev"
                  class="inline-flex items-center px-3 py-1 border border-gray-300 rounded-md text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 disabled:opacity-50 disabled:cursor-not-allowed"
                >
                  <svg class="h-4 w-4 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
                  </svg>
                  Précédent
                </button>
                
                <!-- Numéros de page -->
                <div class="flex items-center space-x-1">
                  <button
                    v-for="page in getPaginationPages(userPagination.current_page, userPagination.total_pages)"
                    :key="page"
                    @click="page !== '...' && loadUserStatsPage(page)"
                    :class="[
                      'px-3 py-1 text-sm font-medium rounded-md',
                      page === userPagination.current_page
                        ? 'bg-blue-600 text-white'
                        : page === '...'
                        ? 'text-gray-400 cursor-default'
                        : 'text-gray-700 bg-white border border-gray-300 hover:bg-gray-50'
                    ]"
                    :disabled="page === '...'"
                  >
                    {{ page }}
                  </button>
                </div>
                
                <!-- Bouton suivant -->
                <button
                  @click="loadUserStatsPage(userPagination.current_page + 1)"
                  :disabled="!userPagination.has_next"
                  class="inline-flex items-center px-3 py-1 border border-gray-300 rounded-md text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 disabled:opacity-50 disabled:cursor-not-allowed"
                >
                  Suivant
                  <svg class="h-4 w-4 ml-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                  </svg>
                </button>
              </div>
            </div>
          </template>
        </div>
      </div>

      <!-- Dernières candidatures -->
      <div class="max-w-7xl mx-auto">
        <div class="bg-white rounded-2xl shadow-sm p-6">
          <!-- En-tête avec titre et icône -->
          <div class="flex items-center justify-between mb-6">
            <div>
              <h3 class="text-xl font-semibold text-gray-800">Dernières candidatures</h3>
              <p class="text-sm text-gray-500 mt-1">Toutes les candidatures avec filtres et recherche</p>
            </div>
            <div class="flex items-center space-x-3">
              <button @click="exportToSP" 
                      :disabled="exportLoading"
                      class="flex items-center px-4 py-2 bg-green-600 text-white rounded-xl hover:bg-green-700 transition-colors disabled:opacity-50 disabled:cursor-not-allowed">
                <svg v-if="!exportLoading" class="w-5 h-5 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                </svg>
                <svg v-else class="w-5 h-5 mr-2 animate-spin" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
                </svg>
                {{ exportLoading ? 'Export...' : 'Exporter vers SP' }}
              </button>
              <div class="w-10 h-10 bg-blue-100 rounded-xl flex items-center justify-center">
                <svg class="w-6 h-6 text-blue-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z" />
                </svg>
              </div>
            </div>
          </div>

          <!-- Filtres et recherche -->
          <div class="flex flex-col sm:flex-row gap-4 mb-6 p-4 bg-gray-50 rounded-xl">
            <!-- Champ de recherche -->
            <div class="flex-1">
              <label for="search" class="sr-only">Rechercher</label>
              <div class="relative">
                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                  <svg class="h-5 w-5 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                  </svg>
                </div>
                <input
                  id="search"
                  v-model="searchQuery"
                  type="text"
                  placeholder="Rechercher par nom, entreprise, téléphone, email, référence..."
                  class="block w-full pl-10 pr-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-orange-500 focus:border-orange-500 text-sm"
                  @input="debouncedSearch"
                />
              </div>
            </div>

            <!-- Filtre par statut -->
            <div class="sm:w-48">
              <label for="status" class="sr-only">Filtrer par statut</label>
              <select
                id="status"
                v-model="selectedStatus"
                @change="applyFilters"
                class="block w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-orange-500 focus:border-orange-500 text-sm"
              >
                <option value="all">Tous les statuts</option>
                <option value="pending">En attente</option>
                <option value="approved">Approuvée</option>
                <option value="rejected">Rejetée</option>
              </select>
            </div>

            <!-- Bouton de réinitialisation -->
            <button
              @click="resetFilters"
              class="inline-flex items-center px-4 py-2 border border-gray-300 rounded-lg text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-orange-500 transition-colors"
            >
              <svg class="w-4 h-4 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
              </svg>
              Réinitialiser
            </button>
          </div>
          
          <div class="border rounded-xl overflow-hidden">
            <div class="min-w-full divide-y divide-gray-200">
              <template v-if="applicationsLoading">
                <div v-for="i in 6" :key="'app-skeleton-'+i"
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

              <template v-else-if="recentApplications.length > 0">
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
                        'px-2.5 py-1 text-xs font-medium rounded-full whitespace-nowrap',
                        app.status === 'pending' && 'bg-gradient-to-r from-yellow-50 to-amber-50 text-yellow-800 border border-yellow-200',
                        app.status === 'approved' && 'bg-gradient-to-r from-green-50 to-emerald-50 text-green-800 border border-green-200',
                        app.status === 'rejected' && 'bg-gradient-to-r from-red-50 to-rose-50 text-red-800 border border-red-200'
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

              <!-- Message si aucune candidature -->
              <template v-else>
                <div class="px-6 py-12 text-center">
                  <svg class="w-12 h-12 text-gray-400 mx-auto mb-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                  </svg>
                  <h3 class="text-sm font-medium text-gray-900 mb-1">Aucune candidature trouvée</h3>
                  <p class="text-sm text-gray-500">Aucune candidature ne correspond aux critères de recherche.</p>
                </div>
              </template>
            </div>
          </div>

          <!-- Pagination -->
          <PaginationControls
            v-if="!applicationsLoading && pagination.total > 0"
            :current-page="pagination.current_page"
            :total-pages="pagination.total_pages"
            :total="pagination.total"
            :per-page="pagination.per_page"
            :has-next="pagination.has_next"
            :has-prev="pagination.has_prev"
            @page-change="handlePageChange"
          />
        </div>
      </div>
    </div>
  </div>
</template>

<script>
import { ref, onMounted, watch } from 'vue'
import ApiService from '../services/ApiService'
import MerchantService from '../services/MerchantService'
import Chart from 'chart.js/auto'
import PaginationControls from '../components/PaginationControls.vue'
import { useAuthStore } from '../stores/auth'

export default {
  name: 'Dashboard',
  components: {
    PaginationControls
  },
  setup() {
    const authStore = useAuthStore()
    const loading = ref(true)
    const applicationsLoading = ref(false)
    const exportLoading = ref(false)
    const selectedPeriod = ref('month')
    const stats = ref({})
    const kpis = ref({})
    const alerts = ref([])
    const recentApplications = ref([])
    const charts = ref({})
    const userStats = ref([])
    
    // Variables pour les filtres et pagination
    const searchQuery = ref('')
    const selectedStatus = ref('all')
    const pagination = ref({
      current_page: 1,
      per_page: 6,
      total: 0,
      total_pages: 0,
      has_next: false,
      has_prev: false
    })
    
    // Variables pour la pagination et recherche des statistiques utilisateur
    const userSearchQuery = ref('')
    const userPerPage = ref(10)
    const userCurrentPage = ref(1)
    const userPagination = ref({
      current_page: 1,
      per_page: 10,
      total: 0,
      total_pages: 0,
      has_next: false,
      has_prev: false
    })
    
    // Debounce pour la recherche
    let searchTimeout = null
    let userSearchTimeout = null
    
    const lineChart = ref(null)
    const pieChart = ref(null)
    const barChart = ref(null)
    
    let lineChartInstance = null
    let pieChartInstance = null
    let barChartInstance = null
    
    const loadData = async () => {
      loading.value = true
      try {
        // Préparer les promesses de base
        const promises = [
          ApiService.getDashboardStats(selectedPeriod.value),
          ApiService.getDashboardKpis(selectedPeriod.value),
          ApiService.getDashboardAlerts(selectedPeriod.value),
          ApiService.getDashboardCharts(selectedPeriod.value)
        ]
        
        // Charger en parallèle les données de base
        const [statsRes, kpisRes, alertsRes, chartsRes] = await Promise.all(promises)
        
        stats.value = statsRes.data.data
        kpis.value = kpisRes.data.data
        alerts.value = alertsRes.data.data
        charts.value = chartsRes.data.data
        
        // Charger les statistiques utilisateur séparément si l'utilisateur est admin
        if (authStore.isAdmin) {
          await loadUserStats()
        } else {
          console.log('Utilisateur non-admin, pas de chargement des stats utilisateur', {
            user: authStore.user?.username,
            isAdmin: authStore.isAdmin,
            roles: authStore.user?.roles
          })
        }
        
        // Créer les graphiques
        createCharts()
      } catch (error) {
        console.error('Erreur chargement dashboard:', error)
      } finally {
        loading.value = false
      }
    }
    
    const createCharts = () => {
      // Détruire les graphiques existants et réinitialiser les instances
      if (lineChartInstance) {
        lineChartInstance.destroy()
        lineChartInstance = null
      }
      if (pieChartInstance) {
        pieChartInstance.destroy()
        pieChartInstance = null
      }
      if (barChartInstance) {
        barChartInstance.destroy()
        barChartInstance = null
      }
      
      // Configuration commune des graphiques
      const commonOptions = {
        responsive: true,
        maintainAspectRatio: true,
        aspectRatio: 1.5,
        animation: {
          duration: 750,
          easing: 'easeOutQuart'
        },
        layout: {
          padding: {
            top: 10,
            bottom: 10
          }
        },
        plugins: {
          legend: {
            display: true,
            position: 'bottom',
            labels: {
              usePointStyle: true,
              padding: 15,
              boxWidth: 8,
              font: {
                size: 11
              }
            }
          }
        }
      }
      
      // Attendre le prochain tick pour que les refs soient montées
      setTimeout(() => {
        try {
          // Graphique en ligne
          if (lineChart.value && charts.value.line_chart) {
            const ctx = lineChart.value.getContext('2d')
            lineChartInstance = new Chart(ctx, {
              type: 'line',
              data: {
                labels: charts.value.line_chart.labels,
                datasets: charts.value.line_chart.datasets.map(dataset => ({
                  ...dataset,
                  borderColor: '#F97316',
                  backgroundColor: 'rgba(249, 115, 22, 0.1)',
                  borderWidth: 2,
                  fill: true,
                  tension: 0.4
                }))
              },
              options: {
                ...commonOptions,
                scales: {
                  y: {
                    beginAtZero: true,
                    grid: {
                      display: true,
                      drawBorder: false
                    }
                  },
                  x: {
                    grid: {
                      display: false
                    }
                  }
                }
              }
            })
          }
          
          // Graphique circulaire
          if (pieChart.value && charts.value.pie_chart) {
            const ctx = pieChart.value.getContext('2d')
            pieChartInstance = new Chart(ctx, {
              type: 'pie',
              data: {
                labels: charts.value.pie_chart.labels,
                datasets: charts.value.pie_chart.datasets.map(dataset => ({
                  ...dataset,
                  backgroundColor: [
                    '#FDE68A', // En attente
                    '#93C5FD', // En examen
                    '#86EFAC', // Approuvé
                    '#FCA5A5'  // Rejeté
                  ]
                }))
              },
              options: {
                ...commonOptions,
                plugins: {
                  ...commonOptions.plugins,
                  legend: {
                    ...commonOptions.plugins.legend,
                    position: 'right'
                  }
                }
              }
            })
          }
          
          // Graphique en barres
          if (barChart.value && charts.value.bar_chart) {
            const ctx = barChart.value.getContext('2d')
            barChartInstance = new Chart(ctx, {
              type: 'bar',
              data: {
                labels: charts.value.bar_chart.labels,
                datasets: charts.value.bar_chart.datasets.map(dataset => ({
                  ...dataset,
                  backgroundColor: '#F97316',
                  borderRadius: 8
                }))
              },
              options: {
                ...commonOptions,
                scales: {
                  y: {
                    beginAtZero: true,
                    grid: {
                      display: true,
                      drawBorder: false
                    }
                  },
                  x: {
                    grid: {
                      display: false
                    }
                  }
                }
              }
            })
          }
        } catch (error) {
          console.error('Erreur lors de la création des graphiques:', error)
        }
      }, 100) // Augmenter légèrement le délai pour s'assurer que le DOM est prêt
    }
    
    const refreshData = () => {
      loadData()
    }

    // Fonction pour exporter vers SP
    const exportToSP = async () => {
      try {
        exportLoading.value = true
        
        // Récupérer toutes les candidatures approuvées
        const approvedApplications = await MerchantService.getApprovedApplicationsForExport()
        
        if (!approvedApplications || approvedApplications.length === 0) {
          alert('Aucune candidature approuvée à exporter.')
          return
        }
        
        const currentDate = new Date().toISOString().slice(0, 10).replace(/-/g, '')
        
        // Générer les fichiers XML
        const createOrgXml = generateCreateOrgXml(approvedApplications)
        const createOperatorXml = generateCreateOperatorXml(approvedApplications)
        
        // Télécharger les fichiers
        downloadFile(`CREATE-ORG-${currentDate}.xml`, createOrgXml)
        downloadFile(`CREATE-ORG-OPERATOR-${currentDate}.xml`, createOperatorXml)
        
        alert(`Export réussi ! ${approvedApplications.length} candidature(s) exportée(s).`)
        
      } catch (error) {
        console.error('Erreur lors de l\'export:', error)
        alert('Erreur lors de l\'export. Veuillez réessayer.')
      } finally {
        exportLoading.value = false
      }
    }

    // Fonction pour mapper les types de cartes ID
    const mapIdType = (idType) => {
      const mapping = {
        'cni': '01',           // Carte Nationale d'identité
        'elector': '02',       // Carte d'électeur
        'passport': '03',      // Passeport
        'driving_license': '05', // Permis de conduire
        'residence': '07',     // Carte de séjour
        'foreign_id': '10'     // Carte d'identité étrangère
      }
      return mapping[idType] || '01'
    }

    // Fonction pour formater les dates au format yyyymmdd
    const formatDateForXml = (dateString) => {
      if (!dateString) return ''
      const date = new Date(dateString)
      const year = date.getFullYear()
      const month = String(date.getMonth() + 1).padStart(2, '0')
      const day = String(date.getDate()).padStart(2, '0')
      return `${year}${month}${day}`
    }

    // Fonction pour générer le nom d'utilisateur
    const generateUsername = (firstName, lastName) => {
      const firstChar = firstName ? firstName.charAt(0).toLowerCase() : ''
      const lastNameLower = lastName ? lastName.toLowerCase() : ''
      return firstChar + lastNameLower
    }

    // Fonction pour générer le XML CREATE-ORG
    const generateCreateOrgXml = (applications) => {
      let xml = '<?xml version="1.0" encoding="UTF-8"?>\n<BulkCreateTopOrganizationRequest>\n'
      
      applications.forEach(app => {
        const businessPhone = (app.merchant_phone || app.business_phone || '').replace(/^\+228/, '')
        const shortCode = `228${businessPhone}`
        const organizationName = app.business_name || ''
        const email = app.email || ''
        const region = app.region || ''
        const phone = `228${businessPhone}`
        const firstName = app.first_name || ''
        const lastName = app.last_name || ''
        const nationality = app.nationality || ''
        const birthDate = formatDateForXml(app.birth_date)
        const idType = mapIdType(app.id_type)
        const idNumber = app.id_number || ''
        const idExpiryDate = formatDateForXml(app.id_expiry_date)
        const usageType = app.usage_type || ''

        // Helper function to add KYC tag only if value is not empty
        const addKycTag = (fieldType, fieldValue) => {
          if (fieldValue && fieldValue.trim() !== '') {
            return `\t\t\t<KYC FieldType="${fieldType}" FieldValue="${fieldValue}" />\n`
          }
          return ''
        }

        xml += `\t<Organization>
\t\t<ShortCode Value="${shortCode}" />
\t\t<OrganizationName Value="${organizationName}" />
\t\t<SimpleKYC>
`
        xml += addKycTag("[Contact Information][Company]", organizationName)
        xml += addKycTag("[Contact Information][Country]", "TGO")
        xml += addKycTag("[Contact Information][Email Address]", email)
        xml += addKycTag("[Contact Information][Region-field]", region)
        xml += addKycTag("[Contact Information][Alternate Number]", phone)
        xml += '\n'
        xml += addKycTag("[Owner Information][First Name]", firstName)
        xml += addKycTag("[Owner Information][Last Name]", lastName)
        xml += addKycTag("[Owner Information][Nationality]", nationality)
        xml += addKycTag("[Owner Information][Date of Birth]", birthDate)
        xml += addKycTag("[Owner Information][Owner ID Type]", idType)
        xml += addKycTag("[Owner Information][Owner ID Number]", idNumber)
        xml += addKycTag("[Owner Information][Owner ID Expiry Date]", idExpiryDate)
        xml += '\n'
        xml += addKycTag("[Organization Type][Organization Type]", usageType)
        xml += addKycTag("[Contact Details][Preferred Notification Language]", "fr")
        xml += '\n'
        xml += `\t\t\t<KYC FieldType="[Contact Details][Preferred Notification Channel]"
\t\t\t\tFieldValue="1001">
\t\t\t</KYC>
`
        if (phone && phone.trim() !== '') {
          xml += `\t\t\t<KYC FieldType="[Contact Details][Notification Receiving MSISDN]"
\t\t\t\tFieldValue="${phone}">
\t\t\t</KYC>
`
        }
        if (email && email.trim() !== '') {
          xml += `\t\t\t<KYC FieldType="[Contact Details][Notification Receiving E-mail]"
\t\t\t\tFieldValue="${email}">
\t\t\t</KYC>
`
        }
        xml += `\t\t</SimpleKYC>
\t</Organization>
`
      })
      
      xml += '</BulkCreateTopOrganizationRequest>'
      return xml
    }

    // Fonction pour générer le XML CREATE-ORG-OPERATOR
    const generateCreateOperatorXml = (applications) => {
      let xml = '<?xml version="1.0" encoding="UTF-8"?>\n<BulkCreateOrganizationOperatorRequest>\n'
      
      applications.forEach(app => {
        const businessPhone = (app.merchant_phone || app.business_phone || '').replace(/^\+228/, '')
        const shortCode = `228${businessPhone}`
        const username = generateUsername(app.first_name, app.last_name)
        const operatorId = `228${businessPhone}`
        const firstName = app.first_name || ''
        const lastName = app.last_name || ''
        const nationality = app.nationality || ''
        const birthDate = formatDateForXml(app.birth_date)
        const gender = app.gender || ''
        const birthPlace = app.birth_place || ''
        const usageType = app.usage_type || ''
        const idType = mapIdType(app.id_type)
        const idNumber = app.id_number || ''
        const idExpiryDate = formatDateForXml(app.id_expiry_date)
        const phone = `228${businessPhone}`

        // Helper function to add KYC tag only if value is not empty
        const addKycTag = (fieldType, fieldValue) => {
          if (fieldValue && fieldValue.trim() !== '') {
            return `\t\t\t<KYC FieldType="${fieldType}" FieldValue="${fieldValue}" />\n`
          }
          return ''
        }

        // Helper function to add MultipleKYC tag only if value is not empty
        const addMultipleKycTag = (fieldType, fieldValue) => {
          if (fieldValue && fieldValue.trim() !== '') {
            return `\t\t\t<KYC FieldType="${fieldType}" FieldValue="${fieldValue}" />\n`
          }
          return ''
        }

        xml += `\t<OrganizationOperator>
\t\t<Notification Language="FR" />
\t\t<Organization ShortCode="${shortCode}" />
\t\t<AuthenticationType Value="WEB" />
\t\t<AuthenticationType Value="HANDSET" />
\t\t<UserName Value="${username}" />		
\t\t<OperatorID Value="${operatorId}" />	
\t\t<SimpleKYC>
`
        xml += addKycTag("[Personal Details][First Name]", firstName)
        xml += addKycTag("[Personal Details][Last Name]", lastName)
        xml += addKycTag("[Personal Details][Nationality]", nationality)
        xml += addKycTag("[Personal Details][Date of Birth]", birthDate)
        xml += addKycTag("[Personal Details][Country]", "TGO")
        xml += addKycTag("[Personal Details][Gender]", gender)
        xml += addKycTag("[Personal Details][Place of Birth]", birthPlace)
        xml += addKycTag("[Personal Details][PLAINALIAS]", usageType)
        xml += `\t\t</SimpleKYC>
`

        // Only add MultipleKYC section if we have at least one non-empty value
        const hasIdDetails = (idType && idType.trim() !== '') || 
                            (idNumber && idNumber.trim() !== '') || 
                            (idExpiryDate && idExpiryDate.trim() !== '')
        
        if (hasIdDetails) {
          xml += `\t\t<MultipleKYC OperationType="Add">
`
          xml += addMultipleKycTag("[ID Details][ID Type]", idType)
          xml += addMultipleKycTag("[ID Details][ID Number]", idNumber)
          xml += addMultipleKycTag("[ID Details][ID Expiry Date]", idExpiryDate)
          xml += `\t\t</MultipleKYC>
`
        }

        xml += `\t\t<Role ID="500000000000013115" />
\t\t<MSISDN Value="${phone}" />
\t</OrganizationOperator>
`
      })
      
      xml += '</BulkCreateOrganizationOperatorRequest>'
      return xml
    }

    // Fonction pour télécharger un fichier
    const downloadFile = (filename, content) => {
      const blob = new Blob([content], { type: 'application/xml' })
      const url = window.URL.createObjectURL(blob)
      const a = document.createElement('a')
      a.href = url
      a.download = filename
      document.body.appendChild(a)
      a.click()
      document.body.removeChild(a)
      window.URL.revokeObjectURL(url)
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

    // Fonctions pour la gestion des candidatures avec pagination
    const loadApplications = async () => {
      try {
        applicationsLoading.value = true
        
        const params = {
          page: pagination.value.current_page,
          per_page: pagination.value.per_page,
          status: selectedStatus.value === 'all' ? null : selectedStatus.value,
          search: searchQuery.value || null
        }
        
        const response = await ApiService.getDashboardRecent(params)
        
        if (response.data.success) {
          recentApplications.value = response.data.data
          // S'assurer que les valeurs de pagination sont des nombres
          pagination.value = {
            ...response.data.pagination,
            current_page: Number(response.data.pagination.current_page),
            per_page: Number(response.data.pagination.per_page),
            total: Number(response.data.pagination.total),
            total_pages: Number(response.data.pagination.total_pages)
          }
        }
      } catch (error) {
        console.error('Erreur chargement candidatures:', error)
      } finally {
        applicationsLoading.value = false
      }
    }

    const handlePageChange = (newPage) => {
      pagination.value.current_page = Number(newPage)
      loadApplications()
    }

    const applyFilters = () => {
      pagination.value.current_page = 1 // Reset à la première page
      loadApplications()
    }

    const debouncedSearch = () => {
      clearTimeout(searchTimeout)
      searchTimeout = setTimeout(() => {
        applyFilters()
      }, 500) // Délai de 500ms
    }

    const resetFilters = () => {
      searchQuery.value = ''
      selectedStatus.value = 'all'
      applyFilters()
    }
    
    // Fonction pour adapter les textes selon la période sélectionnée
    const getPeriodText = (prefix = '') => {
      const periodTexts = {
        'today': `${prefix}aujourd'hui`,
        'week': `${prefix}cette semaine`,
        'month': `${prefix}ce mois`,
        'quarter': `${prefix}ce trimestre`,
        'year': `${prefix}cette année`,
        'all': `${prefix}au total`
      }
      return periodTexts[selectedPeriod.value] || `${prefix}aujourd'hui`
    }
    
    // Fonctions pour les statistiques utilisateurs
    const loadUserStats = async () => {
      if (!authStore.isAdmin) return
      
      console.log('Utilisateur admin détecté, chargement des stats utilisateur...', { 
        user: authStore.user, 
        isAdmin: authStore.isAdmin 
      })
      try {
        const params = {
          period: selectedPeriod.value,
          page: userCurrentPage.value,
          per_page: userPerPage.value,
          search: userSearchQuery.value || null
        }
        
        const userStatsRes = await ApiService.getDashboardUserStats(params)
        userStats.value = userStatsRes.data.data
        userPagination.value = userStatsRes.data.pagination
        console.log('Statistiques utilisateur chargées:', userStats.value.length, 'utilisateurs')
      } catch (error) {
        console.warn('Impossible de charger les statistiques utilisateur:', {
          status: error.response?.status,
          message: error.response?.data?.message,
          user: authStore.user?.username,
          roles: authStore.user?.roles
        })
        userStats.value = []
        userPagination.value = {
          current_page: 1,
          per_page: 10,
          total: 0,
          total_pages: 0,
          has_next: false,
          has_prev: false
        }
      }
    }

    const loadUserStatsPage = (page) => {
      userCurrentPage.value = Number(page)
      loadUserStats()
    }

    const debouncedUserSearch = () => {
      clearTimeout(userSearchTimeout)
      userSearchTimeout = setTimeout(() => {
        userCurrentPage.value = 1 // Reset à la première page
        loadUserStats()
      }, 500) // Délai de 500ms
    }

    const clearUserSearch = () => {
      userSearchQuery.value = ''
      userCurrentPage.value = 1
      loadUserStats()
    }

    // Fonction utilitaire pour générer les pages de pagination
    const getPaginationPages = (currentPage, totalPages) => {
      const pages = []
      const maxVisiblePages = 5

      if (totalPages <= maxVisiblePages) {
        for (let i = 1; i <= totalPages; i++) {
          pages.push(i)
        }
      } else {
        // Logique plus complexe pour afficher ... quand nécessaire
        if (currentPage <= 3) {
          for (let i = 1; i <= 4; i++) {
            pages.push(i)
          }
          pages.push('...')
          pages.push(totalPages)
        } else if (currentPage >= totalPages - 2) {
          pages.push(1)
          pages.push('...')
          for (let i = totalPages - 3; i <= totalPages; i++) {
            pages.push(i)
          }
        } else {
          pages.push(1)
          pages.push('...')
          for (let i = currentPage - 1; i <= currentPage + 1; i++) {
            pages.push(i)
          }
          pages.push('...')
          pages.push(totalPages)
        }
      }
      
      return pages
    }
    
    onMounted(() => {
      loadData()
      loadApplications() // Charger les candidatures séparément
    })
    
    return {
      authStore,
      loading,
      applicationsLoading,
      exportLoading,
      selectedPeriod,
      stats,
      kpis,
      alerts,
      recentApplications,
      userStats,
      searchQuery,
      selectedStatus,
      pagination,
      // Variables pour les stats utilisateurs
      userSearchQuery,
      userPerPage,
      userCurrentPage,
      userPagination,
      lineChart,
      pieChart,
      barChart,
      loadData,
      loadApplications,
      handlePageChange,
      applyFilters,
      debouncedSearch,
      resetFilters,
      refreshData,
      exportToSP,
      formatDate,
      getPeriodText,
      // Fonctions pour les stats utilisateurs
      loadUserStats,
      loadUserStatsPage,
      debouncedUserSearch,
      clearUserSearch,
      getPaginationPages
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
  height: 400px;
  display: flex;
  flex-direction: column;
}

.chart-card h3 {
  font-size: 1.1rem;
  font-weight: 600;
  margin-bottom: 1rem;
  color: #374151;
  flex: none;
}

.chart-card canvas {
  flex: 1;
  max-height: calc(100% - 2rem);
  width: 100% !important;
}
</style>