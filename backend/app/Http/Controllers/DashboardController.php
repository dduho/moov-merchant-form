<?php

namespace App\Http\Controllers;

use App\Models\MerchantApplication;
use App\Models\ApplicationDocument;
use App\Models\User;
use App\Http\Resources\MerchantApplicationResource;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

/**
 * Contrôleur COMPLET pour le dashboard administrateur Moov Money
 * 
 * @package App\Http\Controllers
 * @author Moov Money Development Team
 * @version 1.0.0
 */
class DashboardController extends Controller
{
    /**
     * Applique le filtrage par utilisateur selon le rôle
     */
    private function applyUserFilter($query, Request $request = null)
    {
        $user = $request ? $request->user() : auth()->user();
        
        if ($user) {
            // Charger les rôles pour s'assurer qu'ils sont disponibles
            $user->load('roles');
            
            // Les commerciaux et le personnel ne voient que leurs propres candidatures
            if ($user->roles->contains('name', 'Commercial') || $user->roles->contains('name', 'Personnel')) {
                $query->where('user_id', $user->id);
            }
        }
        // Les admins voient toutes les candidatures (pas de filtre supplémentaire)
        
        return $query;
    }

    /**
     * Statistiques globales avec cache intelligent
     */
    public function stats(Request $request): JsonResponse
    {
        $period = $request->get('period', 'all');
        $refresh = $request->boolean('refresh', false);
        
        // Inclure l'ID utilisateur dans la clé de cache pour les commerciaux et le personnel
        $user = $request->user();
        $userKey = ($user && ($user->roles->contains('name', 'Commercial') || $user->roles->contains('name', 'Personnel'))) ? "_{$user->id}" : '';
        $cacheKey = "dashboard_stats_{$period}{$userKey}";
        $cacheDuration = 300; // 5 minutes
        
        try {
            $stats = $refresh 
                ? $this->calculateStats($period, $request)
                : Cache::remember($cacheKey, $cacheDuration, fn() => $this->calculateStats($period, $request));
            
            return response()->json([
                'success' => true,
                'data' => $stats,
                'cached' => !$refresh,
                'generated_at' => now()->toISOString()
            ]);
        } catch (\Exception $e) {
            Log::error('Erreur calcul stats', ['error' => $e->getMessage()]);
            return response()->json(['success' => false, 'message' => 'Erreur calcul stats'], 500);
        }
    }
    
