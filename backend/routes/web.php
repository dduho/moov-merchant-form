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
