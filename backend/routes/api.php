<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Http\Request;
use App\Http\Controllers\{
    MerchantApplicationController,
    DocumentController,
    DashboardController,
    AuthController,
    NotificationController,
    UserManagementController,
    ObjectiveController
};

// Routes d'authentification
Route::prefix('auth')->group(function () {
    Route::middleware('web')->post('/login', [AuthController::class, 'login']);
    
    Route::middleware(['web', 'auth:sanctum'])->group(function () {
        Route::get('/user', [AuthController::class, 'me']);
        Route::post('/register', [AuthController::class, 'register']);
        Route::post('/logout', [AuthController::class, 'logout']);
        Route::get('/me', [AuthController::class, 'me']);
        Route::put('/profile', [AuthController::class, 'updateProfile']);
        Route::post('/change-password', [UserManagementController::class, 'changePassword']);
        Route::post('/change-password-required', [AuthController::class, 'changePasswordRequired']);
    });
});

// Health check
Route::get('/health', fn() => response()->json([
    'status' => 'healthy',
    'service' => 'Moov Merchant API',
    'version' => '1.0.0',
    'timestamp' => now()->toISOString(),
]));

// Route de test temporaire pour diagnostiquer les problèmes d'auth
Route::middleware(['web', 'auth:sanctum'])->get('/debug-auth', function (Request $request) {
    $user = $request->user();
    return response()->json([
        'authenticated' => !!$user,
        'user_id' => $user ? $user->id : null,
        'user_email' => $user ? $user->email : null,
        'user_roles' => $user ? $user->roles->pluck('name') : [],
        'has_admin_role' => $user ? $user->hasRole('admin') : false,
        'middleware_stack' => 'web,auth:sanctum'
    ]);
});

