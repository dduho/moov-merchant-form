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

Route::get('/test-duplicate-phone', function() {
    try {
        $app = new \App\Models\MerchantApplication();
        $app->fill([
            'first_name' => 'Test',
            'last_name' => 'User',
            'full_name' => 'Test User',
            'phone' => '14996711', // Numéro qui existait déjà
            'birth_date' => '1990-01-01',
            'address' => 'Test Address',
            'id_number' => 'TEST' . uniqid(),
            'id_expiry_date' => '2030-01-01',
            'business_name' => 'Test Business',
            'business_type' => 'boutique',
            'reference_number' => 'TEST' . uniqid(),
            'user_id' => 2
        ]);
        $app->save();
        
        return response()->json([
            'message' => 'Candidature créée avec succès avec téléphone dupliqué !',
            'application' => [
                'id' => $app->id,
                'phone' => $app->phone,
                'reference' => $app->reference_number,
                'uuid' => $app->uuid
            ]
        ]);
        
    } catch (\Exception $e) {
        return response()->json([
            'error' => 'Erreur lors de la création',
            'details' => $e->getMessage()
        ], 500);
    }
});

Route::get('/test-duplicate-email', function() {
    try {
        $app = new \App\Models\MerchantApplication();
        $app->fill([
            'first_name' => 'Test',
            'last_name' => 'Email',
            'full_name' => 'Test Email',
            'phone' => '987654321',
            'email' => 'balyluciwe@mailinator.com', // Email qui existait déjà
            'birth_date' => '1990-01-01',
            'address' => 'Test Address',
            'id_number' => 'TESTEMAIL' . uniqid(),
            'id_expiry_date' => '2030-01-01',
            'business_name' => 'Test Email Business',
            'business_type' => 'boutique',
            'reference_number' => 'TESTEMAIL' . uniqid(),
            'user_id' => 2
        ]);
        $app->save();
        
        return response()->json([
            'message' => 'Candidature créée avec succès avec email dupliqué !',
            'application' => [
                'id' => $app->id,
                'email' => $app->email,
                'reference' => $app->reference_number,
                'uuid' => $app->uuid
            ]
        ]);
        
    } catch (\Exception $e) {
        return response()->json([
            'error' => 'Erreur lors de la création',
            'details' => $e->getMessage()
        ], 500);
    }
});

Route::get('/test-duplicate-reference', function() {
    try {
        $app = new \App\Models\MerchantApplication();
        $app->fill([
            'first_name' => 'Test',
            'last_name' => 'Ref',
            'full_name' => 'Test Ref',
            'phone' => '111222333',
            'birth_date' => '1990-01-01',
            'address' => 'Test Address',
            'id_number' => 'TESTREF' . uniqid(),
            'id_expiry_date' => '2030-01-01',
            'business_name' => 'Test Ref Business',
            'business_type' => 'boutique',
            'reference_number' => 'MM251009LPLXZQ', // Reference qui existe déjà
            'user_id' => 2
        ]);
        $app->save();
        
        return response()->json([
            'message' => 'ATTENTION: Candidature créée avec reference dupliquée ! (ne devrait pas arriver)',
            'application' => [
                'id' => $app->id,
                'reference' => $app->reference_number
            ]
        ]);
        
    } catch (\Exception $e) {
        return response()->json([
            'message' => 'CORRECT: Erreur de contrainte d\'unicité sur reference_number (comme attendu)',
            'error' => $e->getMessage()
        ], 200); // 200 car c'est le comportement attendu
    }
});
