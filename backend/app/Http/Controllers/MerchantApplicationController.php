<?php

namespace App\Http\Controllers;

use App\Models\MerchantApplication;
use App\Http\Requests\StoreMerchantApplicationRequest;
use App\Http\Resources\MerchantApplicationResource;
use App\Services\DocumentStorageService;
use App\Services\NotificationService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;

/**
 * Contrôleur pour la gestion des candidatures marchands Moov Money
 */
class MerchantApplicationController extends Controller
{
    public function __construct(
        private DocumentStorageService $documentStorage,
        private NotificationService $notificationService
    ) {}
    
    /**
     * Liste toutes les candidatures avec filtres et pagination
     */
    public function index(Request $request): JsonResponse
    {
        $query = MerchantApplication::with('documents');
        
        // Filtre par utilisateur pour les commerciaux
        $user = $request->user();
        if ($user && $user->roles->contains('name', 'Commercial')) {
            // Les commerciaux ne voient que leurs propres candidatures
            $query->where('user_id', $user->id);
        }
        // Les admins voient toutes les candidatures (pas de filtre supplémentaire)
        
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }
        
        if ($request->filled('business_type')) {
            $query->where('business_type', $request->business_type);
        }
        
        // NOUVEAU: Filtre par type d'utilisation
        if ($request->filled('usage_type')) {
            $query->where('usage_type', $request->usage_type);
        }
        
        // NOUVEAU: Filtre par genre
        if ($request->filled('gender')) {
            $query->where('gender', $request->gender);
        }
        
        // NOUVEAU: Filtre par nationalité
        if ($request->filled('nationality')) {
            $query->where('nationality', $request->nationality);
        }
        
        if ($request->filled('submitted_after')) {
            $query->where('submitted_at', '>=', $request->submitted_after);
        }
        
        if ($request->filled('submitted_before')) {
            $query->where('submitted_at', '<=', $request->submitted_before);
        }
        
        // Recherche full-text (mise à jour pour inclure first_name, last_name, merchant_phone)
        if ($request->filled('search')) {
            $searchTerm = $request->search;
            
            if (config('database.default') === 'mysql') {
                $query->whereFullText(
                    ['full_name', 'business_name', 'reference_number', 'phone'],
                    $searchTerm
                );
            } else {
                $query->where(function ($q) use ($searchTerm) {
                    $q->where('full_name', 'LIKE', "%{$searchTerm}%")
                      ->orWhere('first_name', 'LIKE', "%{$searchTerm}%")
                      ->orWhere('last_name', 'LIKE', "%{$searchTerm}%")
                      ->orWhere('business_name', 'LIKE', "%{$searchTerm}%")
                      ->orWhere('reference_number', 'LIKE', "%{$searchTerm}%")
                      ->orWhere('phone', 'LIKE', "%{$searchTerm}%")
                      ->orWhere('merchant_phone', 'LIKE', "%{$searchTerm}%")
                      ->orWhere('business_phone', 'LIKE', "%{$searchTerm}%");
                });
            }
        }
        
        $sortField = $request->get('sort_by', 'created_at');
        $sortDirection = $request->get('sort_direction', 'desc');
        
        $allowedSortFields = [
            'created_at', 'updated_at', 'submitted_at', 'full_name', 
            'first_name', 'last_name', 'business_name', 'status', 'reference_number'
        ];
        
        if (in_array($sortField, $allowedSortFields)) {
            $query->orderBy($sortField, $sortDirection === 'asc' ? 'asc' : 'desc');
        } else {
            $query->orderBy('created_at', 'desc');
        }
        
        $perPage = min($request->get('per_page', 15), 100);
        $applications = $query->paginate($perPage);
        
