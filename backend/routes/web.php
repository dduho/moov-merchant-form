<?php

use Illuminate\Support\Facades\Route;
use App\Models\MerchantApplication;
use App\Models\User;
use Illuminate\Support\Facades\Storage;
use Illuminate\Http\Request;

Route::get('/', function () {
    return view('welcome');
});

// Route pour servir les documents privés
Route::get('/documents/{path}', function (Request $request, $path) {
    \Log::info("Documents route accessed with path: " . $path);
    
    // Ajouter le préfixe merchant-documents
    $fullPath = 'merchant-documents/' . $path;
    
    if (!Storage::exists($fullPath)) {
        \Log::error("File not found: " . $fullPath);
        abort(404);
    }
    
    \Log::info("Serving file: " . $fullPath);
    return Storage::response($fullPath);
})->where('path', '.*');

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

Route::get('/test-check-admin-notifications', function() {
    $admin = User::where('username', 'admin')->first();
    
    if (!$admin) {
        return response()->json(['error' => 'Admin user not found']);
    }
    
    $notifications = \App\Models\Notification::where('user_id', $admin->id)
        ->orderBy('created_at', 'desc')
        ->take(5)
        ->get();
    
    return response()->json([
        'admin' => [
            'id' => $admin->id,
            'username' => $admin->username
        ],
        'latest_notifications' => $notifications->map(function($notif) {
            return [
                'id' => $notif->id,
                'type' => $notif->type,
                'title' => $notif->title,
                'message' => $notif->message,
                'read_at' => $notif->read_at,
                'created_at' => $notif->created_at
            ];
        })
    ]);
});

Route::get('/clean-test-notifications', function() {
    try {
        $deletedCount = \App\Models\Notification::whereIn('type', [
            'application_approved',
            'application_rejected', 
            'new_application'
        ])->delete();
        
        return response()->json([
            'message' => 'Notifications de test nettoyées',
            'deleted_count' => $deletedCount
        ]);
        
    } catch (\Exception $e) {
        return response()->json([
            'error' => 'Erreur lors du nettoyage',
            'details' => $e->getMessage()
        ], 500);
    }
});
