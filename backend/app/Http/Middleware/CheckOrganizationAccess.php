<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckOrganizationAccess
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if (!$user) {
            return response()->json(['error' => 'Authentification requise'], 401);
        }

        // Moov staff can access everything
        if ($user->isMoovStaff()) {
            return $next($request);
        }

        // Check organization access for route parameters
        $organizationId = $request->route('organization') ?? $request->route('organization_id') ?? $request->input('organization_id');

        if ($organizationId) {
            // Get the actual ID if it's a model instance
            if (is_object($organizationId)) {
                $organizationId = $organizationId->id;
            }

            // Check if user belongs to this organization
            if ($user->organization_id !== (int) $organizationId) {
                return response()->json([
                    'error' => 'Vous n\'avez pas accès à cette organisation'
                ], 403);
            }
        }

        // For dealers and commercials without organization
        if (($user->isDealer() || $user->isCommercial()) && !$user->organization_id) {
            return response()->json([
                'error' => 'Votre compte n\'est associé à aucune organisation'
            ], 403);
        }

        return $next($request);
    }
}
