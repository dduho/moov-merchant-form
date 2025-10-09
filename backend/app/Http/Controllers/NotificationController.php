<?php

namespace App\Http\Controllers;

use App\Models\Notification;
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
        $user = auth()->user();
        
        $unreadOnly = $request->boolean('unread_only', false);
        $limit = $request->integer('limit', 20);
        
        if ($unreadOnly) {
            $notifications = $this->notificationService->getUnreadNotifications($user, $limit);
        } else {
            $notifications = $this->notificationService->getAllNotifications($user, $limit);
        }
        
        return response()->json([
            'success' => true,
            'data' => $notifications,
            'unread_count' => $user->unreadNotificationsCount()
        ]);
    }

    /**
     * Obtenir le nombre de notifications non lues
     */
    public function unreadCount(): JsonResponse
    {
        $user = auth()->user();
        
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
        
        if (!$this->notificationService->markAsRead($notification, $user)) {
            return response()->json([
                'success' => false,
                'message' => 'Notification non trouvée ou non autorisée'
            ], 403);
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
        
        if ($notification->user_id !== $user->id) {
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
}