<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureNotificationBelongsToUser
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
            return response()->json([
                'success' => false,
                'message' => 'Non authentifié'
            ], 401);
        }

        // Récupérer l'ID/objet de notification depuis les paramètres de route
        $notificationParam = $request->route('notification');
        
        if ($notificationParam) {
            // Si c'est déjà un objet Notification (model binding), l'utiliser directement
            if ($notificationParam instanceof \App\Models\Notification) {
                $notification = $notificationParam;
            } else {
                // Sinon, c'est un ID, le récupérer
                $notification = \App\Models\Notification::find($notificationParam);
            }
            
            if (!$notification) {
                return response()->json([
                    'success' => false,
                    'message' => 'Notification non trouvée'
                ], 404);
            }
            
            // Vérifier que la notification appartient à l'utilisateur
            // Simple vérification : user_id doit correspondre
            if ($notification->user_id !== $user->id) {
                \Illuminate\Support\Facades\Log::warning('Tentative d\'accès à une notification non autorisée', [
                    'user_id' => $user->id,
                    'notification_id' => $notification->id,
                    'notification_user_id' => $notification->user_id,
                    'route' => $request->route()->getName(),
                    'ip' => $request->ip()
                ]);
                
                return response()->json([
                    'success' => false,
                    'message' => 'Accès non autorisé à cette notification'
                ], 403);
            }
        }
        
        return $next($request);
    }
}