// Groupe API avec rate limiting
Route::middleware(['throttle:api'])->group(function () {
    
    // ============================================================
    // ROUTES MERCHANT APPLICATIONS (MerchantApplicationController)
    // ============================================================
    Route::prefix('merchant-applications')->name('merchant-applications.')->group(function () {
        // Routes publiques (consultation)
        Route::get('/', [MerchantApplicationController::class, 'index'])->name('index');
        Route::get('/regions', [MerchantApplicationController::class, 'getRegions'])->name('regions');
        Route::get('/cities', [MerchantApplicationController::class, 'getCities'])->name('cities');
        Route::get('/reference/{reference}', [MerchantApplicationController::class, 'showByReference'])->name('show-by-reference');
        Route::get('/statistics/all', [MerchantApplicationController::class, 'statistics'])->name('statistics');
        
        // Routes nécessitant une authentification
        Route::middleware(['web', 'auth:sanctum', 'force.password.change'])->group(function () {
            Route::post('/', [MerchantApplicationController::class, 'store'])->name('store');
            Route::put('/{merchantApplication}', [MerchantApplicationController::class, 'update'])->name('update');
            Route::put('/{merchantApplication}/full', [MerchantApplicationController::class, 'fullUpdate'])->name('full-update');
            Route::delete('/{merchantApplication}', [MerchantApplicationController::class, 'destroy'])->name('destroy');
            
            // Routes administratives nécessitant une authentification
            Route::post('/{merchantApplication}/status', [MerchantApplicationController::class, 'updateStatus'])->name('update-status');
            Route::post('/{merchantApplication}/restore', [MerchantApplicationController::class, 'restore'])->name('restore');
            Route::post('/{merchantApplication}/approve', [MerchantApplicationController::class, 'approve'])->name('approve');
            Route::post('/{merchantApplication}/reject', [MerchantApplicationController::class, 'reject'])->name('reject');
            Route::post('/mark-as-exported', [MerchantApplicationController::class, 'markAsExported'])->name('mark-as-exported');
            Route::delete('/{merchantApplication}/force', [MerchantApplicationController::class, 'forceDestroy'])->name('force-destroy');
        });
        
        // Routes avec paramètres génériques (doivent être à la fin)
        Route::get('/{merchantApplication}', [MerchantApplicationController::class, 'show'])->name('show');
    });
    
    // ============================================================
    // ROUTES DOCUMENTS (DocumentController)
    // ============================================================
    Route::prefix('documents')->name('documents.')->group(function () {
        // Routes publiques (consultation)
        Route::get('/', [DocumentController::class, 'index'])->name('index');
        Route::get('/{document}', [DocumentController::class, 'show'])->name('show');
        Route::get('/{document}/download', [DocumentController::class, 'download'])->name('download');
        Route::get('/{document}/integrity', [DocumentController::class, 'checkIntegrity'])->name('check-integrity');
        Route::get('/{document}/metadata', [DocumentController::class, 'metadata'])->name('metadata');
        
        // Routes nécessitant une authentification
        Route::middleware(['web', 'auth:sanctum', 'force.password.change'])->group(function () {
            Route::post('/upload', [DocumentController::class, 'upload'])->name('upload');
            Route::delete('/{document}', [DocumentController::class, 'destroy'])->name('destroy');
            Route::post('/{document}/verify', [DocumentController::class, 'verify'])->name('verify');
            Route::post('/{document}/unverify', [DocumentController::class, 'unverify'])->name('unverify');
        });
    });
    
    // ============================================================
    // ROUTES DASHBOARD (DashboardController) - Authentification requise
    // ============================================================
    Route::middleware(['web', 'auth:sanctum', 'force.password.change'])->prefix('dashboard')->name('dashboard.')->group(function () {
        Route::get('/stats', [DashboardController::class, 'stats'])->name('stats');
        Route::get('/recent', [DashboardController::class, 'recent'])->name('recent');
        Route::get('/trends', [DashboardController::class, 'trends'])->name('trends');
        Route::get('/kpis', [DashboardController::class, 'kpis'])->name('kpis');
        Route::get('/alerts', [DashboardController::class, 'alerts'])->name('alerts');
        Route::get('/compare', [DashboardController::class, 'compare'])->name('compare');
        Route::get('/export', [DashboardController::class, 'export'])->name('export');
        Route::get('/summary', [DashboardController::class, 'summary'])->name('summary');
        Route::get('/charts', [DashboardController::class, 'charts'])->name('charts');
        Route::get('/user-stats', [DashboardController::class, 'userStats'])->name('user-stats');
        Route::get('/debug-user', [DashboardController::class, 'debugUser'])->name('debug-user');
    });
    
    // ============================================================
    // ROUTES NOTIFICATIONS (NotificationController) - Authentification requise
    // ============================================================
    Route::middleware(['web', 'auth:sanctum', 'force.password.change'])->prefix('notifications')->name('notifications.')->group(function () {
        Route::get('/', [NotificationController::class, 'index'])->name('index');
        Route::get('/unread-count', [NotificationController::class, 'unreadCount'])->name('unread-count');
        
        // Routes avec middleware supplémentaire pour vérifier l'appartenance des notifications
        Route::middleware('ensure.notification.belongs.to.user')->group(function () {
            Route::patch('/{notification}/read', [NotificationController::class, 'markAsRead'])->name('mark-as-read');
            Route::delete('/{notification}', [NotificationController::class, 'destroy'])->name('destroy');
        });
        
        Route::patch('/mark-all-read', [NotificationController::class, 'markAllAsRead'])->name('mark-all-as-read');
    });
    
    // ============================================================
    // ROUTES GESTION DES UTILISATEURS (UserManagementController) - Admin uniquement
    // ============================================================
    Route::middleware(['web', 'auth:sanctum', 'force.password.change'])->prefix('users')->name('users.')->group(function () {
        Route::get('/', [UserManagementController::class, 'index'])->name('index');
        Route::get('/commercials', [UserManagementController::class, 'getCommercials'])->name('commercials');
        Route::post('/bulk-import', [UserManagementController::class, 'bulkImport'])->name('bulk-import');
        Route::get('/import-template', [UserManagementController::class, 'downloadTemplate'])->name('import-template');
        Route::put('/{user}', [UserManagementController::class, 'update'])->name('update');
        Route::post('/{user}/reset-password', [UserManagementController::class, 'resetPassword'])->name('reset-password');
        Route::post('/{user}/toggle-block', [UserManagementController::class, 'toggleBlock'])->name('toggle-block');
        Route::post('/{user}/toggle-active', [UserManagementController::class, 'toggleActive'])->name('toggle-active');
        Route::get('/{user}/stats', [UserManagementController::class, 'userStats'])->name('stats');
    });
    
    // ============================================================
    // ROUTES GESTION DES OBJECTIFS (ObjectiveController) - Admin uniquement
    // ============================================================
    Route::middleware(['web', 'auth:sanctum', 'force.password.change'])->prefix('objectives')->name('objectives.')->group(function () {
        Route::get('/', [ObjectiveController::class, 'index'])->name('index');
        Route::post('/', [ObjectiveController::class, 'store'])->name('store');
        Route::get('/current', [ObjectiveController::class, 'getCurrentUserObjective'])->name('current');
        Route::get('/progress-stats', [ObjectiveController::class, 'progressStats'])->name('progress-stats');
        Route::post('/bulk-set', [ObjectiveController::class, 'setBulkObjectives'])->name('bulk-set');
        Route::get('/{objective}', [ObjectiveController::class, 'show'])->name('show');
        Route::put('/{objective}', [ObjectiveController::class, 'update'])->name('update');
        Route::delete('/{objective}', [ObjectiveController::class, 'destroy'])->name('destroy');
    });
    
    // Route de test pour vérifier FormData
    Route::post('/test-form', function (Request $request) {
        $documentFields = ['id_card', 'anid_card', 'residence_card', 'residence_proof', 'business_document', 'cfe_document', 'nif_document'];
        $hasFiles = [];
        $fileDetails = [];
        
        foreach ($documentFields as $field) {
            $hasFiles[$field] = $request->hasFile($field);
            if ($request->hasFile($field)) {
                $file = $request->file($field);
                $fileDetails[$field] = [
                    'name' => $file->getClientOriginalName(),
                    'size' => $file->getSize(),
                    'mime' => $file->getMimeType()
                ];
            }
        }
        
        return response()->json([
            'message' => 'Test route - Document upload test',
            'content_type' => $request->header('Content-Type'),
            'method' => $request->method(),
            'has_files' => $hasFiles,
            'file_details' => $fileDetails,
            'all_data_keys' => array_keys($request->all()),
            'input_count' => count($request->all()),
            'total_files_found' => count($fileDetails)
        ]);
    });
    
    // Public route to test merchant application creation without auth
    Route::post('/test-merchant-application', [MerchantApplicationController::class, 'store']);
});
// Route de test pour les documents
Route::get('/test-documents', function () {
    $documents = \App\Models\ApplicationDocument::with('verifier')->limit(5)->get();
    return response()->json(['data' => $documents]);
});

