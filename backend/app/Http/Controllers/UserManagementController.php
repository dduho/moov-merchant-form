<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\UserObjective;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Facades\DB;

class UserManagementController extends Controller
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
     * Lister tous les utilisateurs
     */
    public function index(Request $request)
    {
        // Check admin access
        $accessCheck = $this->checkAdminAccess($request);
        if ($accessCheck) return $accessCheck;

        try {
            $query = User::with(['roles']);

        // Filtres
        if ($request->filled('role')) {  // filled() vérifie que c'est non vide
            $query->whereHas('roles', function($q) use ($request) {
                $q->where('slug', $request->role);
            });
        }

        if ($request->filled('status')) {  // filled() au lieu de has()
            switch ($request->status) {
                case 'active':
                    $query->where('is_active', true)->where('is_blocked', false);
                    break;
                case 'blocked':
                    $query->where('is_blocked', true);
                    break;
                case 'inactive':
                    $query->where('is_active', false);
                    break;
            }
        }

        // Recherche
        if ($request->filled('search')) {  // filled() au lieu de has()
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('first_name', 'like', "%{$search}%")
                  ->orWhere('last_name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%")
                  ->orWhere('phone', 'like', "%{$search}%");
            });
        }

        $users = $query->paginate($request->get('per_page', 15));

        // Ajouter les statistiques pour chaque commercial
        $users->getCollection()->transform(function ($user) {
            try {
                if ($user->roles && $user->roles->contains('slug', 'commercial')) {
                    $currentMonth = date('Y-m-01');
                    $nextMonth = date('Y-m-01', strtotime('+1 month'));
                    $currentYear = date('Y-01-01');
                    $nextYear = date('Y-01-01', strtotime('+1 year'));

                    $user->stats = [
                        'applications_this_month' => $user->countApplicationsForPeriod($currentMonth, $nextMonth),
                        'applications_this_year' => $user->countApplicationsForPeriod($currentYear, $nextYear),
                        'objective' => $user->currentObjective()
                    ];
                }
            } catch (\Exception $e) {
                // En cas d'erreur, on continue sans les stats
                \Log::warning('Error loading user stats', [
                    'user_id' => $user->id,
                    'error' => $e->getMessage()
                ]);
            }
            return $user;
        });

            return response()->json([
                'data' => $users->items(),
                'pagination' => [
                    'current_page' => $users->currentPage(),
                    'last_page' => $users->lastPage(),
                    'per_page' => $users->perPage(),
                    'total' => $users->total()
                ]
            ]);
        } catch (\Exception $e) {
            \Log::error('Error in UserManagementController::index', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            return response()->json([
                'message' => 'Erreur lors du chargement des utilisateurs',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Récupérer la liste des commerciaux pour l'attribution d'objectifs
     */
    public function getCommercials(Request $request)
    {
        // Check admin access
        $accessCheck = $this->checkAdminAccess($request);
        if ($accessCheck) return $accessCheck;

        $commercials = User::with('roles')
            ->whereHas('roles', function($q) {
                $q->where('slug', 'commercial');
            })
            ->where('is_active', true)
            ->where('is_blocked', false)
            ->select(['id', 'first_name', 'last_name', 'email'])
            ->get();

        return response()->json([
            'success' => true,
            'data' => $commercials
        ]);
    }

    /**
     * Réinitialiser le mot de passe d'un utilisateur
     */
    public function resetPassword(Request $request, User $user)
    {
        // Check admin access
        $accessCheck = $this->checkAdminAccess($request);
        if ($accessCheck) return $accessCheck;

        $request->validate([
            'new_password' => 'string|min:6|max:255'
        ]);

        $newPassword = $request->get('new_password', 'password');
        $user->resetPassword($newPassword);

        return response()->json([
            'message' => 'Mot de passe réinitialisé avec succès',
            'data' => [
                'user_id' => $user->id,
                'new_password' => $newPassword,
                'must_change_password' => true
            ]
        ]);
    }

    /**
     * Bloquer/débloquer un utilisateur
     */
    public function toggleBlock(Request $request, User $user)
    {
        // Check admin access
        $accessCheck = $this->checkAdminAccess($request);
        if ($accessCheck) return $accessCheck;

        $user->is_blocked = !$user->is_blocked;
        $user->save();

        return response()->json([
            'message' => $user->is_blocked ? 'Utilisateur bloqué' : 'Utilisateur débloqué',
            'data' => [
                'user_id' => $user->id,
                'is_blocked' => $user->is_blocked
            ]
        ]);
    }

    /**
     * Activer/désactiver un utilisateur
     */
    public function toggleActive(Request $request, User $user)
    {
        // Check admin access
        $accessCheck = $this->checkAdminAccess($request);
        if ($accessCheck) return $accessCheck;

        $user->is_active = !$user->is_active;
        $user->save();

        return response()->json([
            'message' => $user->is_active ? 'Utilisateur activé' : 'Utilisateur désactivé',
            'data' => [
                'user_id' => $user->id,
                'is_active' => $user->is_active
            ]
        ]);
    }

    /**
     * Obtenir les statistiques détaillées d'un utilisateur
     */
    public function userStats(Request $request, User $user)
    {
        // Check admin access
        $accessCheck = $this->checkAdminAccess($request);
        if ($accessCheck) return $accessCheck;

        if (!$user->isCommercial()) {
            return response()->json([
                'message' => 'Cet utilisateur n\'est pas un commercial'
            ], 400);
        }

        $currentYear = date('Y');
        $stats = [];

        // Stats par mois pour l'année courante
        for ($month = 1; $month <= 12; $month++) {
            $startDate = date('Y-m-01', strtotime("{$currentYear}-{$month}-01"));
            $endDate = date('Y-m-t', strtotime($startDate));
            
            $applications = $user->countApplicationsForPeriod($startDate . ' 00:00:00', $endDate . ' 23:59:59');
            $objective = $user->objectives()
                ->active()
                ->forYear($currentYear)
                ->forMonth($month)
                ->first();

            $stats['months'][] = [
                'month' => $month,
                'month_name' => date('F', strtotime("{$currentYear}-{$month}-01")),
                'applications' => $applications,
                'objective' => $objective ? $objective->monthly_target : 0,
                'progress_percentage' => $objective && $objective->monthly_target > 0 
                    ? min(100, round(($applications / $objective->monthly_target) * 100, 2))
                    : 0
            ];
        }

        // Stats globales pour l'année
        $yearStart = date('Y-01-01 00:00:00');
        $yearEnd = date('Y-12-31 23:59:59');
        $totalApplications = $user->countApplicationsForPeriod($yearStart, $yearEnd);
        
        $yearlyObjective = $user->objectives()
            ->active()
            ->forYear($currentYear)
            ->whereNull('target_month') // Objectif annuel
            ->first();

        $stats['yearly'] = [
            'applications' => $totalApplications,
            'objective' => $yearlyObjective ? $yearlyObjective->yearly_target : 0,
            'progress_percentage' => $yearlyObjective && $yearlyObjective->yearly_target > 0
                ? min(100, round(($totalApplications / $yearlyObjective->yearly_target) * 100, 2))
                : 0
        ];

        return response()->json([
            'user' => $user->load('roles'),
            'stats' => $stats,
            'year' => $currentYear
        ]);
    }

    /**
     * Changer le mot de passe (pour la première connexion)
     */
    public function changePassword(Request $request)
    {
        $request->validate([
            'current_password' => 'required|string',
            'new_password' => 'required|string|min:6|confirmed',
        ]);

        $user = $request->user();

        if (!Hash::check($request->current_password, $user->password)) {
            throw ValidationException::withMessages([
                'current_password' => ['Le mot de passe actuel est incorrect.'],
            ]);
        }

        $user->password = Hash::make($request->new_password);
        $user->markPasswordAsChanged();

        return response()->json([
            'message' => 'Mot de passe changé avec succès',
            'data' => [
                'must_change_password' => false
            ]
        ]);
    }
}