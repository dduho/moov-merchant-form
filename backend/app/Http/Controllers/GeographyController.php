<?php

namespace App\Http\Controllers;

use App\Models\GeographicHierarchy;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class GeographyController extends Controller
{
    /**
     * Get all regions.
     */
    public function regions(): JsonResponse
    {
        $regions = GeographicHierarchy::regions()
            ->active()
            ->orderBy('name')
            ->get(['id', 'name', 'code']);

        return response()->json([
            'data' => $regions
        ]);
    }

    /**
     * Get prefectures for a region.
     */
    public function prefectures(Request $request): JsonResponse
    {
        $request->validate([
            'region_id' => 'required|exists:geographic_hierarchy,id',
        ]);

        $prefectures = GeographicHierarchy::prefectures()
            ->where('parent_id', $request->region_id)
            ->active()
            ->orderBy('name')
            ->get(['id', 'name', 'code', 'parent_id']);

        return response()->json([
            'data' => $prefectures
        ]);
    }

    /**
     * Get communes for a prefecture.
     */
    public function communes(Request $request): JsonResponse
    {
        $request->validate([
            'prefecture_id' => 'required|exists:geographic_hierarchy,id',
        ]);

        $communes = GeographicHierarchy::communes()
            ->where('parent_id', $request->prefecture_id)
            ->active()
            ->orderBy('name')
            ->get(['id', 'name', 'code', 'parent_id']);

        return response()->json([
            'data' => $communes
        ]);
    }

    /**
     * Get cantons for a commune.
     */
    public function cantons(Request $request): JsonResponse
    {
        $request->validate([
            'commune_id' => 'required|exists:geographic_hierarchy,id',
        ]);

        $cantons = GeographicHierarchy::cantons()
            ->where('parent_id', $request->commune_id)
            ->active()
            ->orderBy('name')
            ->get(['id', 'name', 'code', 'parent_id']);

        return response()->json([
            'data' => $cantons
        ]);
    }

    /**
     * Get villes for a canton.
     */
    public function villes(Request $request): JsonResponse
    {
        $request->validate([
            'canton_id' => 'required|exists:geographic_hierarchy,id',
        ]);

        $villes = GeographicHierarchy::villes()
            ->where('parent_id', $request->canton_id)
            ->active()
            ->orderBy('name')
            ->get(['id', 'name', 'code', 'parent_id']);

        return response()->json([
            'data' => $villes
        ]);
    }

    /**
     * Get children of a location.
     */
    public function children(Request $request): JsonResponse
    {
        $request->validate([
            'parent_id' => 'required|exists:geographic_hierarchy,id',
        ]);

        $children = GeographicHierarchy::where('parent_id', $request->parent_id)
            ->active()
            ->orderBy('name')
            ->get(['id', 'name', 'code', 'type', 'parent_id']);

        return response()->json([
            'data' => $children
        ]);
    }

    /**
     * Get the full hierarchy.
     */
    public function fullHierarchy(): JsonResponse
    {
        $hierarchy = GeographicHierarchy::getFullHierarchy();

        return response()->json([
            'data' => $hierarchy
        ]);
    }

    /**
     * Search locations.
     */
    public function search(Request $request): JsonResponse
    {
        $request->validate([
            'query' => 'required|string|min:2',
            'type' => 'nullable|in:region,prefecture,commune,canton,ville',
        ]);

        $query = GeographicHierarchy::where('name', 'like', "%{$request->query}%")
            ->active()
            ->with('parent');

        if ($request->has('type')) {
            $query->where('type', $request->type);
        }

        $results = $query->limit(20)->get();

        return response()->json([
            'data' => $results
        ]);
    }

    /**
     * Get a location with its ancestors.
     */
    public function show(GeographicHierarchy $location): JsonResponse
    {
        $ancestors = [];
        $current = $location;

        while ($current->parent) {
            $current = $current->parent;
            $ancestors[] = [
                'id' => $current->id,
                'name' => $current->name,
                'type' => $current->type,
            ];
        }

        return response()->json([
            'data' => $location,
            'ancestors' => array_reverse($ancestors),
        ]);
    }
}