        return response()->json([
            'success' => true,
            'data' => MerchantApplicationResource::collection($applications->items()),
            'meta' => [
                'current_page' => $applications->currentPage(),
                'last_page' => $applications->lastPage(),
                'per_page' => $applications->perPage(),
                'total' => $applications->total(),
                'from' => $applications->firstItem(),
                'to' => $applications->lastItem(),
            ],
            'links' => [
                'first' => $applications->url(1),
                'last' => $applications->url($applications->lastPage()),
                'prev' => $applications->previousPageUrl(),
                'next' => $applications->nextPageUrl(),
            ]
        ]);
    }
    
    /**
     * Enregistre une nouvelle candidature marchand
     */
    public function store(StoreMerchantApplicationRequest $request): JsonResponse
    {
        return DB::transaction(function () use ($request) {
            try {
                $applicationData = $request->validated();
                
                // Attacher l'utilisateur connecté s'il y en a un
                if ($request->user()) {
                    $applicationData['user_id'] = $request->user()->id;
                }
                
                // Remove document files from application data (they're handled separately)
                $documentFields = ['id_card', 'anid_card', 'residence_card', 'residence_proof', 'business_document', 'cfe_document', 'nif_document'];
                foreach ($documentFields as $field) {
                    unset($applicationData[$field]);
                }
                
                // Legacy documents array handling
                $documents = $applicationData['documents'] ?? [];
                unset($applicationData['documents']);
                
                $application = MerchantApplication::create($applicationData);
                
                // Handle individual document fields - with debug logging
                $documentFields = [
                    'id_card' => 'id_card',
                    'anid_card' => 'anid_card',
                    'residence_card' => 'residence_card',
                    'residence_proof' => 'other',  // Map to 'other' as it's not in enum
                    'business_document' => 'business_license',  // Use correct enum value
                    'cfe_document' => 'cfe_card',  // Use correct enum value 
                    'nif_document' => 'nif_document',
                ];
                
                // Debug: Log all request files
                Log::info('Checking for document files', [
                    'application_id' => $application->id,
                    'all_files' => array_keys($request->allFiles()),
                    'has_files_check' => array_map(function($field) use ($request) {
                        return [$field => $request->hasFile($field)];
                    }, array_keys($documentFields))
                ]);
                
                foreach ($documentFields as $fieldName => $documentType) {
                    if ($request->hasFile($fieldName)) {
                        Log::info('Processing document file', [
                            'field_name' => $fieldName,
                            'document_type' => $documentType,
                            'file_size' => $request->file($fieldName)->getSize(),
                            'file_name' => $request->file($fieldName)->getClientOriginalName()
                        ]);
                        $this->storeDocument($application, $documentType, $request->file($fieldName), $request->ip());
                    }
                }
                
                $application->load('documents');
                
                try {
                    $this->notificationService->sendConfirmations($application);
                } catch (\Exception $e) {
                    Log::warning('Notifications non envoyées', [
                        'application_id' => $application->id,
                        'error' => $e->getMessage()
                    ]);
                }
                
                Log::info('Nouvelle candidature créée', [
                    'application_id' => $application->id,
                    'reference' => $application->reference_number,
                    'first_name' => $application->first_name,
                    'last_name' => $application->last_name,
                    'business_name' => $application->business_name,
                    'phone' => $application->phone,
                    'merchant_phone' => $application->merchant_phone,
                    'usage_type' => $application->usage_type,
                    'ip' => $request->ip(),
                    'user_agent' => $request->userAgent()
                ]);
                
                return response()->json([
                    'success' => true,
                    'message' => 'Candidature soumise avec succès',
                    'data' => new MerchantApplicationResource($application),
                    'reference_number' => $application->reference_number
                ], 201);
                
            } catch (\Exception $e) {
                Log::error('Erreur création candidature', [
                    'error' => $e->getMessage(),
                    'trace' => $e->getTraceAsString(),
                    'request_data' => $request->except(['documents', 'signature'])
                ]);
                
                throw $e;
            }
        });
    }
    
    public function show(Request $request, MerchantApplication $merchantApplication): JsonResponse
    {
        // Vérifier les permissions pour les commerciaux
        $user = $request->user();
        if ($user && $user->roles->contains('name', 'Commercial')) {
            // Les commerciaux ne peuvent voir que leurs propres candidatures
            if ($merchantApplication->user_id !== $user->id) {
                return response()->json([
                    'success' => false,
                    'message' => 'Accès non autorisé à cette candidature'
                ], 403);
            }
        }
        
        $merchantApplication->load(['documents', 'reviewer', 'user.roles']);
        
        return response()->json([
            'success' => true,
            'data' => new MerchantApplicationResource($merchantApplication)
        ]);
    }
    
    public function showByReference(string $reference): JsonResponse
    {
        $application = MerchantApplication::with(['documents', 'reviewer'])
            ->where('reference_number', $reference)
            ->firstOrFail();
        
        return response()->json([
            'success' => true,
            'data' => new MerchantApplicationResource($application)
        ]);
    }
    
    /**
     * Mise à jour administrative (notes seulement)
     */
    public function update(Request $request, MerchantApplication $merchantApplication): JsonResponse
    {
        $validated = $request->validate([
            'admin_notes' => 'sometimes|string|max:1000|nullable',
        ]);
        
        $merchantApplication->update($validated);
        $merchantApplication->load(['documents', 'reviewer']);
        
        Log::info('Candidature mise à jour', [
            'application_id' => $merchantApplication->id,
            'updated_by' => auth()->id(),
            'changes' => $merchantApplication->getChanges()
        ]);
        
        return response()->json([
            'success' => true,
            'message' => 'Candidature mise à jour avec succès',
            'data' => new MerchantApplicationResource($merchantApplication)
        ]);
    }

    /**
     * Mise à jour complète d'une candidature (pour l'édition par commercial/admin)
     */
    public function fullUpdate(Request $request, MerchantApplication $merchantApplication): JsonResponse
    {
        Log::debug('Entering fullUpdate', [
            'url'   => $request->fullUrl(),
            'method'=> $request->method(),
            'params'=> $request->route()?->parameters(),
            'user'  => optional($request->user())->id,
        ]);
        try {
            // Diagnostic: log contexte d'appel
            $docFiles = $request->hasFile('documents') ? $request->file('documents') : [];
            $docKeys = is_array($docFiles) ? array_keys($docFiles) : [];
            // Vérifier l'authentification en premier
            $user = $request->user();
            Log::info('fullUpdate reçu', [
                'application_id' => $merchantApplication->id,
                'user_id' => $user?->id,
                'is_authenticated' => !!$user,
                'content_type' => $request->header('Content-Type'),
                'has_documents' => $request->hasFile('documents'),
                'document_keys' => $docKeys,
            ]);
            
            if (!$user) {
                Log::warning('Tentative fullUpdate sans authentification', [
                    'application_id' => $merchantApplication->id,
                    'ip' => $request->ip(),
                    'user_agent' => $request->userAgent()
                ]);
                
                return response()->json([
                    'success' => false,
                    'message' => 'Vous devez être connecté pour modifier une candidature'
                ], 401);
            }

            // Les commerciaux ne peuvent modifier que leurs propres candidatures
            if ($user->roles->contains('slug', 'commercial') && $merchantApplication->user_id !== $user->id) {
                return response()->json([
                    'success' => false,
                    'message' => 'Vous ne pouvez modifier que vos propres candidatures'
                ], 403);
            }

            // Validation des données (réutiliser les règles de création en version statique)
            $rules = StoreMerchantApplicationRequest::rulesFor($merchantApplication->id);

            // Pas de traitement spécial nécessaire pour business_phone (valeur simple)
            $data = $request->all();
            
            // Debug: Log des données reçues
            Log::info('Données reçues dans fullUpdate', [
                'application_id' => $merchantApplication->id,
                'request_method' => $request->method(),
                'content_type' => $request->header('Content-Type'),
                'data_keys' => array_keys($data),
                'data_count' => count($data),
                'first_name' => $data['first_name'] ?? 'NOT_SET',
                'last_name' => $data['last_name'] ?? 'NOT_SET',
            ]);

            $validator = Validator::make($data, $rules);
            if ($validator->fails()) {
                Log::warning('Validation échouée lors de fullUpdate', [
                    'application_id' => $merchantApplication->id,
                    'user_id' => $user->id,
                    'validation_errors' => $validator->errors()->toArray(),
                    'input_data_keys' => array_keys($data)
                ]);
                
                return response()->json([
                    'success' => false,
                    'message' => 'Erreur de validation des données',
                    'errors' => $validator->errors()
                ], 422);
            }
            $validated = $validator->validated();
            
            // Extraire et retirer les documents des champs à mettre à jour
            $incomingDocuments = $validated['documents'] ?? null;
            unset($validated['documents']);
            
            // Supprimer les champs système qui ne doivent pas être modifiés
            $protectedFields = [
                'user_id', 'reference_number', 'submitted_at', 
                'created_at', 'updated_at', 'status', 'reviewed_at', 'reviewed_by'
            ];
            
            foreach ($protectedFields as $field) {
                unset($validated[$field]);
            }

            // S'assurer que le service de stockage est disponible
            $this->documentStorage ??= app(\App\Services\DocumentStorageService::class);

            // Mise à jour en transaction
            DB::transaction(function () use ($merchantApplication, $validated, $request) {
                $merchantApplication->update($validated);

                // Gestion des nouveaux documents si fournis - handle individual document fields
                $documentFields = [
                    'id_card' => 'id_card',
                    'anid_card' => 'anid_card',
                    'residence_card' => 'residence_card',
                    'residence_proof' => 'other',  // Map to 'other' as it's not in enum
                    'business_document' => 'business_license',  // Use correct enum value
                    'cfe_document' => 'cfe_card',  // Use correct enum value 
                    'nif_document' => 'nif_document',
                ];
                
                foreach ($documentFields as $fieldName => $documentType) {
                    if ($request->hasFile($fieldName)) {
                        // Supprimer l'ancien document de ce type s'il existe
                        $existingDocument = $merchantApplication->documents()
                            ->where('document_type', $documentType)
                            ->first();
                        
                        if ($existingDocument) {
                            // Supprime le fichier existant via le service de stockage
                            $this->documentStorage->delete($existingDocument->file_path);
                            $existingDocument->delete();
                        }
                        
                        // Ajouter le nouveau document
                        $this->storeDocument($merchantApplication, $documentType, $request->file($fieldName), $request->ip());
                    }
                }
            });

            $merchantApplication->load(['documents', 'reviewer', 'user.roles']);
            
            Log::info('Candidature mise à jour complètement', [
                'application_id' => $merchantApplication->id,
                'updated_by' => $user->id,
                'updated_fields' => array_keys($validated),
                'has_new_documents' => $request->hasFile('documents')
            ]);
            
            return response()->json([
                'success' => true,
                'message' => 'Candidature mise à jour avec succès',
                'data' => new MerchantApplicationResource($merchantApplication)
            ]);
            
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erreur de validation des données',
                'errors' => $e->errors()
            ], 422);
            
        } catch (\Exception $e) {
            // Attention: ne pas accéder à $user->id directement (peut être null/non défini)
            Log::error('Erreur mise à jour complète candidature', [
                'application_id' => $merchantApplication->id ?? null,
                'user_id' => request()->user()?->id,
                'error' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine()
            ]);
            
            $payload = [
                'success' => false,
                'message' => 'Erreur lors de la mise à jour de la candidature'
            ];
            if (config('app.debug')) {
                $payload['error'] = $e->getMessage();
            }
            return response()->json($payload, 500);
        }
    }

    /**
     * Adapter les règles de validation pour l'édition
     */
    // Suppression de adaptValidationRulesForUpdate: remplacée par rulesFor() dans StoreMerchantApplicationRequest
    
    public function updateStatus(Request $request, MerchantApplication $merchantApplication): JsonResponse
    {
        $validated = $request->validate([
            'status' => 'required|in:pending,under_review,approved,rejected,needs_info,archived',
            'admin_notes' => 'sometimes|string|max:1000|nullable',
        ]);
        
        $oldStatus = $merchantApplication->status;
        
        $merchantApplication->updateStatus(
            $validated['status'],
            $validated['admin_notes'] ?? null,
            auth()->id()
        );
        
        try {
            $this->notificationService->sendStatusUpdate($merchantApplication);
        } catch (\Exception $e) {
            Log::warning('Notification statut non envoyée', [
                'application_id' => $merchantApplication->id,
                'error' => $e->getMessage()
            ]);
        }
        
        Log::info('Statut candidature modifié', [
            'application_id' => $merchantApplication->id,
            'reference' => $merchantApplication->reference_number,
            'old_status' => $oldStatus,
            'new_status' => $validated['status'],
            'updated_by' => auth()->id(),
            'notes' => $validated['admin_notes'] ?? null
        ]);
        
        $merchantApplication->load(['documents', 'reviewer']);
        
        return response()->json([
            'success' => true,
            'message' => 'Statut mis à jour avec succès',
            'data' => new MerchantApplicationResource($merchantApplication)
        ]);
    }
    
    /**
     * Statistiques globales (mise à jour pour inclure nouveaux champs)
     */
    public function statistics(Request $request): JsonResponse
    {
        $period = $request->get('period', 'all');
        
        $query = MerchantApplication::query();
        
        switch ($period) {
            case 'today':
                $query->whereDate('created_at', today());
                break;
            case 'week':
                $query->whereBetween('created_at', [now()->startOfWeek(), now()->endOfWeek()]);
                break;
            case 'month':
                $query->whereMonth('created_at', now()->month)
                      ->whereYear('created_at', now()->year);
                break;
            case 'year':
                $query->whereYear('created_at', now()->year);
                break;
        }
        
        $stats = [
            'total' => $query->count(),
            'by_status' => $query->clone()
                ->select('status', DB::raw('count(*) as count'))
                ->groupBy('status')
                ->pluck('count', 'status'),
            
            'by_business_type' => $query->clone()
                ->select('business_type', DB::raw('count(*) as count'))
                ->groupBy('business_type')
                ->pluck('count', 'business_type'),
            
            // NOUVEAU: Statistiques par type d'utilisation
            'by_usage_type' => $query->clone()
                ->select('usage_type', DB::raw('count(*) as count'))
                ->groupBy('usage_type')
                ->pluck('count', 'usage_type'),
            
            // NOUVEAU: Statistiques par genre
            'by_gender' => $query->clone()
                ->select('gender', DB::raw('count(*) as count'))
                ->groupBy('gender')
                ->pluck('count', 'gender'),
            
            // NOUVEAU: Statistiques par type de pièce d'identité
            'by_id_type' => $query->clone()
                ->select('id_type', DB::raw('count(*) as count'))
                ->groupBy('id_type')
                ->pluck('count', 'id_type'),
            
            'with_documents' => MerchantApplication::has('documents')->count(),
            'foreign_applicants' => $query->clone()->where('is_foreigner', true)->count(),
            'with_cfe' => $query->clone()->where('has_cfe', true)->count(),
            'with_nif' => $query->clone()->where('has_nif', true)->count(),
            
            'avg_processing_time_hours' => MerchantApplication::whereNotNull('reviewed_at')
                ->selectRaw('AVG(TIMESTAMPDIFF(HOUR, submitted_at, reviewed_at)) as avg_time')
                ->value('avg_time'),
            
            'recent_activity' => [
                'last_24h' => MerchantApplication::where('created_at', '>=', now()->subDay())->count(),
                'last_7d' => MerchantApplication::where('created_at', '>=', now()->subWeek())->count(),
                'last_30d' => MerchantApplication::where('created_at', '>=', now()->subMonth())->count(),
            ],
            
            'period' => $period,
            'generated_at' => now()->toISOString()
        ];
        
        return response()->json([
            'success' => true,
            'data' => $stats
        ]);
    }
    
    private function storeDocument(
        MerchantApplication $application, 
        string $type, 
        $file, 
        string $ip
    ): void {
        try {
            $stored = $this->documentStorage->store($file, $type);
            
            $application->documents()->create([
                'document_type' => $type,
                'original_name' => $file->getClientOriginalName(),
                'file_name' => $stored['filename'],
                'file_path' => $stored['path'],
                'mime_type' => $file->getMimeType(),
                'file_size' => $file->getSize(),
                'upload_ip' => $ip,
                'hash_sha256' => $stored['hash'],
            ]);
            
            Log::info('Document stocké', [
                'application_id' => $application->id,
                'document_type' => $type,
                'file_size' => $file->getSize(),
                'file_name' => $stored['filename']
            ]);
            
        } catch (\Exception $e) {
            Log::error('Erreur stockage document', [
                'application_id' => $application->id,
                'document_type' => $type,
                'error' => $e->getMessage()
            ]);
            
            throw new \Exception("Erreur lors du stockage du document {$type}: " . $e->getMessage());
        }
    }
    
    public function destroy(MerchantApplication $merchantApplication): JsonResponse
    {
        try {
            $merchantApplication->delete();
            
            Log::warning('Candidature supprimée', [
                'application_id' => $merchantApplication->id,
                'reference' => $merchantApplication->reference_number,
                'deleted_by' => auth()->id()
            ]);
            
            return response()->json([
                'success' => true,
                'message' => 'Candidature archivée avec succès'
            ]);
            
        } catch (\Exception $e) {
            Log::error('Erreur suppression candidature', [
                'application_id' => $merchantApplication->id,
                'error' => $e->getMessage()
            ]);
            
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la suppression'
            ], 500);
        }
    }
    
    public function restore(int $id): JsonResponse
    {
        $application = MerchantApplication::withTrashed()->findOrFail($id);
        
        if (!$application->trashed()) {
            return response()->json([
                'success' => false,
                'message' => 'Cette candidature n\'est pas archivée'
            ], 400);
        }
        
        $application->restore();
        
        Log::info('Candidature restaurée', [
            'application_id' => $application->id,
            'restored_by' => auth()->id()
        ]);
        
        return response()->json([
            'success' => true,
            'message' => 'Candidature restaurée avec succès',
            'data' => new MerchantApplicationResource($application)
        ]);
    }

    /**
     * Valider une candidature (Admin seulement)
     */
    public function approve(MerchantApplication $merchantApplication): JsonResponse
    {
        // Vérifier les permissions
        if (!auth()->user()->hasRole('admin')) {
            return response()->json([
                'success' => false,
                'message' => 'Action non autorisée'
            ], 403);
        }

        if ($merchantApplication->status === 'approved') {
            return response()->json([
                'success' => false,
                'message' => 'Cette candidature est déjà approuvée'
            ], 400);
        }

        try {
            DB::beginTransaction();

            $merchantApplication->update([
                'status' => 'approved',
                'reviewed_at' => now(),
                'reviewed_by' => auth()->id()
            ]);

            // Envoyer notification
            $this->notificationService->sendApprovalNotification($merchantApplication);

            DB::commit();

            Log::info('Candidature approuvée', [
                'application_id' => $merchantApplication->id,
                'approved_by' => auth()->id()
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Candidature approuvée avec succès',
                'data' => new MerchantApplicationResource($merchantApplication->fresh())
            ]);

        } catch (\Exception $e) {
            DB::rollback();
            
            Log::error('Erreur approbation candidature', [
                'application_id' => $merchantApplication->id,
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de l\'approbation'
            ], 500);
        }
    }

    /**
     * Rejeter une candidature (Admin seulement)
     */
    public function reject(Request $request, MerchantApplication $merchantApplication): JsonResponse
    {
        // Vérifier les permissions
        if (!auth()->user()->hasRole('admin')) {
            return response()->json([
                'success' => false,
                'message' => 'Action non autorisée'
            ], 403);
        }

        if ($merchantApplication->status === 'rejected') {
            return response()->json([
                'success' => false,
                'message' => 'Cette candidature est déjà rejetée'
            ], 400);
        }

        $request->validate([
            'reason' => 'nullable|string|max:1000'
        ]);

        try {
            DB::beginTransaction();

            $merchantApplication->update([
                'status' => 'rejected',
                'rejected_reason' => $request->reason,
                'reviewed_at' => now(),
                'reviewed_by' => auth()->id()
            ]);

            // Envoyer notification
            $this->notificationService->sendRejectionNotification($merchantApplication, $request->reason);

            DB::commit();

            Log::info('Candidature rejetée', [
                'application_id' => $merchantApplication->id,
                'rejected_by' => auth()->id(),
                'reason' => $request->reason
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Candidature rejetée avec succès',
                'data' => new MerchantApplicationResource($merchantApplication->fresh())
            ]);

        } catch (\Exception $e) {
            DB::rollback();
            
            Log::error('Erreur rejet candidature', [
                'application_id' => $merchantApplication->id,
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Erreur lors du rejet'
            ], 500);
        }
    }

    /**
     * Supprimer définitivement une candidature (Admin seulement)
     */
    public function forceDestroy(MerchantApplication $merchantApplication): JsonResponse
    {
        // Vérifier les permissions
        if (!auth()->user()->hasRole('admin')) {
            return response()->json([
                'success' => false,
                'message' => 'Action non autorisée'
            ], 403);
        }

        try {
            DB::beginTransaction();

            // Supprimer les documents associés
            foreach ($merchantApplication->documents as $document) {
                $this->documentStorage->deleteFile($document->file_path);
                $document->delete();
            }

            // Supprimer la candidature
            $merchantApplication->forceDelete();

            DB::commit();

            Log::info('Candidature supprimée définitivement', [
                'application_id' => $merchantApplication->id,
                'deleted_by' => auth()->id()
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Candidature supprimée définitivement avec succès'
            ]);

        } catch (\Exception $e) {
            DB::rollback();
            
            Log::error('Erreur suppression définitive candidature', [
                'application_id' => $merchantApplication->id,
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la suppression définitive'
            ], 500);
        }
    }
}