<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Database\Eloquent\Model;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }
    
    public function boot(): void
    {
        // Laravel 12 : Prevent lazy loading en développement
        Model::preventLazyLoading(!app()->isProduction());
        
        // Prevent silently discarding attributes
        Model::preventSilentlyDiscardingAttributes(!app()->isProduction());
        
        // Prevent accessing missing attributes
        Model::preventAccessingMissingAttributes(!app()->isProduction());
        
        // Rate Limiting personnalisé
        RateLimiter::for('api', function (Request $request) {
            return Limit::perMinute(60)->by($request->user()?->id ?: $request->ip())
                ->response(function (Request $request, array $headers) {
                    return response()->json([
                        'message' => 'Trop de requêtes. Veuillez réessayer dans quelques instants.',
                        'retry_after' => $headers['Retry-After'] ?? 60
                    ], 429, $headers);
                });
        });
        
        // Rate limiting stricte pour uploads
        RateLimiter::for('uploads', function (Request $request) {
            return Limit::perMinute(10)->by($request->ip())
                ->response(function () {
                    return response()->json([
                        'message' => 'Trop d\'uploads. Attendez avant de réessayer.',
                    ], 429);
                });
        });
    }
}