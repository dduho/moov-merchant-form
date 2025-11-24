<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\UserObjective;
use App\Models\Role;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

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

        $users = $query->paginate($request->get('per_page', 10));

        // Ajouter les statistiques pour chaque commercial et personnel
        $users->getCollection()->transform(function ($user) {
            try {
                // Les commerciaux ont des objectifs, le personnel n'en a pas
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

        // Build query by explicitly joining the pivot `role_user` to avoid
        // potential pivot table name mismatches in different environments.
        $query = User::select(['users.id', 'users.first_name', 'users.last_name'])
            ->join('role_user', 'users.id', '=', 'role_user.user_id')
            ->join('roles', 'role_user.role_id', '=', 'roles.id')
            ->where('roles.slug', 'commercial')
            ->where('users.is_active', true)
            ->where('users.is_blocked', false)
            ->distinct();

        // Optional search parameter (q) for typeahead
        if ($request->filled('q')) {
            $q = $request->q;
            $query->where(function($sub) use ($q) {
                $sub->where('first_name', 'like', "%{$q}%")
                    ->orWhere('last_name', 'like', "%{$q}%");
            });
        }

        // Limit results to prevent huge payloads
        $limit = intval($request->get('limit', 50));
        $commercials = $query->limit($limit)->get();

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

        try {
            $request->validate([
                'new_password' => 'nullable|string|min:6|max:255'
            ]);

            $newPassword = $request->get('new_password', 'password');
            $user->resetPassword($newPassword);

            return response()->json([
                'message' => 'Mot de passe réinitialisé avec succès',
                'success' => true,
                'data' => [
                    'user_id' => $user->id,
                    'user_name' => $user->first_name . ' ' . $user->last_name,
                    'new_password' => $newPassword,
                    'must_change_password' => true
                ]
            ]);
        } catch (ValidationException $e) {
            return response()->json([
                'message' => 'Erreur de validation',
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            \Log::error('Error resetting password', [
                'user_id' => $user->id,
                'error' => $e->getMessage()
            ]);
            
            return response()->json([
                'message' => 'Erreur lors de la réinitialisation du mot de passe',
                'errors' => ['server' => [$e->getMessage()]]
            ], 500);
        }
    }

    /**
     * Bloquer/débloquer un utilisateur
     */
    public function toggleBlock(Request $request, User $user)
    {
        // Check admin access
        $accessCheck = $this->checkAdminAccess($request);
        if ($accessCheck) return $accessCheck;

        try {
            // Empêcher de bloquer son propre compte
            if ($user->id === $request->user()->id) {
                return response()->json([
                    'message' => 'Action non autorisée',
                    'errors' => ['permission' => ['Vous ne pouvez pas bloquer votre propre compte']]
                ], 403);
            }

            $user->is_blocked = !$user->is_blocked;
            $user->save();

            return response()->json([
                'message' => $user->is_blocked ? 'Utilisateur bloqué avec succès' : 'Utilisateur débloqué avec succès',
                'success' => true,
                'data' => [
                    'user_id' => $user->id,
                    'user_name' => $user->first_name . ' ' . $user->last_name,
                    'is_blocked' => $user->is_blocked
                ]
            ]);
        } catch (\Exception $e) {
            \Log::error('Error toggling user block status', [
                'user_id' => $user->id,
                'error' => $e->getMessage()
            ]);
            
            return response()->json([
                'message' => 'Erreur lors du changement de statut',
                'errors' => ['server' => [$e->getMessage()]]
            ], 500);
        }
    }

    /**
     * Activer/désactiver un utilisateur
     */
    public function toggleActive(Request $request, User $user)
    {
        // Check admin access
        $accessCheck = $this->checkAdminAccess($request);
        if ($accessCheck) return $accessCheck;

        try {
            // Empêcher de désactiver son propre compte
            if ($user->id === $request->user()->id) {
                return response()->json([
                    'message' => 'Action non autorisée',
                    'errors' => ['permission' => ['Vous ne pouvez pas désactiver votre propre compte']]
                ], 403);
            }

            $user->is_active = !$user->is_active;
            $user->save();

            return response()->json([
                'message' => $user->is_active ? 'Utilisateur activé avec succès' : 'Utilisateur désactivé avec succès',
                'success' => true,
                'data' => [
                    'user_id' => $user->id,
                    'user_name' => $user->first_name . ' ' . $user->last_name,
                    'is_active' => $user->is_active
                ]
            ]);
        } catch (\Exception $e) {
            \Log::error('Error toggling user active status', [
                'user_id' => $user->id,
                'error' => $e->getMessage()
            ]);
            
            return response()->json([
                'message' => 'Erreur lors du changement de statut',
                'errors' => ['server' => [$e->getMessage()]]
            ], 500);
        }
    }

    /**
     * Mettre à jour un utilisateur
     */
    public function update(Request $request, User $user)
    {
        // Check admin access
        $accessCheck = $this->checkAdminAccess($request);
        if ($accessCheck) return $accessCheck;

        try {
            // Validation des données
            $validated = $request->validate([
                'first_name' => 'required|string|max:255',
                'last_name' => 'required|string|max:255',
                'email' => 'nullable|email|unique:users,email,' . $user->id,
                'phone' => 'required|string|max:20|unique:users,phone,' . $user->id,
                'username' => 'required|string|max:255|unique:users,username,' . $user->id,
                'role_slug' => 'required|exists:roles,slug'
            ]);

            // Empêcher de modifier son propre rôle
            if ($user->id === $request->user()->id && $user->roles->first()->slug !== $validated['role_slug']) {
                return response()->json([
                    'message' => 'Action non autorisée',
                    'errors' => ['permission' => ['Vous ne pouvez pas modifier votre propre rôle']]
                ], 403);
            }

            // Mettre à jour les informations de l'utilisateur
            $user->first_name = $validated['first_name'];
            $user->last_name = $validated['last_name'];
            $user->email = $validated['email'];
            $user->phone = $validated['phone'];
            $user->username = $validated['username'];
            $user->save();

            // Mettre à jour le rôle si nécessaire
            if ($user->roles->first()->slug !== $validated['role_slug']) {
                $user->roles()->sync([\App\Models\Role::where('slug', $validated['role_slug'])->first()->id]);
            }

            return response()->json([
                'message' => 'Utilisateur mis à jour avec succès',
                'success' => true,
                'data' => $user->load('roles')
            ]);
        } catch (ValidationException $e) {
            return response()->json([
                'message' => 'Erreur de validation',
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            \Log::error('Error updating user', [
                'user_id' => $user->id,
                'error' => $e->getMessage()
            ]);
            
            return response()->json([
                'message' => 'Erreur lors de la mise à jour de l\'utilisateur',
                'errors' => ['server' => [$e->getMessage()]]
            ], 500);
        }
    }

    /**
    {
        // Check admin access
        $accessCheck = $this->checkAdminAccess($request);
        if ($accessCheck) return $accessCheck;

        try {
            // Empêcher de désactiver son propre compte
            if ($user->id === $request->user()->id) {
                return response()->json([
                    'message' => 'Action non autorisée',
                    'errors' => ['permission' => ['Vous ne pouvez pas désactiver votre propre compte']]
                ], 403);
            }

            $user->is_active = !$user->is_active;
            $user->save();

            return response()->json([
                'message' => $user->is_active ? 'Utilisateur activé avec succès' : 'Utilisateur désactivé avec succès',
                'success' => true,
                'data' => [
                    'user_id' => $user->id,
                    'user_name' => $user->first_name . ' ' . $user->last_name,
                    'is_active' => $user->is_active
                ]
            ]);
        } catch (\Exception $e) {
            \Log::error('Error toggling user active status', [
                'user_id' => $user->id,
                'error' => $e->getMessage()
            ]);
            
            return response()->json([
                'message' => 'Erreur lors du changement de statut',
                'errors' => ['server' => [$e->getMessage()]]
            ], 500);
        }
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

        // Applications récentes (10 dernières)
        $stats['recent_applications'] = $user->merchantApplications()
            ->orderBy('created_at', 'desc')
            ->take(10)
            ->get(['id', 'business_name', 'status', 'created_at'])
            ->map(function($app) {
                return [
                    'id' => $app->id,
                    'business_name' => $app->business_name,
                    'status' => $app->status,
                    'created_at' => $app->created_at->toISOString()
                ];
            });

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

    /**
     * Importer des utilisateurs en masse depuis un fichier Excel/CSV
     */
    public function bulkImport(Request $request)
    {
        // Check admin access
        $accessCheck = $this->checkAdminAccess($request);
        if ($accessCheck) return $accessCheck;

        // Initialiser les variables
        $users = [];
        $errors = [];
        $successCount = 0;
        $errorCount = 0;

        try {
            $request->validate([
                'file' => 'required|file|mimes:xlsx,xls,csv|max:2048'
            ]);

            $file = $request->file('file');
            $extension = $file->getClientOriginalExtension();
            
            // Vérifier et créer les rôles si nécessaire
            $this->ensureRolesExist();

            // Parse selon le type de fichier
            if ($extension === 'csv') {
                $data = $this->parseCSV($file);
            } else {
                // Pour Excel, on va utiliser une approche simple avec fgetcsv après conversion
                return response()->json([
                    'message' => 'Format Excel non supporté pour le moment. Veuillez utiliser un fichier CSV.',
                    'errors' => ['file' => ['Seuls les fichiers CSV sont supportés actuellement']]
                ], 422);
            }

            // Validation et création des utilisateurs
            DB::beginTransaction();

            foreach ($data as $index => $row) {
                $lineNumber = $index + 2; // +2 car ligne 1 = headers et index commence à 0

                try {
                    // Validation des données de la ligne
                    $validated = $this->validateUserRow($row, $lineNumber);
                    
                    if (!$validated['valid']) {
                        $errors[] = [
                            'line' => $lineNumber,
                            'data' => $row,
                            'errors' => $validated['errors']
                        ];
                        $errorCount++;
                        continue;
                    }

                    // Créer l'utilisateur
                    $user = User::create([
                        'first_name' => $validated['data']['first_name'],
                        'last_name' => $validated['data']['last_name'],
                        'email' => $validated['data']['email'] ?: null,
                        'phone' => $validated['data']['phone'],
                        'username' => $validated['data']['username'],
                        'password' => Hash::make('123456'), // Mot de passe par défaut
                        'is_active' => true,
                        'must_change_password' => true
                    ]);

                    // Assigner le rôle
                    $role = Role::where('slug', $validated['data']['role_slug'])->first();
                    if ($role) {
                        $user->roles()->attach($role->id);
                        
                        // Plus besoin de créer des objectifs particuliers automatiquement
                        // Les objectifs globaux s'appliquent à tous les commerciaux
                    } else {
                        // Si le rôle n'existe pas, ajouter une erreur
                        $errors[] = [
                            'line' => $lineNumber,
                            'data' => $row,
                            'errors' => ['role' => 'Le rôle spécifié n\'existe pas dans le système']
                        ];
                        $errorCount++;
                        // Supprimer l'utilisateur créé sans rôle
                        $user->delete();
                        continue;
                    }

                    $users[] = $user;
                    $successCount++;

                } catch (\Exception $e) {
                    $errors[] = [
                        'line' => $lineNumber,
                        'data' => $row,
                        'errors' => ['exception' => $e->getMessage()]
                    ];
                    $errorCount++;
                }
            }

            DB::commit();

            return response()->json([
                'message' => "Importation terminée : {$successCount} utilisateur(s) créé(s), {$errorCount} erreur(s)",
                'success' => true,
                'data' => [
                    'success_count' => $successCount,
                    'error_count' => $errorCount,
                    'users' => $users,
                    'errors' => $errors
                ]
            ], $errorCount > 0 ? 207 : 200); // 207 = Multi-Status

        } catch (ValidationException $e) {
            DB::rollBack();
            return response()->json([
                'message' => 'Erreur de validation du fichier',
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error in bulk import', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            return response()->json([
                'message' => 'Erreur lors de l\'importation',
                'errors' => ['server' => [$e->getMessage()]]
            ], 500);
        }
    }

    /**
     * Parser un fichier CSV
     */
    private function parseCSV($file)
    {
        $data = [];
        $handle = fopen($file->getRealPath(), 'r');
        
        // Détecter le séparateur automatiquement
        $firstLine = fgets($handle);
        rewind($handle);
        
        $delimiters = [',', ';', "\t", '|'];
        $delimiter = ',';
        $maxCount = 0;
        
        foreach ($delimiters as $del) {
            $count = substr_count($firstLine, $del);
            if ($count > $maxCount) {
                $maxCount = $count;
                $delimiter = $del;
            }
        }
        
        // Lire la première ligne (headers)
        $headers = fgetcsv($handle, 0, $delimiter);
        
        // Normaliser les headers (enlever BOM, espaces, etc.)
        $headers = array_map(function($header) {
            return trim(str_replace("\xEF\xBB\xBF", '', $header));
        }, $headers);

        // Lire les données
        while (($row = fgetcsv($handle, 0, $delimiter)) !== false) {
            if (count($row) === count($headers)) {
                $data[] = array_combine($headers, $row);
            }
        }
        
        fclose($handle);
        return $data;
    }

    /**
     * Valider une ligne de données utilisateur
     */
    private function validateUserRow($row, $lineNumber)
    {
        $errors = [];
        $data = [];

        // Prénom (requis)
        if (empty($row['prenom']) || empty(trim($row['prenom']))) {
            $errors[] = 'Le prénom est requis';
        } else {
            $data['first_name'] = trim($row['prenom']);
        }

        // Nom (requis)
        if (empty($row['nom']) || empty(trim($row['nom']))) {
            $errors[] = 'Le nom est requis';
        } else {
            $data['last_name'] = trim($row['nom']);
        }

        // Email (optionnel mais doit être valide et unique)
        $email = isset($row['email']) ? trim($row['email']) : '';
        if (!empty($email)) {
            if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                $errors[] = 'L\'email n\'est pas valide';
            } elseif (User::where('email', $email)->exists()) {
                $errors[] = 'Cet email existe déjà';
            } else {
                $data['email'] = $email;
            }
        } else {
            $data['email'] = null;
        }

        // Téléphone (requis et unique)
        if (empty($row['telephone']) || empty(trim($row['telephone']))) {
            $errors[] = 'Le téléphone est requis';
        } elseif (User::where('phone', trim($row['telephone']))->exists()) {
            $errors[] = 'Ce téléphone existe déjà';
        } else {
            $data['phone'] = trim($row['telephone']);
        }

        // Nom d'utilisateur (requis et unique)
        if (empty($row['username']) || empty(trim($row['username']))) {
            $errors[] = 'Le nom d\'utilisateur est requis';
        } elseif (strlen(trim($row['username'])) < 4) {
            $errors[] = 'Le nom d\'utilisateur doit faire au moins 4 caractères';
        } elseif (User::where('username', trim($row['username']))->exists()) {
            $errors[] = 'Ce nom d\'utilisateur existe déjà';
        } else {
            $data['username'] = trim($row['username']);
        }

        // Rôle (requis)
        $role = isset($row['role']) ? strtolower(trim($row['role'])) : '';
        if (empty($role)) {
            $errors[] = 'Le rôle est requis';
        } elseif (!in_array($role, ['admin', 'commercial', 'personnel'])) {
            $errors[] = 'Le rôle doit être: admin, commercial ou personnel';
        } else {
            $data['role_slug'] = $role;
        }

        return [
            'valid' => empty($errors),
            'errors' => $errors,
            'data' => $data
        ];
    }

    /**
     * Télécharger un template Excel pour l'importation
     */
    public function downloadTemplate()
    {
        $csv = "prenom,nom,email,telephone,username,role\n";
        $csv .= "Jean,Dupont,jean.dupont@example.com,90123456,jdupont,commercial\n";
        $csv .= "Marie,Martin,,91234567,mmartin,personnel\n";
        $csv .= "Admin,Test,admin@test.com,92345678,admintest,admin\n";

        return response($csv, 200)
            ->header('Content-Type', 'text/csv')
            ->header('Content-Disposition', 'attachment; filename="template_import_utilisateurs.csv"');
    }

    /**
     * S'assurer que les rôles nécessaires existent dans la base de données
     */
    private function ensureRolesExist()
    {
        $requiredRoles = [
            ['name' => 'Admin', 'slug' => 'admin'],
            ['name' => 'Commercial', 'slug' => 'commercial'],
            ['name' => 'Personnel', 'slug' => 'personnel'],
        ];

        foreach ($requiredRoles as $roleData) {
            Role::firstOrCreate(
                ['slug' => $roleData['slug']],
                ['name' => $roleData['name']]
            );
        }
    }
}