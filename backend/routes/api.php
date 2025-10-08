<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Http\Request;
use App\Http\Controllers\{
    MerchantApplicationController,
    DocumentController,
    DashboardController,
    AuthController
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
    });
});

// Health check
Route::get('/health', fn() => response()->json([
    'status' => 'healthy',
    'service' => 'Moov Merchant API',
    'version' => '1.0.0',
    'timestamp' => now()->toISOString(),
]));

// Groupe API avec rate limiting
Route::middleware(['throttle:api'])->group(function () {
    
    // ============================================================
    // ROUTES MERCHANT APPLICATIONS (MerchantApplicationController)
    // ============================================================
    Route::prefix('merchant-applications')->name('merchant-applications.')->group(function () {
        // Routes publiques (consultation)
        Route::get('/', [MerchantApplicationController::class, 'index'])->name('index');
        Route::get('/reference/{reference}', [MerchantApplicationController::class, 'showByReference'])->name('show-by-reference');
        Route::get('/statistics/all', [MerchantApplicationController::class, 'statistics'])->name('statistics');
        
        // Routes nécessitant une authentification
        Route::middleware(['web', 'auth:sanctum'])->group(function () {
            Route::post('/', [MerchantApplicationController::class, 'store'])->name('store');
            Route::put('/{merchantApplication}', [MerchantApplicationController::class, 'update'])->name('update');
            Route::put('/{merchantApplication}/full', [MerchantApplicationController::class, 'fullUpdate'])->name('full-update');
            Route::delete('/{merchantApplication}', [MerchantApplicationController::class, 'destroy'])->name('destroy');
            
            // Routes administratives nécessitant une authentification
            Route::post('/{merchantApplication}/status', [MerchantApplicationController::class, 'updateStatus'])->name('update-status');
            Route::post('/{merchantApplication}/restore', [MerchantApplicationController::class, 'restore'])->name('restore');
            Route::post('/{merchantApplication}/approve', [MerchantApplicationController::class, 'approve'])->name('approve');
            Route::post('/{merchantApplication}/reject', [MerchantApplicationController::class, 'reject'])->name('reject');
            Route::delete('/{merchantApplication}/force', [MerchantApplicationController::class, 'forceDestroy'])->name('force-destroy');
        });
        
        // Routes avec paramètres génériques (doivent être à la fin)
        Route::get('/{merchantApplication}', [MerchantApplicationController::class, 'show'])->name('show');
    });
    
    // ============================================================
    // ROUTES DOCUMENTS (DocumentController)
    // ============================================================
    Route::prefix('documents')->name('documents.')->group(function () {
        Route::get('/', [DocumentController::class, 'index'])->name('index');
        Route::post('/upload', [DocumentController::class, 'upload'])->name('upload');
        Route::get('/{document}', [DocumentController::class, 'show'])->name('show');
        Route::get('/{document}/download', [DocumentController::class, 'download'])->name('download');
        Route::delete('/{document}', [DocumentController::class, 'destroy'])->name('destroy');
        Route::post('/{document}/verify', [DocumentController::class, 'verify'])->name('verify');
        Route::post('/{document}/unverify', [DocumentController::class, 'unverify'])->name('unverify');
        Route::get('/{document}/integrity', [DocumentController::class, 'checkIntegrity'])->name('check-integrity');
        Route::get('/{document}/metadata', [DocumentController::class, 'metadata'])->name('metadata');
    });
    
    // ============================================================
    // ROUTES DASHBOARD (DashboardController) - Authentification requise
    // ============================================================
    Route::middleware(['web', 'auth:sanctum'])->prefix('dashboard')->name('dashboard.')->group(function () {
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