// Route de test pour v�rifier un document
Route::post('/test-verify-document/{id}', function ($id) {
    $document = \App\Models\ApplicationDocument::find($id);
    if (!$document) return response()->json(['error' => 'Document non trouv�'], 404);
    $document->is_verified = true;
    $document->verified_at = now();
    $document->verified_by = 1; // User ID de test
    $document->save();
    return response()->json(['success' => true, 'message' => 'Document v�rifi�', 'data' => $document]);
});

// Route simple pour v�rifier
Route::post('/simple-verify/{id}', function ($id) {
    try {
        $document = \App\Models\ApplicationDocument::find($id);
        if (!$document) return response()->json(['error' => 'Document non trouv�'], 404);
        $document->is_verified = true;
        $document->verified_at = now();
        $document->save();
        return response()->json(['success' => true]);
    } catch (\Exception $e) {
        return response()->json(['error' => $e->getMessage()], 500);
    }
});

// Route de test pour v�rifier avec auth simul�e
Route::post('/verify-test/{id}', function ($id) {
    $document = \App\Models\ApplicationDocument::find($id);
    if (!$document) return response()->json(['error' => 'Document non trouv�'], 404);
    $document->is_verified = true;
    $document->verified_at = now();
    $document->verified_by = 1;
    $document->verification_notes = 'V�rification manuelle depuis interface';
    $document->save();
    return response()->json(['success' => true, 'message' => 'Document v�rifi� avec succ�s', 'data' => new \App\Http\Resources\ApplicationDocumentResource($document)]);
});

// Route ultra-simple pour v�rifier
Route::post('/ultra-verify/{id}', function ($id) {
    $document = \App\Models\ApplicationDocument::find($id);
    if (!$document) return response()->json(['error' => 'Document non trouv�'], 404);
    $document->update(['is_verified' => true, 'verified_at' => now(), 'verified_by' => 1]);
    return response()->json(['success' => true, 'document_id' => $document->id, 'verified' => $document->is_verified]);
});

