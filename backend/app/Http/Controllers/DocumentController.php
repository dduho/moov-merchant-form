<?php

namespace App\Http\Controllers;

use App\Models\ApplicationDocument;
use App\Models\MerchantApplication;
use App\Services\DocumentStorageService;
use App\Http\Resources\ApplicationDocumentResource;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;

/**
 * Contrôleur pour la gestion des documents des candidatures
 * 
 * Gère:
 * - Upload de documents
 * - Consultation/Téléchargement
 * - Vérification d'intégrité
 * - Validation administrative
 * - Suppression
 */
class DocumentController extends Controller
{
    /**
     * Injection du service de stockage
     */
    public function __construct(
        private DocumentStorageService $documentStorage
    ) {}
    
    /**
     * Upload d'un document individuel
     * 
     * @param Request $request
     * @return JsonResponse
     * 
     * Body params:
     * - file: fichier à uploader (jpg, jpeg, png, pdf, max 5MB)
     * - type: type de document (id_card, anid_card, residence_card, etc.)
     * - description: description optionnelle
     * - merchant_application_id: ID de la candidature (optionnel)
     */
    public function upload(Request $request): JsonResponse
    {
        // Validation des données
        $validator = Validator::make($request->all(), [
            'file' => [
                'required',
                'file',
                'mimes:jpg,jpeg,png,pdf',
                'max:5120' // 5MB en KB
            ],
            'type' => [
                'required',
                'in:id_card,anid_card,residence_card,cfe_card,nif_document,business_license,other'
            ],
            'description' => 'sometimes|string|max:500|nullable',
            'merchant_application_id' => 'sometimes|exists:merchant_applications,id'
        ]);
        
        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Erreur de validation',
                'errors' => $validator->errors()
            ], 422);
        }
        
        try {
            $file = $request->file('file');
            $type = $request->input('type');
            
            // Vérifications de sécurité supplémentaires
            $realMimeType = $file->getMimeType();
            $allowedMimes = ['image/jpeg', 'image/jpg', 'image/png', 'application/pdf'];
            
            if (!in_array($realMimeType, $allowedMimes)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Type de fichier non autorisé. Seuls JPG, PNG et PDF sont acceptés.'
                ], 422);
            }
            
            // Stocker le fichier via le service
            $stored = $this->documentStorage->store($file, $type);
            
            // Créer l'entrée en base de données
            $documentData = [
                'document_type' => $type,
                'original_name' => $file->getClientOriginalName(),
                'file_name' => $stored['filename'],
                'file_path' => $stored['path'],
                'mime_type' => $file->getMimeType(),
                'file_size' => $file->getSize(),
                'upload_ip' => $request->ip(),
                'hash_sha256' => $stored['hash'],
                'description' => $request->input('description'),
            ];
            
            // Si une candidature est spécifiée, l'associer
            if ($request->filled('merchant_application_id')) {
                $documentData['merchant_application_id'] = $request->input('merchant_application_id');
            }
            
            $document = ApplicationDocument::create($documentData);
            
            // Logger l'upload
            Log::info('Document uploadé avec succès', [
                'document_id' => $document->id,
                'type' => $type,
                'file_size' => $file->getSize(),
                'ip' => $request->ip(),
                'merchant_application_id' => $request->input('merchant_application_id')
            ]);
            
            return response()->json([
                'success' => true,
                'message' => 'Document uploadé avec succès',
                'data' => [
                    'id' => $document->id,
                    'uuid' => $document->uuid,
                    'document_type' => $document->document_type,
                    'original_name' => $document->original_name,
                    'file_size' => $document->formatted_size,
                    'mime_type' => $document->mime_type,
                    'url' => $document->url,
                    'hash' => $document->hash_sha256,
                    'uploaded_at' => $document->created_at->toISOString()
                ]
            ], 201);
            
        } catch (\InvalidArgumentException $e) {
            // Erreurs de validation du service
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 422);
            
        } catch (\Exception $e) {
            // Erreurs système
            Log::error('Erreur upload document', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'file' => $request->file('file')?->getClientOriginalName()
            ]);
            
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de l\'upload du document',
                'error' => config('app.debug') ? $e->getMessage() : 'Erreur interne du serveur'
            ], 500);
        }
    }
    
    /**
     * Affiche/Visualise un document
     * 
     * @param ApplicationDocument $document (route model binding)
     * @return Response
     */
    public function show(ApplicationDocument $document): Response
    {
        // Vérifier que le fichier existe
        if (!Storage::exists($document->file_path)) {
            abort(404, 'Document introuvable sur le serveur');
        }
        
        // Vérifier l'intégrité du fichier (optionnel mais recommandé)
        if (!$document->verifyIntegrity()) {
            Log::error('Intégrité du document compromise', [
                'document_id' => $document->id,
                'file_path' => $document->file_path
            ]);
            
            abort(500, 'Intégrité du document compromise');
        }
        
        // Logger la consultation
        Log::info('Document consulté', [
            'document_id' => $document->id,
            'type' => $document->document_type,
            'ip' => request()->ip()
        ]);
        
        // Retourner le fichier pour affichage inline
        return response()->make(
            Storage::get($document->file_path),
            200,
            [
                'Content-Type' => $document->mime_type,
                'Content-Disposition' => 'inline; filename="' . $document->original_name . '"',
                'Content-Length' => $document->file_size,
                'Cache-Control' => 'public, max-age=3600',
                'X-Content-Type-Options' => 'nosniff'
            ]
        );
    }
    
    /**
     * Télécharge un document
     * 
     * @param ApplicationDocument $document
     * @return Response
     */
    public function download(ApplicationDocument $document): Response
    {
        if (!Storage::exists($document->file_path)) {
            abort(404, 'Document introuvable');
        }
        
        // Vérifier l'intégrité
        if (!$document->verifyIntegrity()) {
            abort(500, 'Intégrité du document compromise');
        }
        
        Log::info('Document téléchargé', [
            'document_id' => $document->id,
            'type' => $document->document_type,
            'ip' => request()->ip()
        ]);
        
        // Forcer le téléchargement
        return response()->download(
            Storage::path($document->file_path),
            $document->original_name,
            [
                'Content-Type' => $document->mime_type,
                'Content-Length' => $document->file_size
            ]
        );
    }
    
    /**
     * Liste les documents d'une candidature
     * 
     * @param Request $request
     * @return JsonResponse
     */
    public function index(Request $request): JsonResponse
    {
        $query = ApplicationDocument::query();
        
        // Filtrer par candidature
        if ($request->filled('merchant_application_id')) {
            $query->where('merchant_application_id', $request->input('merchant_application_id'));
        }
        
        // Filtrer par type
        if ($request->filled('document_type')) {
            $query->where('document_type', $request->input('document_type'));
        }
        
        // Filtrer par statut de vérification
        if ($request->filled('is_verified')) {
            $query->where('is_verified', $request->boolean('is_verified'));
        }
        
        // Charger les relations
        $query->with('merchantApplication');
        
        // Pagination
        $documents = $query->paginate($request->get('per_page', 20));
        
        return response()->json([
            'success' => true,
            'data' => ApplicationDocumentResource::collection($documents->items()),
            'meta' => [
                'current_page' => $documents->currentPage(),
                'last_page' => $documents->lastPage(),
                'per_page' => $documents->perPage(),
                'total' => $documents->total()
            ]
        ]);
    }
    
    /**
     * Vérifie/Valide un document (admin)
     * 
     * @param ApplicationDocument $document
     * @param Request $request
     * @return JsonResponse
     */
    public function verify(ApplicationDocument $document, Request $request): JsonResponse
    {
        $validated = $request->validate([
            'notes' => 'sometimes|string|max:500|nullable'
        ]);
        
        // Vérifier d'abord l'intégrité du fichier
        if (!$document->verifyIntegrity()) {
            return response()->json([
                'success' => false,
                'message' => 'L\'intégrité du document ne peut être vérifiée. Le fichier a peut-être été modifié.'
            ], 400);
        }
        
        // Marquer comme vérifié
        $document->is_verified = true;
        $document->verified_at = now();
        $document->verified_by = auth()->id(); // ID de l'admin connecté
        $document->verification_notes = $validated['notes'] ?? null;
        $document->save();
        
        Log::info('Document vérifié', [
            'document_id' => $document->id,
            'verified_by' => auth()->id(),
            'merchant_application_id' => $document->merchant_application_id
        ]);
        
        return response()->json([
            'success' => true,
            'message' => 'Document vérifié avec succès',
            'data' => new ApplicationDocumentResource($document)
        ]);
    }
    
    /**
     * Annule la vérification d'un document (admin)
     * 
     * @param ApplicationDocument $document
     * @return JsonResponse
     */
    public function unverify(ApplicationDocument $document): JsonResponse
    {
        $document->is_verified = false;
        $document->verified_at = null;
        $document->verified_by = null;
        $document->verification_notes = null;
        $document->save();
        
        Log::info('Vérification document annulée', [
            'document_id' => $document->id,
            'unverified_by' => auth()->id()
        ]);
        
        return response()->json([
            'success' => true,
            'message' => 'Vérification annulée'
        ]);
    }
    
    /**
     * Supprime un document
     * 
     * @param ApplicationDocument $document
     * @return JsonResponse
     */
    public function destroy(ApplicationDocument $document): JsonResponse
    {
        try {
            $documentId = $document->id;
            $filePath = $document->file_path;
            
            // Supprimer le fichier physique
            if (Storage::exists($filePath)) {
                Storage::delete($filePath);
            }
            
            // Supprimer l'entrée en base (soft delete si configuré)
            $document->delete();
            
            Log::warning('Document supprimé', [
                'document_id' => $documentId,
                'file_path' => $filePath,
                'deleted_by' => auth()->id()
            ]);
            
            return response()->json([
                'success' => true,
                'message' => 'Document supprimé avec succès'
            ]);
            
        } catch (\Exception $e) {
            Log::error('Erreur suppression document', [
                'document_id' => $document->id,
                'error' => $e->getMessage()
            ]);
            
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la suppression',
                'error' => config('app.debug') ? $e->getMessage() : 'Erreur interne'
            ], 500);
        }
    }
    
    /**
     * Vérifie l'intégrité d'un document
     * 
     * @param ApplicationDocument $document
     * @return JsonResponse
     */
    public function checkIntegrity(ApplicationDocument $document): JsonResponse
    {
        if (!Storage::exists($document->file_path)) {
            return response()->json([
                'success' => false,
                'message' => 'Fichier introuvable',
                'integrity' => false
            ], 404);
        }
        
        $isValid = $document->verifyIntegrity();
        
        Log::info('Vérification intégrité document', [
            'document_id' => $document->id,
            'is_valid' => $isValid
        ]);
        
        return response()->json([
            'success' => true,
            'integrity' => $isValid,
            'message' => $isValid 
                ? 'Le document est intègre' 
                : 'L\'intégrité du document ne peut être vérifiée',
            'hash' => $document->hash_sha256
        ]);
    }
    
    /**
     * Obtient les métadonnées d'un document
     * 
     * @param ApplicationDocument $document
     * @return JsonResponse
     */
    public function metadata(ApplicationDocument $document): JsonResponse
    {
        $metadata = [
            'id' => $document->id,
            'uuid' => $document->uuid,
            'document_type' => $document->document_type,
            'original_name' => $document->original_name,
            'file_size' => $document->file_size,
            'formatted_size' => $document->formatted_size,
            'mime_type' => $document->mime_type,
            'hash_sha256' => $document->hash_sha256,
            'upload_ip' => $document->upload_ip,
            'is_verified' => $document->is_verified,
            'verified_at' => $document->verified_at?->toISOString(),
            'uploaded_at' => $document->created_at->toISOString(),
            'file_exists' => Storage::exists($document->file_path),
            'url' => $document->url,
        ];
        
        return response()->json([
            'success' => true,
            'data' => $metadata
        ]);
    }
}