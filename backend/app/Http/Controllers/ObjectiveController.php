<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\UserObjective;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Illuminate\Pagination\LengthAwarePaginator;

class ObjectiveController extends Controller
{
    /**
     * Check if the authenticated user has admin role
     */
    private function checkAdminAccess(Request $request)
    {
        $user = $request->user();
        
        if (!$user) {
            return response()->json(['message' => 'Non authentifié'], 401);
        }
        
        if (!$user->hasRole('admin')) {
            return response()->json(['message' => 'Accès refusé. Droits administrateur requis.'], 403);
        }
        
        return null;
    }

    /**
     * Lister les objectifs
     */
    public function index(Request $request)
    {
        // Check admin access
        $accessCheck = $this->checkAdminAccess($request);
        if ($accessCheck) return $accessCheck;

        try {
            $query = UserObjective::with(['user']);

            // Filtres
            if ($request->has('user_id')) {
                $query->where('user_id', $request->user_id);
            }

            if ($request->has('year')) {
                $query->forYear($request->year);
            }

            if ($request->has('month')) {
                $query->forMonth($request->month);
            }

            if ($request->has('active')) {
                $query->active();
            }

            $objectives = $query->paginate($request->get('per_page', 15));

            return response()->json([
                'data' => $objectives->items(),
                'pagination' => [
                    'current_page' => $objectives->currentPage(),
                    'last_page' => $objectives->lastPage(),
                    'per_page' => $objectives->perPage(),
                    'total' => $objectives->total()
                ]
            ]);
        } catch (\Exception $e) {
            \Log::error('ObjectiveController@index error', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            return response()->json(['error' => 'Erreur interne du serveur'], 500);
        }
    }

    /**
     * Créer un objectif
     */
    public function store(Request $request)
    {
        // Check admin access
        $accessCheck = $this->checkAdminAccess($request);
        if ($accessCheck) return $accessCheck;

        $request->validate([
            'user_id' => 'nullable|exists:users,id', // null = objectif global par défaut
            'monthly_target' => 'required|integer|min:1',
            'yearly_target' => 'nullable|integer|min:0',
            'target_year' => 'required|integer|min:2020|max:2050',
            'target_month' => 'required|integer|min:1|max:12',
            'description' => 'nullable|string|max:500'
        ]);

        // Vérifier si un objectif existe déjà pour cette période
        $query = UserObjective::where('target_year', $request->target_year)
            ->where('target_month', $request->target_month);
        
        if ($request->user_id) {
            $query->where('user_id', $request->user_id);
        } else {
            $query->whereNull('user_id');
        }
        
        $existingObjective = $query->first();

        // Si un objectif existe, on le met à jour au lieu de créer un nouveau
        if ($existingObjective) {
            $existingObjective->update([
                'monthly_target' => $request->monthly_target,
                'yearly_target' => $request->yearly_target ?? 0,
                'description' => $request->description,
                'is_active' => $request->is_active ?? true
            ]);

            $message = $request->user_id 
                ? 'Objectif particulier mis à jour pour cette période.'
                : 'Objectif global mis à jour pour cette période.';
            
            return response()->json([
                'message' => $message,
                'data' => $existingObjective->load('user')
            ], 200);
        }

        // Sinon, créer un nouvel objectif
        $objectiveData = $request->all();
        $objectiveData['yearly_target'] = $request->yearly_target ?? 0;
        
        $objective = UserObjective::create($objectiveData);

        return response()->json([
            'message' => 'Objectif créé avec succès',
            'data' => $objective->load('user')
        ], 201);
    }

    /**
     * Afficher un objectif
     */
    public function show(Request $request, UserObjective $objective)
    {
        // Check admin access
        $accessCheck = $this->checkAdminAccess($request);
        if ($accessCheck) return $accessCheck;

        return response()->json([
            'data' => $objective->load('user')
        ]);
    }

    /**
     * Mettre à jour un objectif
     */
    public function update(Request $request, UserObjective $objective)
    {
        // Check admin access
        $accessCheck = $this->checkAdminAccess($request);
        if ($accessCheck) return $accessCheck;

        $request->validate([
            'monthly_target' => 'required|integer|min:1',
            'yearly_target' => 'nullable|integer|min:0',
            'description' => 'nullable|string|max:500',
            'is_active' => 'boolean'
        ]);

        $updateData = $request->all();
        $updateData['yearly_target'] = $request->yearly_target ?? 0;
        
        $objective->update($updateData);

        return response()->json([
            'message' => 'Objectif mis à jour avec succès',
            'data' => $objective->load('user')
        ]);
    }

    /**
     * Supprimer un objectif
     */
    public function destroy(Request $request, UserObjective $objective)
    {
        // Check admin access
        $accessCheck = $this->checkAdminAccess($request);
        if ($accessCheck) return $accessCheck;

        $objective->delete();

        return response()->json([
            'message' => 'Objectif supprimé avec succès'
        ]);
    }

    /**
     * Définir des objectifs pour tous les commerciaux
     */
    public function setBulkObjectives(Request $request)
    {
        // Check admin access
        $accessCheck = $this->checkAdminAccess($request);
        if ($accessCheck) return $accessCheck;

        $request->validate([
            'monthly_target' => 'integer|min:0',
            'yearly_target' => 'integer|min:0', 
            'target_year' => 'required|integer|min:2020|max:2050',
            'target_month' => 'nullable|integer|min:1|max:12',
            'description' => 'nullable|string|max:500',
            'user_ids' => 'array',
            'user_ids.*' => 'exists:users,id'
        ]);

        $userIds = $request->user_ids;
        
        // Si aucun utilisateur spécifié, prendre tous les commerciaux
        if (empty($userIds)) {
            $userIds = User::whereHas('roles', function($q) {
                $q->where('slug', 'commercial');
            })->pluck('id')->toArray();
        }

        $created = 0;
        $updated = 0;
        $errors = [];

        foreach ($userIds as $userId) {
            try {
                $existingObjective = UserObjective::where('user_id', $userId)
                    ->where('target_year', $request->target_year)
                    ->where('target_month', $request->target_month)
                    ->first();

                $objectiveData = [
                    'user_id' => $userId,
                    'monthly_target' => $request->monthly_target ?? 0,
                    'yearly_target' => $request->yearly_target ?? 0,
                    'target_year' => $request->target_year,
                    'target_month' => $request->target_month,
                    'description' => $request->description,
                    'is_active' => true
                ];

                if ($existingObjective) {
                    $existingObjective->update($objectiveData);
                    $updated++;
                } else {
                    UserObjective::create($objectiveData);
                    $created++;
                }

            } catch (\Exception $e) {
                $user = User::find($userId);
                $errors[] = "Erreur pour {$user->full_name}: " . $e->getMessage();
            }
        }

        return response()->json([
            'message' => "Objectifs définis avec succès",
            'data' => [
                'created' => $created,
                'updated' => $updated,
                'errors' => $errors,
                'total_users' => count($userIds)
            ]
        ]);
    }

    /**
     * Obtenir les statistiques de progression des objectifs
     */
    public function progressStats(Request $request)
    {
        // Check admin access
        $accessCheck = $this->checkAdminAccess($request);
        if ($accessCheck) return $accessCheck;

        $year = $request->get('year', date('Y'));
        $month = $request->get('month');

        // Récupérer tous les objectifs particuliers
        $query = UserObjective::with(['user'])
            ->forYear($year);

        if ($month) {
            $query->forMonth($month);
        }

        $particularObjectives = $query->active()->get();

        // Récupérer tous les objectifs globaux
        $globalObjectivesQuery = UserObjective::forYear($year);
        if ($month) {
            $globalObjectivesQuery->forMonth($month);
        }
        $globalObjectives = $globalObjectivesQuery->active()->whereNull('user_id')->get();

        // Récupérer tous les commerciaux
        $commercials = User::whereHas('roles', function($query) {
            $query->where('slug', 'commercial');
        })->where('is_active', true)->get();

        $stats = collect();

        // Traiter les objectifs particuliers existants
        foreach ($particularObjectives as $objective) {
            $user = $objective->user;
            $stats->push($this->calculateObjectiveStats($objective, $user, $year, $month));
        }

        // Pour chaque commercial, ajouter les statistiques des objectifs globaux
        foreach ($commercials as $commercial) {
            foreach ($globalObjectives as $globalObjective) {
                // Créer un objectif "virtuel" pour ce commercial basé sur l'objectif global
                $virtualObjective = new UserObjective([
                    'id' => 'global_' . $globalObjective->id . '_user_' . $commercial->id,
                    'user_id' => $commercial->id,
                    'yearly_target' => $globalObjective->yearly_target,
                    'monthly_target' => $globalObjective->monthly_target,
                    'target_year' => $globalObjective->target_year,
                    'target_month' => $globalObjective->target_month,
                    'description' => $globalObjective->description,
                    'is_active' => $globalObjective->is_active,
                    'created_at' => $globalObjective->created_at,
                    'updated_at' => $globalObjective->updated_at
                ]);
                $virtualObjective->user = $commercial;
                $virtualObjective->is_global_for_user = true; // Marquer comme objectif global appliqué

                $stats->push($this->calculateObjectiveStats($virtualObjective, $commercial, $year, $month));
            }
        }

        // Statistiques globales
        $totalApplications = $stats->sum('stats.applications');
        $totalTarget = $stats->sum('stats.target');
        $averageProgress = $stats->avg('stats.progress_percentage');

        return response()->json([
            'data' => $stats,
            'summary' => [
                'total_users' => $stats->count(),
                'total_applications' => $totalApplications,
                'total_target' => $totalTarget,
                'average_progress' => round($averageProgress, 2),
                'users_completed' => $stats->where('stats.status', 'completed')->count(),
                'users_on_track' => $stats->where('stats.status', 'on_track')->count(),
                'users_behind' => $stats->where('stats.status', 'behind')->count()
            ],
            'period' => [
                'year' => $year,
                'month' => $month,
                'type' => $month ? 'monthly' : 'yearly'
            ]
        ]);
    }

    /**
     * Retourne la liste fusionnée d'objectifs pour un commercial donné
     * (objectifs particuliers + objectifs globaux virtualisés)
     */
    public function forCommercial(Request $request, $userId)
    {
        // Check admin access
        $accessCheck = $this->checkAdminAccess($request);
        if ($accessCheck) return $accessCheck;

        $year = $request->get('year', date('Y'));
        $month = $request->get('month');

        // Charger l'utilisateur (commercial)
        $commercial = User::find($userId);
        if (!$commercial) {
            return response()->json(['message' => 'Commercial non trouvé'], 404);
        }

        // Récupérer objectifs particuliers pour ce commercial
        $particularQuery = UserObjective::with('user')
            ->where('user_id', $commercial->id)
            ->forYear($year);

        if ($month) $particularQuery->forMonth($month);
        $particularObjectives = $particularQuery->active()->get();

        // Récupérer objectifs globaux pour la période
        $globalQuery = UserObjective::forYear($year)->whereNull('user_id');
        if ($month) $globalQuery->forMonth($month);
        $globalObjectives = $globalQuery->active()->get();

        // Créer des objectifs virtuels basés sur les globaux
        $virtuals = collect();
        foreach ($globalObjectives as $g) {
            $virtual = new UserObjective([
                'id' => 'global_' . $g->id . '_user_' . $commercial->id,
                'user_id' => $commercial->id,
                'yearly_target' => $g->yearly_target,
                'monthly_target' => $g->monthly_target,
                'target_year' => $g->target_year,
                'target_month' => $g->target_month,
                'description' => $g->description,
                'is_active' => $g->is_active,
                'created_at' => $g->created_at,
                'updated_at' => $g->updated_at
            ]);
            $virtual->user = $commercial;
            $virtual->is_global_for_user = true;
            $virtuals->push($virtual);
        }

        // Fusionner les collections (particuliers d'abord, puis globaux virtuels)
        $merged = $particularObjectives->merge($virtuals);

        // Tri par date (année desc, mois desc)
        $sorted = $merged->sortByDesc(function ($item) {
            return sprintf('%04d-%02d', $item->target_year ?? 0, $item->target_month ?? 0);
        })->values();

        // Pagination manuelle
        $page = max(1, (int)$request->get('page', 1));
        $perPage = (int)$request->get('per_page', 10);
        $total = $sorted->count();
        $results = $sorted->slice(($page - 1) * $perPage, $perPage)->values();

        $paginator = new LengthAwarePaginator($results, $total, $perPage, $page, [
            'path' => LengthAwarePaginator::resolveCurrentPath(),
            'query' => $request->query()
        ]);

        return response()->json([
            'data' => $paginator->items(),
            'pagination' => [
                'current_page' => $paginator->currentPage(),
                'last_page' => $paginator->lastPage(),
                'per_page' => $paginator->perPage(),
                'total' => $paginator->total()
            ]
        ]);
    }

    /**
     * Obtenir l'objectif actuel de l'utilisateur connecté
     */
    public function getCurrentUserObjective(Request $request)
    {
        $user = $request->user();
        
        if (!$user) {
            \Log::error('getCurrentUserObjective: User not authenticated');
            return response()->json(['message' => 'Non authentifié'], 401);
        }
        
        \Log::info('getCurrentUserObjective called', [
            'user_id' => $user->id,
            'user_roles' => $user->roles->pluck('slug')->toArray()
        ]);
        
        // Vérifier que l'utilisateur est un commercial
        if (!$user->roles->pluck('slug')->contains('commercial')) {
            \Log::warning('getCurrentUserObjective: User is not commercial', [
                'user_id' => $user->id,
                'roles' => $user->roles->pluck('slug')->toArray()
            ]);
            return response()->json([
                'message' => 'Cette fonctionnalité est réservée aux commerciaux'
            ], 403);
        }

        $currentMonth = now()->month;
        $currentYear = now()->year;

        // Chercher d'abord un objectif particulier pour ce commercial
        $objective = UserObjective::where('user_id', $user->id)
            ->where('target_month', $currentMonth)
            ->where('target_year', $currentYear)
            ->first();

        \Log::info('Particular objective search', [
            'user_id' => $user->id,
            'month' => $currentMonth,
            'year' => $currentYear,
            'found' => $objective ? 'yes' : 'no'
        ]);

        // Si aucun objectif particulier, chercher l'objectif global par défaut
        if (!$objective) {
            $objective = UserObjective::whereNull('user_id')
                ->where('target_month', $currentMonth)
                ->where('target_year', $currentYear)
                ->first();
                
            \Log::info('Global objective search', [
                'month' => $currentMonth,
                'year' => $currentYear,
                'found' => $objective ? 'yes' : 'no'
            ]);
        }

        if (!$objective) {
            \Log::warning('No objective found for current month', [
                'user_id' => $user->id,
                'month' => $currentMonth,
                'year' => $currentYear
            ]);
            return response()->json([
                'data' => null,
                'message' => 'Aucun objectif défini pour le mois actuel'
            ]);
        }

        // Calculer les statistiques de progression
        $progress = $this->calculateUserProgress($user->id, $objective);

        return response()->json([
            'data' => [
                'objective' => $objective->load('user'),
                'progress' => $progress
            ]
        ]);
    }

    /**
     * Calculer la progression d'un utilisateur pour un objectif donné
     */
    private function calculateUserProgress($userId, $objective)
    {
        $now = now();
        $startOfMonth = now()->startOfMonth();
        $endOfMonth = now()->copy()->endOfMonth();
        $startOfWeek = now()->copy()->startOfWeek();
        $endOfWeek = now()->copy()->endOfWeek();
        $today = now()->toDateString();

        // Compter les candidatures créées par l'utilisateur (commercial)
        $monthlyQuery = \App\Models\MerchantApplication::where('user_id', $userId)
            ->whereBetween('created_at', [$startOfMonth, $endOfMonth]);
        
        $weeklyQuery = \App\Models\MerchantApplication::where('user_id', $userId)
            ->whereBetween('created_at', [$startOfWeek, $endOfWeek]);
        
        $todayQuery = \App\Models\MerchantApplication::where('user_id', $userId)
            ->whereDate('created_at', $today);

        \Log::info('calculateUserProgress', [
            'user_id' => $userId,
            'monthly' => $monthlyQuery->count(),
            'week' => $weeklyQuery->count(),
            'today' => $todayQuery->count()
        ]);

        return [
            'monthly' => $monthlyQuery->count(),
            'week' => $weeklyQuery->count(),
            'today' => $todayQuery->count()
        ];
    }

    /**
     * Calculer les statistiques pour un objectif donné
     */
    private function calculateObjectiveStats($objective, $user, $year, $month)
    {
        if ($month) {
            // Stats mensuelles
            $startDate = date('Y-m-01 00:00:00', strtotime("{$year}-{$month}-01"));
            $endDate = date('Y-m-t 23:59:59', strtotime($startDate));
            $applications = $user->countApplicationsForPeriod($startDate, $endDate);
            $target = $objective->monthly_target;
        } else {
            // Stats annuelles
            $startDate = date('Y-01-01 00:00:00', strtotime("{$year}-01-01"));
            $endDate = date('Y-12-31 23:59:59', strtotime("{$year}-12-31"));
            $applications = $user->countApplicationsForPeriod($startDate, $endDate);
            $target = $objective->yearly_target;
        }

        $progressPercentage = $target > 0 ? min(100, round(($applications / $target) * 100, 2)) : 0;

        return [
            'user' => [
                'id' => $user->id,
                'full_name' => $user->full_name,
                'email' => $user->email
            ],
            'objective' => [
                'id' => $objective->id,
                'target' => $target,
                'description' => $objective->description,
                'is_global_for_user' => $objective->is_global_for_user ?? false
            ],
            'stats' => [
                'applications' => $applications,
                'target' => $target,
                'progress_percentage' => $progressPercentage,
                'remaining' => max(0, $target - $applications),
                'status' => $progressPercentage >= 100 ? 'completed' : ($progressPercentage >= 80 ? 'on_track' : 'behind')
            ]
        ];
    }
}