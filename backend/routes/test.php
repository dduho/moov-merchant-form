<?php

use Illuminate\Support\Facades\Route;
use App\Models\MerchantApplication;
use App\Models\User;

Route::get('/test-filter', function() {
    // Test du filtrage pour l'utilisateur commercial
    $commercial = User::where('username', 'commercial')->first();
    
    if (!$commercial) {
        return response()->json(['error' => 'Commercial user not found']);
    }
    
    // Charger les rôles
    $commercial->load('roles');
    
    // Toutes les candidatures
    $allApplications = MerchantApplication::all();
    
    // Candidatures du commercial
    $commercialApplications = MerchantApplication::where('user_id', $commercial->id)->get();
    
    return response()->json([
        'commercial_user' => [
            'id' => $commercial->id,
            'username' => $commercial->username,
            'roles' => $commercial->roles->pluck('name')
        ],
        'all_applications_count' => $allApplications->count(),
        'commercial_applications_count' => $commercialApplications->count(),
        'all_applications' => $allApplications->map(function($app) {
            return [
                'id' => $app->id,
                'user_id' => $app->user_id,
                'reference' => $app->reference_number,
                'name' => $app->full_name
            ];
        }),
        'commercial_applications' => $commercialApplications->map(function($app) {
            return [
                'id' => $app->id,
                'user_id' => $app->user_id,
                'reference' => $app->reference_number,
                'name' => $app->full_name
            ];
        })
    ]);
});

Route::get('/test-list-users', function() {
    $users = User::with('roles')->get();
    
    return response()->json([
        'users' => $users->map(function($user) {
            return [
                'id' => $user->id,
                'username' => $user->username,
                'email' => $user->email,
                'roles' => $user->roles->pluck('name')
            ];
        })
    ]);
});

Route::get('/test-notify-admins/{id}', function($id) {
    try {
        $application = MerchantApplication::findOrFail($id);
        
        $notificationService = new \App\Services\NotificationService();
        $notificationService->notifyNewApplication($application);
        
        return response()->json([
            'message' => 'Notification admin envoyée avec succès',
            'application' => [
                'id' => $application->id,
                'reference' => $application->reference_number,
                'name' => $application->full_name
            ]
        ]);
        
    } catch (\Exception $e) {
        return response()->json([
            'error' => 'Erreur lors de l\'envoi de la notification admin',
            'details' => $e->getMessage()
        ], 500);
    }
});