// Route de test pour les notifications
Route::get('/test-notifications', function () {
    $user = \App\Models\User::first();
    if (!$user) {
        return response()->json(['error' => 'Aucun utilisateur trouv�']);
    }
    
    $notification = \App\Models\Notification::create([
        'user_id' => $user->id,
        'type' => 'test',
        'title' => 'Test de notification',
        'message' => 'Ceci est un test de notification',
        'data' => json_encode(['test' => true]),
        'priority' => 'normal'
    ]);
    
    return response()->json(['success' => true, 'notification' => $notification, 'user' => $user]);
});

// Route de test de diagnostic pour les notifications
Route::get('/test-notifications-diagnosis', function () {
    try {
        $user = auth()->user();
        
        if (!$user) {
            return response()->json([
                'error' => 'Non authentifié'
            ], 401);
        }
        
        // Test simple de récupération des notifications
        $notifications = $user->notifications()
            ->notExpired()
            ->orderBy('created_at', 'desc')
            ->limit(10)
            ->get();
            
        return response()->json([
            'success' => true,
            'user_id' => $user->id,
            'user_name' => $user->first_name . ' ' . $user->last_name,
            'user_roles' => $user->roles->pluck('slug'),
            'notifications_count' => $notifications->count(),
            'notifications' => $notifications->map(function($n) {
                return [
                    'id' => $n->id,
                    'type' => $n->type,
                    'title' => $n->title,
                    'is_read' => $n->isRead(),
                    'created_at' => $n->created_at
                ];
            })
        ]);
        
    } catch (\Exception $e) {
        return response()->json([
            'error' => 'Erreur lors du diagnostic: ' . $e->getMessage(),
            'trace' => $e->getTraceAsString()
        ], 500);
    }
})->middleware(['auth:sanctum']);

// Route de test pour approuver une candidature
Route::post('/test-approve/{id}', function ($id) {
    $application = \App\Models\MerchantApplication::findOrFail($id);
    $application->status = 'approved';
    $application->approved_at = now();
    $application->save();
    
    $notificationService = app(\App\Services\NotificationService::class);
    $notificationService->notifyApplicationApproved($application);
    
    return response()->json(['success' => true, 'message' => 'Application approved and notification sent']);
});

// Route de test simple pour notifications
Route::get('/test-simple-notification', function () {
    try {
        $user = \App\Models\User::first();
        $notification = \App\Models\Notification::create([
            'user_id' => $user->id,
            'type' => 'test',
            'title' => 'Test notification',
            'message' => 'Ceci est une notification de test',
            'data' => json_encode(['test' => true])
        ]);
        return response()->json(['success' => true, 'notification' => $notification]);
    } catch (\Exception $e) {
        return response()->json(['error' => $e->getMessage()]);
    }
});

// Route de test pour lister les notifications
Route::get('/test-list-notifications', function () {
    $notifications = \App\Models\Notification::with('user')->latest()->limit(10)->get();
    return response()->json(['notifications' => $notifications]);
});

// Route pour nettoyer les notifications de test
Route::delete('/clean-test-notifications', function () {
    $deleted = \App\Models\Notification::where('type', 'test')->delete();
    return response()->json(['message' => "Supprimé $deleted notifications de test"]);
});

// Test simple d'approbation sans notification
Route::post('/test-simple-approve/{id}', function ($id) {
    try {
        $application = \App\Models\MerchantApplication::findOrFail($id);
        
        $application->update([
            'status' => 'approved',
            'approved_at' => now(),
            'approved_by' => 1
        ]);
        
        return response()->json(['message' => 'Application approuvée (sans notification)', 'status' => $application->status]);
    } catch (\Exception $e) {
        return response()->json(['error' => $e->getMessage()], 500);
    }
});

