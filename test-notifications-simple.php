<?php

// Test simple des notifications
use Illuminate\Support\Facades\Route;

Route::get('/test-notifications-simple', function () {
    try {
        // Test 1: Vérifier qu'on a des utilisateurs
        $users = \App\Models\User::with('roles')->get();
        
        if ($users->isEmpty()) {
            return response()->json([
                'error' => 'Aucun utilisateur dans la base de données'
            ]);
        }
        
        // Test 2: Vérifier qu'on peut créer une notification
        $user = $users->first();
        $notification = \App\Models\Notification::create([
            'user_id' => $user->id,
            'type' => 'test',
            'title' => 'Test notification',
            'message' => 'Ceci est un test de notification',
            'data' => ['test' => true],
            'priority' => 'normal'
        ]);
        
        // Test 3: Vérifier qu'on peut la récupérer
        $userNotifications = $user->notifications()
            ->notExpired()
            ->orderBy('created_at', 'desc')
            ->get();
            
        // Nettoyer
        $notification->delete();
        
        return response()->json([
            'success' => true,
            'message' => 'Tests réussis',
            'data' => [
                'users_count' => $users->count(),
                'test_user' => [
                    'id' => $user->id,
                    'name' => $user->first_name . ' ' . $user->last_name,
                    'roles' => $user->roles->pluck('slug')
                ],
                'test_notification_created' => $notification ? true : false,
                'notifications_retrieved' => $userNotifications->count()
            ]
        ]);
        
    } catch (\Exception $e) {
        return response()->json([
            'error' => 'Erreur lors des tests: ' . $e->getMessage(),
            'trace' => $e->getTraceAsString()
        ], 500);
    }
});