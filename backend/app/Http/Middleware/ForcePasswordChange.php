<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class ForcePasswordChange
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        // Vérifier si l'utilisateur doit changer son mot de passe
        if ($user && $user->must_change_password) {
            // Permettre les routes de changement de mot de passe et de déconnexion
            $allowedRoutes = [
                'api/auth/change-password',
                'api/auth/logout',
                'api/auth/me',
                // Routes dashboard autorisées
                'api/dashboard/stats',
                'api/dashboard/kpis',
                'api/dashboard/alerts', 
                'api/dashboard/charts',
                'api/dashboard/recent',
                'api/dashboard/user-stats',
                // Routes gestion utilisateurs autorisées pour les admins
                'api/users',
                'api/users/commercials',
                // Routes gestion objectifs autorisées pour les admins
                'api/objectives',
                'api/objectives/progress-stats'
            ];

            $currentPath = trim($request->getPathInfo(), '/');
            
            if (!in_array($currentPath, $allowedRoutes)) {
                return response()->json([
                    'message' => 'Vous devez changer votre mot de passe avant de continuer.',
                    'must_change_password' => true,
                    'redirect' => '/change-password'
                ], 403);
            }
        }

        return $next($request);
    }
}