// Route de test pour rejet avec notification
Route::post('/test-reject-with-notification/{id}', function ($id) {
    try {
        $application = \App\Models\MerchantApplication::findOrFail($id);
        
        if ($application->status === 'rejected') {
            return response()->json(['message' => 'Application déjà rejetée']);
        }
        
        $reason = 'Documents incomplets - test de notification';
        
        // Mettre à jour le statut
        $application->update([
            'status' => 'rejected',
            'rejected_at' => now(),
            'rejected_by' => 1,
            'rejected_reason' => $reason
        ]);
        
        // Recharger l'application
        $application->refresh();
        
        // Envoyer la notification
        $notificationService = app(\App\Services\NotificationService::class);
        $notificationService->notifyApplicationRejected($application, $reason);
        
        return response()->json([
            'message' => 'Application rejetée et notification envoyée',
            'application' => $application->load('user')
        ]);
        
    } catch (\Exception $e) {
        return response()->json([
            'error' => $e->getMessage(),
            'trace' => $e->getTraceAsString()
        ], 500);
    }
});

// Route de test pour approbation avec notification
Route::post('/test-approve-with-notification/{id}', function ($id) {
    try {
        $application = \App\Models\MerchantApplication::findOrFail($id);
        
        // S'assurer qu'elle n'est pas déjà approuvée
        if ($application->status === 'approved') {
            return response()->json(['message' => 'Application déjà approuvée']);
        }
        
        // Mettre à jour le statut d'abord
        $application->update([
            'status' => 'approved',
            'approved_at' => now(),
            'approved_by' => 1 // Simuler admin user ID 1
        ]);
        
        // Recharger l'application pour avoir les nouvelles données
        $application->refresh();
        
        // Envoyer la notification
        $notificationService = app(\App\Services\NotificationService::class);
        $notificationService->notifyApplicationApproved($application);
        
        return response()->json([
            'message' => 'Application approuvée et notification envoyée',
            'application' => $application->load('user')
        ]);
        
    } catch (\Exception $e) {
        return response()->json([
            'error' => $e->getMessage(),
            'trace' => $e->getTraceAsString()
        ], 500);
    }
});

// Route de test pour simuler l'API notifications sans auth
Route::get('/test-notifications-api', function () {
    try {
        $user = \App\Models\User::first(); // Simulate user 1
        if (!$user) {
            return response()->json(['error' => 'No users found']);
        }
        
        $notifications = $user->notifications()
            ->latest()
            ->limit(10)
            ->get();
            
        return response()->json([
            'data' => $notifications,
            'meta' => [
                'total' => $notifications->count(),
                'unread_count' => $user->notifications()->whereNull('read_at')->count()
            ]
        ]);
    } catch (\Exception $e) {
        return response()->json(['error' => $e->getMessage(), 'trace' => $e->getTraceAsString()]);
    }
});

// Route de test pour marquer une notification comme lue (sans middleware)
Route::get('/test-mark-notification-read/{id}', function ($id) {
    $user = auth()->user();
    if (!$user) {
        return response()->json(['error' => 'Non authentifié'], 401);
    }
    
    try {
        $notification = \App\Models\Notification::find($id);
        
        if (!$notification) {
            return response()->json(['error' => 'Notification non trouvée'], 404);
        }
        
        if ($notification->user_id !== $user->id) {
            return response()->json(['error' => 'Non autorisé'], 403);
        }
        
        $notification->read_at = now();
        $notification->save();
        
        return response()->json([
            'success' => 'Notification marquée comme lue', 
            'notification' => [
                'id' => $notification->id,
                'type' => $notification->type,
                'is_read_before' => $notification->getOriginal('read_at') !== null,
                'is_read_after' => $notification->isRead(),
                'read_at' => $notification->read_at
            ]
        ]);
    } catch (\Exception $e) {
        return response()->json(['error' => $e->getMessage(), 'trace' => $e->getTraceAsString()], 500);
    }
})->middleware(['web', 'auth:sanctum']);

// Route de test pour créer une notification
Route::get('/create-test-notification', function () {
    $user = auth()->user();
    if (!$user) {
        return response()->json(['error' => 'Non authentifié'], 401);
    }
    
    try {
        $notification = \App\Models\Notification::create([
            'id' => \Illuminate\Support\Str::uuid(),
            'type' => 'test_notification',
            'notifiable_type' => 'App\\Models\\User',
            'notifiable_id' => $user->id,
            'user_id' => $user->id,
            'data' => json_encode(['message' => 'Test notification for marking as read']),
            'read_at' => null,
        ]);
        
        return response()->json([
            'success' => 'Notification créée',
            'notification' => $notification
        ]);
    } catch (\Exception $e) {
        return response()->json(['error' => $e->getMessage()], 500);
    }
})->middleware(['web', 'auth:sanctum']);