    /**
     * Dernières candidatures avec pagination, filtres et recherche
     */
    public function recent(Request $request): JsonResponse
    {
        try {
            $perPage = min($request->get('per_page', 6), 50);
            $page = max($request->get('page', 1), 1);
            $status = $request->get('status');
            $search = $request->get('search');
            $userId = $request->get('user_id'); // Nouveau paramètre pour filtrer par utilisateur
            
            $query = MerchantApplication::with(['documents', 'user'])->latest('created_at');
            
            // Appliquer le filtrage par utilisateur
            $query = $this->applyUserFilter($query, $request);
            
            // Filtre supplémentaire par utilisateur (pour les admins)
            if ($userId && $request->user() && $request->user()->hasRole('admin')) {
                $query->where('user_id', $userId);
            }
            
            // Filtre par statut
            if ($status && $status !== 'all') {
                $query->where('status', $status);
            }
            
            // Recherche globale
            if ($search) {
                $query->where(function($q) use ($search) {
                    $q->where('reference_number', 'LIKE', "%{$search}%")
                      ->orWhere('first_name', 'LIKE', "%{$search}%")
                      ->orWhere('last_name', 'LIKE', "%{$search}%")
                      ->orWhere('business_name', 'LIKE', "%{$search}%")
                      ->orWhere('phone', 'LIKE', "%{$search}%")
                      ->orWhere('merchant_phone', 'LIKE', "%{$search}%")
                      ->orWhere('email', 'LIKE', "%{$search}%");
                });
            }
            
            // Pagination
            $total = $query->count();
            $applications = $query->skip(($page - 1) * $perPage)
                                  ->take($perPage)
                                  ->get();
            
            $totalPages = ceil($total / $perPage);
            
            return response()->json([
                'success' => true,
                'data' => MerchantApplicationResource::collection($applications),
                'pagination' => [
                    'current_page' => $page,
                    'per_page' => $perPage,
                    'total' => $total,
                    'total_pages' => $totalPages,
                    'has_next' => $page < $totalPages,
                    'has_prev' => $page > 1
                ]
            ]);
        } catch (\Exception $e) {
            Log::error('Erreur récupération dernières candidatures', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            return response()->json([
                'success' => false, 
                'message' => 'Erreur lors de la récupération des candidatures',
                'debug' => $e->getMessage()
            ], 500);
        }
    }
    
    /**
     * Tendances temporelles
     */
    public function trends(Request $request): JsonResponse
    {
        $period = $request->get('period', 'month');
        $groupBy = $request->get('group_by', 'day');
        
        $startDate = match($period) {
            'week' => now()->subWeek(),
            'month' => now()->subMonth(),
            'quarter' => now()->subMonths(3),
            'year' => now()->subYear(),
            default => now()->subMonth()
        };
        
        $dateFormat = match($groupBy) {
            'day' => '%Y-%m-%d',
            'week' => '%Y-%u',
            'month' => '%Y-%m',
            default => '%Y-%m-%d'
        };
        
        $trendsQuery = MerchantApplication::where('created_at', '>=', $startDate);
        $trendsQuery = $this->applyUserFilter($trendsQuery, $request);
        $applicationTrends = $trendsQuery
            ->select(
                DB::raw("DATE_FORMAT(created_at, '{$dateFormat}') as period"),
                DB::raw('count(*) as count'),
                'status'
            )
            ->groupBy('period', 'status')
            ->orderBy('period')
            ->get()
            ->groupBy('period')
            ->map(fn($items) => $items->pluck('count', 'status')->toArray());
        
        return response()->json([
            'success' => true,
            'data' => ['applications_by_status' => $applicationTrends],
            'period' => $period
        ]);
    }
    
    /**
     * Indicateurs de performance (KPIs) dynamiques selon la période
     */
    public function kpis(Request $request): JsonResponse
    {
        try {
            $user = $request->user();
            $period = $request->get('period', 'all');
            
            // Calculs pour la période courante
            $currentPeriodQuery = MerchantApplication::query();
            $currentPeriodQuery = $this->applyPeriodFilter($currentPeriodQuery, $period);
            $currentPeriodQuery = $this->applyUserFilter($currentPeriodQuery, $request);
            $currentPeriodCount = $currentPeriodQuery->count();
            
            // Calculs pour la période précédente (pour la croissance)
            $previousPeriodQuery = MerchantApplication::query();
            $previousPeriodQuery = $this->applyPreviousPeriodFilter($previousPeriodQuery, $period);
            $previousPeriodQuery = $this->applyUserFilter($previousPeriodQuery, $request);
            $previousPeriodCount = $previousPeriodQuery->count();
            $growthPercentage = $previousPeriodCount > 0 
                ? round((($currentPeriodCount - $previousPeriodCount) / $previousPeriodCount) * 100, 2)
                : 0;
            
            // Applications approuvées pour la période courante
            $approvedCurrentQuery = MerchantApplication::where('status', 'approved');
            $approvedCurrentQuery = $this->applyPeriodFilter($approvedCurrentQuery, $period);
            $approvedCurrentQuery = $this->applyUserFilter($approvedCurrentQuery, $request);
            $approvedCurrent = $approvedCurrentQuery->count();
            
            // Taux de conversion pour la période courante
            $conversionRate = $currentPeriodCount > 0
                ? round(($approvedCurrent / $currentPeriodCount) * 100, 2)
                : 0;
            
            // Applications traitées (approuvées + rejetées) pour la période courante
            $processedCurrentQuery = MerchantApplication::whereIn('status', ['approved', 'rejected']);
            $processedCurrentQuery = $this->applyPeriodFilter($processedCurrentQuery, $period);
            $processedCurrentQuery = $this->applyUserFilter($processedCurrentQuery, $request);
            $processedCurrent = $processedCurrentQuery->count();
            
            // Temps de réponse moyen pour la période courante
            // Ne calculer que pour les candidatures approuvées ou rejetées (réellement traitées)
            // Utiliser created_at si submitted_at est null
            $avgResponseTimeQuery = MerchantApplication::whereNotNull('reviewed_at')
                ->whereIn('status', ['approved', 'rejected', 'exported_for_creation', 'exported_for_update']);
            $avgResponseTimeQuery = $this->applyPeriodFilter($avgResponseTimeQuery, $period);
            $avgResponseTimeQuery = $this->applyUserFilter($avgResponseTimeQuery, $request);
            $avgResponseTime = $avgResponseTimeQuery
                ->selectRaw('AVG(TIMESTAMPDIFF(HOUR, COALESCE(submitted_at, created_at), reviewed_at)) as avg')
                ->value('avg');
            
            return response()->json([
                'success' => true,
                'data' => [
                    'daily_activity' => [
                        // Le frontend utilise ces clés dynamiquement selon la période
                        'today' => $period === 'today' ? $currentPeriodCount : 0,
                        'this_week' => $period === 'week' ? $currentPeriodCount : 0,
                        'this_month' => $period === 'month' ? $currentPeriodCount : 0,
                        'this_quarter' => $period === 'quarter' ? $currentPeriodCount : 0,
                        'this_year' => $period === 'year' ? $currentPeriodCount : 0,
                        'total' => $currentPeriodCount,
                        'growth_percentage' => $growthPercentage,
                        'trend' => $growthPercentage > 0 ? 'up' : ($growthPercentage < 0 ? 'down' : 'stable')
                    ],
                    'validated_applications' => [
                        // Applications approuvées selon la période
                        'approved_today' => $period === 'today' ? $approvedCurrent : 0,
                        'approved_this_week' => $period === 'week' ? $approvedCurrent : 0,
                        'approved_this_month' => $period === 'month' ? $approvedCurrent : 0,
                        'approved_this_quarter' => $period === 'quarter' ? $approvedCurrent : 0,
                        'approved_this_year' => $period === 'year' ? $approvedCurrent : 0,
                        'total_approved' => $approvedCurrent
                    ],
                    'conversion' => [
                        // Taux de conversion selon la période
                        'rate_today' => $period === 'today' ? $conversionRate : 0,
                        'rate_this_week' => $period === 'week' ? $conversionRate : 0,
                        'rate_this_month' => $period === 'month' ? $conversionRate : 0,
                        'rate_this_quarter' => $period === 'quarter' ? $conversionRate : 0,
                        'rate_this_year' => $period === 'year' ? $conversionRate : 0,
                        'rate_percentage' => $conversionRate,
                        'approved_today' => $period === 'today' ? $approvedCurrent : 0,
                        'approved_this_week' => $period === 'week' ? $approvedCurrent : 0,
                        'approved_this_month' => $period === 'month' ? $approvedCurrent : 0,
                        'approved_this_quarter' => $period === 'quarter' ? $approvedCurrent : 0,
                        'approved_this_year' => $period === 'year' ? $approvedCurrent : 0
                    ],
                    'processing_performance' => [
                        // Performance de traitement selon la période
                        'avg_time_today' => $period === 'today' ? round($avgResponseTime ?? 0, 1) : 0,
                        'avg_time_this_week' => $period === 'week' ? round($avgResponseTime ?? 0, 1) : 0,
                        'avg_time_this_month' => $period === 'month' ? round($avgResponseTime ?? 0, 1) : 0,
                        'avg_time_this_quarter' => $period === 'quarter' ? round($avgResponseTime ?? 0, 1) : 0,
                        'avg_time_this_year' => $period === 'year' ? round($avgResponseTime ?? 0, 1) : 0,
                        'avg_response_time_hours' => round($avgResponseTime ?? 0, 1),
                        'processed_today' => $period === 'today' ? $processedCurrent : 0,
                        'processed_this_week' => $period === 'week' ? $processedCurrent : 0,
                        'processed_this_month' => $period === 'month' ? $processedCurrent : 0,
                        'processed_this_quarter' => $period === 'quarter' ? $processedCurrent : 0,
                        'processed_this_year' => $period === 'year' ? $processedCurrent : 0,
                        'processed_total' => $processedCurrent
                    ]
                ]
            ]);
        } catch (\Exception $e) {
            Log::error('Erreur KPIs', ['error' => $e->getMessage()]);
            return response()->json(['success' => false], 500);
        }
    }
    
    /**
     * Statistiques par utilisateur (Admin uniquement)
     */
    public function userStats(Request $request): JsonResponse
    {
        try {
            $user = $request->user();
            
            // Vérifier que l'utilisateur existe
            if (!$user) {
                return response()->json([
                    'success' => false,
                    'message' => 'Utilisateur non authentifié'
                ], 401);
            }
            
            // Charger les rôles si ce n'est pas déjà fait
            $user->load('roles');
            
            // Vérifier que l'utilisateur est admin
            if (!$user->roles->contains('slug', 'admin')) {
                return response()->json([
                    'success' => false,
                    'message' => 'Accès non autorisé - Droits admin requis'
                ], 403);
            }
            
            $period = $request->get('period', 'all');
            $search = $request->get('search', '');
            $perPage = $request->get('per_page', 10); // Nombre d'éléments par page
            $page = $request->get('page', 1);
            
            // Construire la requête de base pour les utilisateurs
            $usersQuery = User::with('roles');
            
            // Appliquer la recherche si un terme est fourni
            if (!empty($search)) {
                $usersQuery->where(function($query) use ($search) {
                    $query->where('first_name', 'LIKE', "%{$search}%")
                          ->orWhere('last_name', 'LIKE', "%{$search}%")
                          ->orWhere('username', 'LIKE', "%{$search}%")
                          ->orWhere('email', 'LIKE', "%{$search}%")
                          ->orWhere('phone', 'LIKE', "%{$search}%");
                });
            }
            
            // Récupérer tous les utilisateurs pour calculer les stats (avant pagination)
            $allUsers = $usersQuery->get();
            
            $userStats = [];
            
            foreach ($allUsers as $userItem) {
                // Query de base pour les candidatures de cet utilisateur
                $baseQuery = MerchantApplication::where('user_id', $userItem->id);
                $baseQuery = $this->applyPeriodFilter($baseQuery, $period);
                
                // Total soumis par cet utilisateur
                $totalSubmitted = $baseQuery->count();
                
                // Candidatures validées (approuvées) par cet utilisateur
                $totalApproved = (clone $baseQuery)->where('status', 'approved')->count();
                
                // Candidatures rejetées par cet utilisateur
                $totalRejected = (clone $baseQuery)->where('status', 'rejected')->count();
                
                // Candidatures en attente par cet utilisateur
                $totalPending = (clone $baseQuery)->where('status', 'pending')->count();
                
                // Calcul du taux de conversion (approuvées / total soumis * 100)
                $conversionRate = $totalSubmitted > 0 
                    ? round(($totalApproved / $totalSubmitted) * 100, 2)
                    : 0;
                
                // Afficher tous les utilisateurs correspondant à la recherche
                $userStats[] = [
                    'user_id' => $userItem->id,
                    'username' => $userItem->username,
                    'first_name' => $userItem->first_name,
                    'last_name' => $userItem->last_name,
                    'full_name' => $userItem->first_name . ' ' . $userItem->last_name,
                    'email' => $userItem->email,
                    'phone' => $userItem->phone,
                    'roles' => $userItem->roles->pluck('name')->toArray(),
                    'stats' => [
                        'total_submitted' => $totalSubmitted,
                        'total_approved' => $totalApproved,
                        'total_rejected' => $totalRejected,
                        'total_pending' => $totalPending,
                        'conversion_rate' => $conversionRate
                    ]
                ];
            }
            
            // Trier par nombre total de candidatures soumises (descendant)
            usort($userStats, function ($a, $b) {
                return $b['stats']['total_submitted'] <=> $a['stats']['total_submitted'];
            });
            
            // Calculer les informations de pagination
            $totalResults = count($userStats);
            $totalPages = ceil($totalResults / $perPage);
            $offset = ($page - 1) * $perPage;
            
            // Appliquer la pagination
            $paginatedStats = array_slice($userStats, $offset, $perPage);
            
            return response()->json([
                'success' => true,
                'data' => $paginatedStats,
                'pagination' => [
                    'current_page' => (int)$page,
                    'per_page' => (int)$perPage,
                    'total' => $totalResults,
                    'total_pages' => $totalPages,
                    'has_prev' => $page > 1,
                    'has_next' => $page < $totalPages
                ],
                'period' => $period,
                'search' => $search
            ]);
            
        } catch (\Exception $e) {
            Log::error('Erreur statistiques utilisateur', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            return response()->json([
                'success' => false, 
                'message' => 'Erreur interne: ' . $e->getMessage()
            ], 500);
        }
    }
    
    /**
     * Debug utilisateur - pour tester l'authentification
     */
    public function debugUser(Request $request): JsonResponse
    {
        $user = $request->user();
        
        if (!$user) {
            return response()->json([
                'authenticated' => false,
                'message' => 'Utilisateur non authentifié'
            ]);
        }
        
        $user->load('roles');
        
        return response()->json([
            'authenticated' => true,
            'user' => [
                'id' => $user->id,
                'username' => $user->username,
                'email' => $user->email,
                'first_name' => $user->first_name,
                'last_name' => $user->last_name,
                'roles' => $user->roles->pluck('name')->toArray()
            ],
            'is_admin' => $user->roles->contains('slug', 'admin'),
            'headers' => [
                'authorization' => $request->header('Authorization'),
                'cookie' => $request->header('Cookie') ? 'Present' : 'Not present',
                'x-requested-with' => $request->header('X-Requested-With')
            ]
        ]);
    }
    
    /**
     * Alertes système
     */
    public function alerts(Request $request): JsonResponse
    {
        $alerts = [];
        
        $criticalPendingQuery = MerchantApplication::where('status', 'pending')
            ->where('created_at', '<', now()->subHours(72));
        $criticalPendingQuery = $this->applyUserFilter($criticalPendingQuery, $request);
        $criticalPending = $criticalPendingQuery->count();
        
        if ($criticalPending > 0) {
            $alerts[] = [
                'id' => 'critical_pending',
                'type' => 'error',
                'priority' => 'high',
                'title' => 'Candidatures critiques',
                'message' => "{$criticalPending} candidature(s) en attente depuis plus de 72h",
                'action' => 'review_pending',
                'count' => $criticalPending,
                'icon' => 'alert-triangle'
            ];
        }
        
        $unverifiedDocs = ApplicationDocument::where('is_verified', false)
            ->whereNotNull('merchant_application_id')
            ->where('created_at', '<', now()->subDays(2))
            ->count();
        
        if ($unverifiedDocs > 10) {
            $alerts[] = [
                'id' => 'unverified_documents',
                'type' => 'warning',
                'priority' => 'medium',
                'title' => 'Documents à vérifier',
                'message' => "{$unverifiedDocs} documents en attente depuis plus de 2 jours",
                'action' => 'verify_documents',
                'count' => $unverifiedDocs,
                'icon' => 'file-text'
            ];
        }
        
        return response()->json([
            'success' => true,
            'data' => $alerts,
            'count' => count($alerts),
            'has_critical' => collect($alerts)->where('type', 'error')->isNotEmpty()
        ]);
    }
    
    /**
     * Comparaison de périodes
     */
    public function compare(Request $request): JsonResponse
    {
        $period1 = $request->get('period1', 'this_month');
        $period2 = $request->get('period2', 'last_month');
        
        $dates1 = $this->getPeriodDates($period1);
        $dates2 = $this->getPeriodDates($period2);
        
        $stats1 = $this->getStatsForPeriod($dates1['start'], $dates1['end']);
        $stats2 = $this->getStatsForPeriod($dates2['start'], $dates2['end']);
        
        $comparison = [
            'total_applications' => [
                'period1' => $stats1['total'],
                'period2' => $stats2['total'],
                'difference' => $stats1['total'] - $stats2['total'],
                'percentage_change' => $stats2['total'] > 0 
                    ? round((($stats1['total'] - $stats2['total']) / $stats2['total']) * 100, 2)
                    : 0
            ],
            'approved' => [
                'period1' => $stats1['approved'],
                'period2' => $stats2['approved'],
                'difference' => $stats1['approved'] - $stats2['approved']
            ],
            'approval_rate' => [
                'period1' => $stats1['approval_rate'],
                'period2' => $stats2['approval_rate'],
                'difference' => round($stats1['approval_rate'] - $stats2['approval_rate'], 2)
            ]
        ];
        
        return response()->json([
            'success' => true,
            'data' => $comparison,
            'periods' => [
                'period1' => ['name' => $period1, 'start' => $dates1['start']->toISOString()],
                'period2' => ['name' => $period2, 'start' => $dates2['start']->toISOString()]
            ]
        ]);
    }
    
    /**
     * Export de données
     */
    public function export(Request $request): JsonResponse
    {
        $format = $request->get('format', 'json');
        $period = $request->get('period', 'all');
        
        $query = MerchantApplication::query();
        $query = $this->applyPeriodFilter($query, $period);
        $data = $query->get();
        
        if ($format === 'csv') {
            return $this->exportToCsv($data);
        }
        
        return response()->json([
            'success' => true,
            'data' => MerchantApplicationResource::collection($data),
            'meta' => ['count' => $data->count(), 'format' => $format]
        ]);
    }
    
    /**
     * Résumé rapide
     */
    public function summary(): JsonResponse
    {
        $summary = Cache::remember('dashboard_summary', 300, function () {
            return [
                'applications' => [
                    'total' => MerchantApplication::count(),
                    'pending' => MerchantApplication::where('status', 'pending')->count(),
                    'today' => MerchantApplication::whereDate('created_at', today())->count()
                ],
                'documents' => [
                    'total' => ApplicationDocument::count(),
                    'unverified' => ApplicationDocument::where('is_verified', false)->count()
                ],
                'performance' => [
                    'approval_rate' => $this->calculateApprovalRate(),
                    'avg_processing_hours' => $this->calculateAvgProcessingTime()
                ]
            ];
        });
        
        return response()->json(['success' => true, 'data' => $summary]);
    }
    
    /**
     * Données pour graphiques
     */
    public function charts(Request $request): JsonResponse
    {
        $period = $request->get('period', 'month');
        
        try {
            $linechartData = $this->getLinechartData($period, $request);
            
            $piechartQuery = MerchantApplication::select('status', DB::raw('count(*) as count'))
                ->groupBy('status');
            $piechartQuery = $this->applyUserFilter($piechartQuery, $request);
            $piechartData = $piechartQuery->pluck('count', 'status')->toArray();
            
            $barchartQuery = MerchantApplication::select('business_type', DB::raw('count(*) as count'))
                ->groupBy('business_type');
            $barchartQuery = $this->applyUserFilter($barchartQuery, $request);
            $barchartData = $barchartQuery->pluck('count', 'business_type')->toArray();
            
            return response()->json([
                'success' => true,
                'data' => [
                    'line_chart' => [
                        'labels' => array_keys($linechartData),
                        'datasets' => [[
                            'label' => 'Candidatures',
                            'data' => array_values($linechartData),
                            'borderColor' => '#ff6b35',
                            'backgroundColor' => 'rgba(255, 107, 53, 0.1)'
                        ]]
                    ],
                    'pie_chart' => [
                        'labels' => array_keys($piechartData),
                        'datasets' => [[
                            'data' => array_values($piechartData),
                            'backgroundColor' => ['#ffc107', '#2196f3', '#4caf50', '#f44336', '#ff9800']
                        ]]
                    ],
                    'bar_chart' => [
                        'labels' => array_keys($barchartData),
                        'datasets' => [[
                            'label' => 'Nombre',
                            'data' => array_values($barchartData),
                            'backgroundColor' => '#ff6b35'
                        ]]
                    ]
                ]
            ]);
        } catch (\Exception $e) {
            Log::error('Erreur charts', ['error' => $e->getMessage()]);
            return response()->json(['success' => false], 500);
        }
    }
    
    // =================================================================
    // MÉTHODES PRIVÉES
    // =================================================================
    
    private function calculateStats(string $period, Request $request = null): array
    {
        $query = MerchantApplication::query();
        $query = $this->applyPeriodFilter($query, $period);
        $query = $this->applyUserFilter($query, $request);
        
        $total = $query->count();
        
        $byStatus = $query->clone()
            ->select('status', DB::raw('count(*) as count'))
            ->groupBy('status')
            ->pluck('count', 'status')
            ->toArray();
        
        $byBusinessType = $query->clone()
            ->select('business_type', DB::raw('count(*) as count'))
            ->groupBy('business_type')
            ->pluck('count', 'business_type')
            ->toArray();
        
        // Appliquer le filtrage de période ET utilisateur aux requêtes séparées
        $approvedQuery = MerchantApplication::where('status', 'approved');
        $approvedQuery = $this->applyPeriodFilter($approvedQuery, $period);
        $approvedQuery = $this->applyUserFilter($approvedQuery, $request);
        $approved = $approvedQuery->count();
        
        $reviewedQuery = MerchantApplication::whereIn('status', ['approved', 'rejected']);
        $reviewedQuery = $this->applyPeriodFilter($reviewedQuery, $period);
        $reviewedQuery = $this->applyUserFilter($reviewedQuery, $request);
        $reviewed = $reviewedQuery->count();
        $approvalRate = $reviewed > 0 ? round(($approved / $reviewed) * 100, 2) : 0;
        
        return [
            'overview' => [
                'total_applications' => $total,
                'pending' => $byStatus['pending'] ?? 0,
                'under_review' => $byStatus['under_review'] ?? 0,
                'approved' => $byStatus['approved'] ?? 0,
                'rejected' => $byStatus['rejected'] ?? 0
            ],
            'by_status' => $byStatus,
            'by_business_type' => $byBusinessType,
            'performance_metrics' => [
                'approval_rate' => $approvalRate
            ]
        ];
    }
    
    private function applyPeriodFilter($query, string $period)
    {
        return match($period) {
            'today' => $query->whereDate('created_at', today()),
            'week' => $query->whereBetween('created_at', [now()->startOfWeek(), now()->endOfWeek()]),
            'month' => $query->whereMonth('created_at', now()->month)->whereYear('created_at', now()->year),
            'quarter' => $query->whereBetween('created_at', [now()->startOfQuarter(), now()->endOfQuarter()]),
            'year' => $query->whereYear('created_at', now()->year),
            default => $query
        };
    }
    
    private function applyPreviousPeriodFilter($query, string $period)
    {
        return match($period) {
            'today' => $query->whereDate('created_at', now()->subDay()),
            'week' => $query->whereBetween('created_at', [now()->subWeek()->startOfWeek(), now()->subWeek()->endOfWeek()]),
            'month' => $query->whereMonth('created_at', now()->subMonth()->month)->whereYear('created_at', now()->subMonth()->year),
            'quarter' => $query->whereBetween('created_at', [now()->subQuarter()->startOfQuarter(), now()->subQuarter()->endOfQuarter()]),
            'year' => $query->whereYear('created_at', now()->subYear()->year),
            default => $query->where('created_at', '<', match($period) {
                'week' => now()->startOfWeek(),
                'month' => now()->startOfMonth(),
                'quarter' => now()->startOfQuarter(),
                'year' => now()->startOfYear(),
                default => now()->startOfDay()
            })
        };
    }
    
    private function getPeriodDates(string $period): array
    {
        return match($period) {
            'this_month' => ['start' => now()->startOfMonth(), 'end' => now()->endOfMonth()],
            'last_month' => ['start' => now()->subMonth()->startOfMonth(), 'end' => now()->subMonth()->endOfMonth()],
            'this_week' => ['start' => now()->startOfWeek(), 'end' => now()->endOfWeek()],
            default => ['start' => now()->startOfMonth(), 'end' => now()->endOfMonth()]
        };
    }
    
    private function getStatsForPeriod($start, $end): array
    {
        $applications = MerchantApplication::whereBetween('created_at', [$start, $end]);
        
        $total = $applications->count();
        $approved = $applications->clone()->where('status', 'approved')->count();
        $rejected = $applications->clone()->where('status', 'rejected')->count();
        
        $reviewed = $approved + $rejected;
        $approvalRate = $reviewed > 0 ? round(($approved / $reviewed) * 100, 2) : 0;
        
        return [
            'total' => $total,
            'approved' => $approved,
            'rejected' => $rejected,
            'approval_rate' => $approvalRate
        ];
    }
    
    private function calculateApprovalRate(): float
    {
        $approved = MerchantApplication::where('status', 'approved')->count();
        $reviewed = MerchantApplication::whereIn('status', ['approved', 'rejected'])->count();
        
        return $reviewed > 0 ? round(($approved / $reviewed) * 100, 2) : 0;
    }
    
    private function calculateAvgProcessingTime(): float
    {
        $avg = MerchantApplication::whereNotNull('reviewed_at')
            ->whereIn('status', ['approved', 'rejected', 'exported_for_creation', 'exported_for_update'])
            ->selectRaw('AVG(TIMESTAMPDIFF(HOUR, COALESCE(submitted_at, created_at), reviewed_at)) as avg')
            ->value('avg');

        return round($avg ?? 0, 1);
    }
    
    private function exportToCsv($data)
    {
        $csv = "Reference,Nom,Telephone,Email,Commerce,Type,Statut,Date\n";
        
        foreach ($data as $app) {
            $csv .= implode(',', [
                $app->reference_number,
                '"' . str_replace('"', '""', $app->full_name) . '"',
                $app->phone,
                $app->email ?? '',
                '"' . str_replace('"', '""', $app->business_name) . '"',
                $app->business_type,
                $app->status,
                $app->created_at->format('Y-m-d H:i:s')
            ]) . "\n";
        }
        
        $filename = 'export_' . date('Y-m-d_H-i-s') . '.csv';
        
        return response($csv, 200, [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => "attachment; filename=\"{$filename}\""
        ]);
    }
    
    private function getLinechartData(string $period, Request $request = null): array
    {
        $days = match($period) {
            'week' => 7,
            'month' => 30,
            'quarter' => 90,
            default => 30
        };
        
        $data = [];
        
        for ($i = $days - 1; $i >= 0; $i--) {
            $date = now()->subDays($i)->format('Y-m-d');
            $query = MerchantApplication::whereDate('created_at', now()->subDays($i));
            $query = $this->applyUserFilter($query, $request);
            $count = $query->count();
            $data[$date] = $count;
        }
        
        return $data;
    }
}