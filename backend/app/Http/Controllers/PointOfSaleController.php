<?php

namespace App\Http\Controllers;

use App\Models\PointOfSale;
use App\Models\SystemSetting;
use App\Services\ProximityAlertService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;

class PointOfSaleController extends Controller
{
    protected ProximityAlertService $proximityService;

    public function __construct(ProximityAlertService $proximityService)
    {
        $this->proximityService = $proximityService;
    }

    /**
     * List all PDVs with filters.
     */
    public function index(Request $request): JsonResponse
    {
        $user = Auth::user();
        $query = PointOfSale::with(['organization', 'creator', 'validator']);

        // Filter by organization for dealers
        if ($user->isDealer() && $user->organization_id) {
            $query->where('organization_id', $user->organization_id);
        }

        // Filter by creator for commercials
        if ($user->isCommercial() && !$user->isMoovStaff()) {
            $query->where('created_by', $user->id);
        }

        // Apply filters
        if ($request->has('status')) {
            $query->where('status', $request->status);
        }

        if ($request->has('region')) {
            $query->where('region', $request->region);
        }

        if ($request->has('prefecture')) {
            $query->where('prefecture', $request->prefecture);
        }

        if ($request->has('organization_id') && $user->isMoovStaff()) {
            $query->where('organization_id', $request->organization_id);
        }

        if ($request->has('search')) {
            $query->search($request->search);
        }

        // Sorting
        $sortBy = $request->get('sort_by', 'created_at');
        $sortDir = $request->get('sort_dir', 'desc');
        $query->orderBy($sortBy, $sortDir);

        // Pagination
        $perPage = $request->get('per_page', 15);
        $pdvs = $query->paginate($perPage);

        return response()->json($pdvs);
    }

    /**
     * Show a specific PDV.
     */
    public function show(PointOfSale $pointOfSale): JsonResponse
    {
        $user = Auth::user();

        // Check access
        if (!$this->canAccessPdv($user, $pointOfSale)) {
            return response()->json(['error' => 'Accès non autorisé'], 403);
        }

        return response()->json([
            'data' => $pointOfSale->load(['organization', 'creator', 'validator'])
        ]);
    }

    /**
     * Store a new PDV.
     */
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            // Required fields
            'organization_id' => 'required|exists:organizations,id',
            'dealer_name' => 'required|string|max:255',
            'nom_point' => 'required|string|max:255',
            'firstname' => 'required|string|max:255',
            'lastname' => 'required|string|max:255',
            'region' => 'required|in:MARITIME,PLATEAUX,CENTRALE,KARA,SAVANES',
            'latitude' => 'required|numeric|between:-90,90',
            'longitude' => 'required|numeric|between:-180,180',
            'numero_proprietaire' => 'required|string|max:20',
            
