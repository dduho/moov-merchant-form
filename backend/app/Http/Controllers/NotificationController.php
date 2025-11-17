<?php

namespace App\Http\Controllers;

use App\Models\Notification;
use App\Models\User;
use App\Services\NotificationService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class NotificationController extends Controller
{
    public function __construct(
        private NotificationService $notificationService
    ) {}

    /**
     * Obtenir les notifications de l'utilisateur connecté
     */
    public function index(Request $request): JsonResponse
    {
        try {
            $user = auth()->user();
            
            if (!$user) {
                return response()->json([
                    'success' => false,
                    'message' => 'Non authentifié'
                ], 401);
            }
            
            $unreadOnly = $request->boolean('unread_only', false);
            $limit = $request->integer('limit', 20);
            
            // Version simplifiée pour debug
            $query = $user->notifications()
                ->notExpired()
                ->orderBy('created_at', 'desc');
                
            if ($unreadOnly) {
                $query->unread();
            }
            
            $notifications = $query->limit($limit)->get();
            
            // Retour simple sans ressource pour debug
            $notificationsArray = $notifications->map(function($n) {
                return [
                    'id' => $n->id,
                    'type' => $n->type,
                    'title' => $n->title,
                    'message' => $n->message,
                    'data' => $n->data,
                    'read_at' => $n->read_at,
                    'is_read' => $n->isRead(),
                    'created_at' => $n->created_at,
                    'action_url' => $n->action_url,
                    'priority' => $n->priority
                ];
            });
            
            return response()->json([
                'success' => true,
                'data' => $notificationsArray,
                'unread_count' => $user->unreadNotificationsCount()
            ]);
            
        } catch (\Exception $e) {
            \Log::error('Erreur dans NotificationController::index', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la récupération des notifications',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Obtenir le nombre de notifications non lues
     */
    public function unreadCount(): JsonResponse
    {
        $user = auth()->user();
        
        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'Non authentifié'
            ], 401);
        }
        
        return response()->json([
            'success' => true,
            'unread_count' => $user->unreadNotificationsCount()
        ]);
    }

    /**
     * Marquer une notification comme lue
     */
    public function markAsRead(Notification $notification): JsonResponse
    {
        $user = auth()->user();
        
        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'Non authentifié'
            ], 401);
        }
        
        // Vérifier que l'utilisateur peut marquer cette notification comme lue
        if (!$this->canUserAccessNotification($notification, $user)) {
            return response()->json([
                'success' => false,
                'message' => 'Notification non trouvée ou non autorisée'
            ], 403);
        }
        
        if (!$this->notificationService->markAsRead($notification, $user)) {
            return response()->json([
                'success' => false,
                'message' => 'Impossible de marquer la notification comme lue'
            ], 500);
        }
        
        return response()->json([
            'success' => true,
            'message' => 'Notification marquée comme lue'
        ]);
    }

    /**
     * Marquer toutes les notifications comme lues
     */
    public function markAllAsRead(): JsonResponse
    {
        $user = auth()->user();
        
        $updated = $this->notificationService->markAllAsRead($user);
        
        return response()->json([
            'success' => true,
            'message' => "Toutes les notifications ont été marquées comme lues",
            'updated_count' => $updated
        ]);
    }

    /**
     * Supprimer une notification
     */
    public function destroy(Notification $notification): JsonResponse
    {
        $user = auth()->user();
        
        // Vérifier que l'utilisateur peut supprimer cette notification
        if (!$this->canUserAccessNotification($notification, $user)) {
            return response()->json([
                'success' => false,
                'message' => 'Notification non trouvée ou non autorisée'
            ], 403);
        }
        
        $notification->delete();
        
        return response()->json([
            'success' => true,
            'message' => 'Notification supprimée'
        ]);
    }

    /**
     * Vérifier si un utilisateur peut accéder à une notification
     */
    private function canUserAccessNotification(Notification $notification, User $user): bool
    {
        // L'utilisateur doit être le propriétaire de la notification
        return $notification->user_id === $user->id;
    }
}