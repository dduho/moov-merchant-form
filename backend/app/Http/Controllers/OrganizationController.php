<?php

namespace App\Http\Controllers;

use App\Models\Organization;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;

class OrganizationController extends Controller
{
    /**
     * List all organizations.
     */
    public function index(Request $request): JsonResponse
    {
        $user = Auth::user();

        // Only Moov staff can see all organizations
        if (!$user->isMoovStaff()) {
            return response()->json(['error' => 'Accès non autorisé'], 403);
        }

        $query = Organization::withCount('pointsOfSale');

        if ($request->has('active_only')) {
            $query->active();
        }

        if ($request->has('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('code', 'like', "%{$search}%");
            });
        }

        $sortBy = $request->get('sort_by', 'name');
        $sortDir = $request->get('sort_dir', 'asc');
        $query->orderBy($sortBy, $sortDir);

        $perPage = $request->get('per_page', 15);
        $organizations = $query->paginate($perPage);

        return response()->json($organizations);
    }

    /**
     * Show a specific organization.
     */
    public function show(Organization $organization): JsonResponse
    {
        $user = Auth::user();

        // Check access
        if (!$user->isMoovStaff() && $user->organization_id !== $organization->id) {
            return response()->json(['error' => 'Accès non autorisé'], 403);
        }

        return response()->json([
            'data' => $organization->loadCount('pointsOfSale')->loadCount('users')
        ]);
    }

    /**
     * Store a new organization.
     */
    public function store(Request $request): JsonResponse
    {
        $user = Auth::user();

        if (!$user->isMoovStaff()) {
            return response()->json(['error' => 'Action non autorisée'], 403);
        }

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'code' => 'nullable|string|max:20|unique:organizations,code',
            'phone' => 'nullable|string|max:20',
            'email' => 'nullable|email|max:255',
            'address' => 'nullable|string|max:500',
            'is_active' => 'boolean',
        ]);

        // Generate code if not provided
        if (empty($validated['code'])) {
            $validated['code'] = Organization::generateCode($validated['name']);
        }

        $organization = Organization::create($validated);

        return response()->json([
            'message' => 'Organisation créée avec succès',
            'data' => $organization
        ], 201);
    }

    /**
     * Update an organization.
     */
    public function update(Request $request, Organization $organization): JsonResponse
    {
        $user = Auth::user();

        if (!$user->isMoovStaff()) {
            return response()->json(['error' => 'Action non autorisée'], 403);
        }

        $validated = $request->validate([
            'name' => 'sometimes|string|max:255',
            'code' => 'sometimes|string|max:20|unique:organizations,code,' . $organization->id,
            'phone' => 'nullable|string|max:20',
            'email' => 'nullable|email|max:255',
            'address' => 'nullable|string|max:500',
            'is_active' => 'boolean',
        ]);

        $organization->update($validated);

        return response()->json([
            'message' => 'Organisation mise à jour avec succès',
            'data' => $organization->fresh()
        ]);
    }

    /**
     * Delete an organization.
     */
    public function destroy(Organization $organization): JsonResponse
    {
        $user = Auth::user();

        if (!$user->isMoovStaff()) {
            return response()->json(['error' => 'Action non autorisée'], 403);
        }

        // Check if organization has PDVs
        if ($organization->pointsOfSale()->count() > 0) {
            return response()->json([
                'error' => 'Impossible de supprimer une organisation avec des PDV associés'
            ], 422);
        }

        $organization->delete();

        return response()->json([
            'message' => 'Organisation supprimée avec succès'
        ]);
    }

    /**
     * Toggle organization active status.
     */
    public function toggleActive(Organization $organization): JsonResponse
    {
        $user = Auth::user();

        if (!$user->isMoovStaff()) {
            return response()->json(['error' => 'Action non autorisée'], 403);
        }

        $organization->is_active = !$organization->is_active;
        $organization->save();

        return response()->json([
            'message' => $organization->is_active ? 'Organisation activée' : 'Organisation désactivée',
            'data' => $organization
        ]);
    }

    /**
     * Get organization users.
     */
    public function users(Organization $organization): JsonResponse
    {
        $user = Auth::user();

        // Check access
        if (!$user->isMoovStaff() && $user->organization_id !== $organization->id) {
            return response()->json(['error' => 'Accès non autorisé'], 403);
        }

        return response()->json([
            'data' => $organization->users()->with('roles')->get()
        ]);
    }

    /**
     * Get organization PDVs.
     */
    public function pdvs(Request $request, Organization $organization): JsonResponse
    {
        $user = Auth::user();

        // Check access
        if (!$user->isMoovStaff() && $user->organization_id !== $organization->id) {
            return response()->json(['error' => 'Accès non autorisé'], 403);
        }

        $query = $organization->pointsOfSale()->with(['creator', 'validator']);

        if ($request->has('status')) {
            $query->where('status', $request->status);
        }

        $perPage = $request->get('per_page', 15);
        $pdvs = $query->orderBy('created_at', 'desc')->paginate($perPage);

        return response()->json($pdvs);
    }

    /**
     * Get all organizations as a simple list (for dropdowns).
     */
    public function list(): JsonResponse
    {
        $organizations = Organization::active()
            ->select('id', 'name', 'code')
            ->orderBy('name')
            ->get();

        return response()->json([
            'data' => $organizations
        ]);
    }
}