            // Optional fields
            'numero' => 'nullable|string|max:50',
            'numero_flooz' => 'nullable|string|max:50',
            'shortcode' => 'nullable|string|max:20',
            'profil' => 'nullable|string|max:100',
            'type_activite' => 'nullable|string|max:100',
            'date_of_birth' => 'nullable|date',
            'gender' => 'nullable|in:M,F',
            'id_description' => 'nullable|string|max:100',
            'id_number' => 'nullable|string|max:50',
            'id_expiry_date' => 'nullable|date',
            'nationality' => 'nullable|string|max:100',
            'profession' => 'nullable|string|max:100',
            'prefecture' => 'nullable|string|max:100',
            'commune' => 'nullable|string|max:100',
            'canton' => 'nullable|string|max:100',
            'ville' => 'nullable|string|max:100',
            'quartier' => 'nullable|string|max:100',
            'localisation' => 'nullable|string',
            'gps_accuracy' => 'nullable|numeric|min:0',
            'autre_contact' => 'nullable|string|max:20',
            'nif' => 'nullable|string|max:50',
            'regime_fiscal' => 'nullable|string|max:100',
            'support_visibilite' => 'nullable|string|max:100',
            'etat_support' => 'nullable|in:BON,MAUVAIS',
            'numero_cagnt' => 'nullable|string|max:50',
        ]);

        $user = Auth::user();
        $validated['created_by'] = $user->id;
        $validated['status'] = 'pending';

        // Check GPS accuracy
        $requiredAccuracy = SystemSetting::getRequiredGpsAccuracy();
        if (isset($validated['gps_accuracy']) && $validated['gps_accuracy'] > $requiredAccuracy) {
            if (SystemSetting::shouldAutoRejectLowAccuracy()) {
                return response()->json([
                    'error' => "La précision GPS ({$validated['gps_accuracy']}m) dépasse la limite autorisée ({$requiredAccuracy}m)",
                    'gps_accuracy' => $validated['gps_accuracy'],
                    'required_accuracy' => $requiredAccuracy,
                ], 422);
            }
        }

        // Check proximity
        $proximityCheck = $this->proximityService->checkProximity(
            $validated['latitude'],
            $validated['longitude']
        );

        $pdv = PointOfSale::create($validated);

        return response()->json([
            'message' => 'PDV créé avec succès',
            'data' => $pdv->load(['organization', 'creator']),
            'proximity_alert' => $proximityCheck,
        ], 201);
    }

    /**
     * Update a PDV.
     */
    public function update(Request $request, PointOfSale $pointOfSale): JsonResponse
    {
        $user = Auth::user();

        // Check access
        if (!$this->canModifyPdv($user, $pointOfSale)) {
            return response()->json(['error' => 'Modification non autorisée'], 403);
        }

        // Can only update pending PDVs (unless admin)
        if ($pointOfSale->status !== 'pending' && !$user->isMoovStaff()) {
            return response()->json(['error' => 'Seuls les PDV en attente peuvent être modifiés'], 403);
        }

        $validated = $request->validate([
            'dealer_name' => 'sometimes|string|max:255',
            'nom_point' => 'sometimes|string|max:255',
            'firstname' => 'sometimes|string|max:255',
            'lastname' => 'sometimes|string|max:255',
            'region' => 'sometimes|in:MARITIME,PLATEAUX,CENTRALE,KARA,SAVANES',
            'latitude' => 'sometimes|numeric|between:-90,90',
            'longitude' => 'sometimes|numeric|between:-180,180',
            'numero_proprietaire' => 'sometimes|string|max:20',
            // ... other optional fields
        ]);

        $pointOfSale->update($validated);

        // Check proximity if coordinates changed
        $proximityAlert = null;
        if (isset($validated['latitude']) || isset($validated['longitude'])) {
            $proximityAlert = $this->proximityService->checkProximity(
                $pointOfSale->latitude,
                $pointOfSale->longitude,
                $pointOfSale->id
            );
        }

        return response()->json([
            'message' => 'PDV mis à jour avec succès',
            'data' => $pointOfSale->fresh(['organization', 'creator', 'validator']),
            'proximity_alert' => $proximityAlert,
        ]);
    }

    /**
     * Validate a PDV (admin only).
     */
    public function validate(Request $request, PointOfSale $pointOfSale): JsonResponse
    {
        $user = Auth::user();

        if (!$user->isMoovStaff()) {
            return response()->json(['error' => 'Action non autorisée'], 403);
        }

        if ($pointOfSale->status !== 'pending') {
            return response()->json(['error' => 'Ce PDV n\'est pas en attente de validation'], 422);
        }

        $pointOfSale->validate($user->id);

        return response()->json([
            'message' => 'PDV validé avec succès',
            'data' => $pointOfSale->fresh(['organization', 'creator', 'validator']),
        ]);
    }

    /**
     * Reject a PDV (admin only).
     */
    public function reject(Request $request, PointOfSale $pointOfSale): JsonResponse
    {
        $user = Auth::user();

        if (!$user->isMoovStaff()) {
            return response()->json(['error' => 'Action non autorisée'], 403);
        }

        $request->validate([
            'reason' => 'required|string|max:500',
        ]);

        if ($pointOfSale->status !== 'pending') {
            return response()->json(['error' => 'Ce PDV n\'est pas en attente de validation'], 422);
        }

        $pointOfSale->reject($request->reason, $user->id);

        return response()->json([
            'message' => 'PDV rejeté',
            'data' => $pointOfSale->fresh(['organization', 'creator', 'validator']),
        ]);
    }

    /**
     * Check proximity for a location.
     */
    public function checkProximity(Request $request): JsonResponse
    {
        $request->validate([
            'latitude' => 'required|numeric|between:-90,90',
            'longitude' => 'required|numeric|between:-180,180',
            'exclude_id' => 'nullable|integer|exists:point_of_sales,id',
        ]);

        $result = $this->proximityService->checkProximity(
            $request->latitude,
            $request->longitude,
            $request->exclude_id
        );

        return response()->json($result);
    }

    /**
     * Get all PDVs for map display.
     */
    public function mapData(Request $request): JsonResponse
    {
        $user = Auth::user();
        $query = PointOfSale::select([
            'id', 'reference_number', 'nom_point', 'dealer_name',
            'latitude', 'longitude', 'status', 'region', 'prefecture',
            'organization_id'
        ]);

        // Filter by organization for dealers
        if ($user->isDealer() && $user->organization_id) {
            $query->where('organization_id', $user->organization_id);
        }

        // Filter by status
        if ($request->has('status')) {
            $query->where('status', $request->status);
        }

        // Filter by region
        if ($request->has('region')) {
            $query->where('region', $request->region);
        }

        return response()->json([
            'data' => $query->get(),
            'alert_distance' => SystemSetting::getProximityAlertDistance(),
        ]);
    }

    /**
     * Get pending PDVs for validation queue.
     */
    public function validationQueue(Request $request): JsonResponse
    {
        $user = Auth::user();

        if (!$user->isMoovStaff()) {
            return response()->json(['error' => 'Action non autorisée'], 403);
        }

        $perPage = $request->get('per_page', 15);
        
        $pdvs = PointOfSale::pending()
            ->with(['organization', 'creator'])
            ->orderBy('created_at', 'asc')
            ->paginate($perPage);

        return response()->json($pdvs);
    }

    /**
     * Get regions list.
     */
    public function getRegions(): JsonResponse
    {
        return response()->json([
            'data' => PointOfSale::getRegions()
        ]);
    }

    /**
     * Delete a PDV.
     */
    public function destroy(PointOfSale $pointOfSale): JsonResponse
    {
        $user = Auth::user();

        if (!$this->canModifyPdv($user, $pointOfSale)) {
            return response()->json(['error' => 'Suppression non autorisée'], 403);
        }

        $pointOfSale->delete();

        return response()->json([
            'message' => 'PDV supprimé avec succès'
        ]);
    }

    /**
     * Check if user can access a PDV.
     */
    protected function canAccessPdv($user, PointOfSale $pdv): bool
    {
        // Moov staff can access all
        if ($user->isMoovStaff()) {
            return true;
        }

        // Dealers can access their organization's PDVs
        if ($user->isDealer() && $user->organization_id === $pdv->organization_id) {
            return true;
        }

        // Commercials can access PDVs they created
        if ($user->isCommercial() && $pdv->created_by === $user->id) {
            return true;
        }

        return false;
    }

    /**
     * Check if user can modify a PDV.
     */
    protected function canModifyPdv($user, PointOfSale $pdv): bool
    {
        // Moov staff can modify all
        if ($user->isMoovStaff()) {
            return true;
        }

        // Commercials can modify pending PDVs they created
        if ($pdv->status === 'pending' && $pdv->created_by === $user->id) {
            return true;
        }

        return false;
    }
}
