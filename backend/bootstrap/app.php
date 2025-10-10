<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Illuminate\Validation\ValidationException;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        channels: __DIR__.'/../routes/channels.php',
        health: '/up',
        apiPrefix: 'api',
    )
    ->withMiddleware(function (Middleware $middleware) {
        // Laravel 12 : Middleware API personnalisés
        $middleware->api(prepend: [
            \App\Http\Middleware\ForceJsonResponse::class,
            \Illuminate\Http\Middleware\HandleCors::class,
        ]);
        
        // Alias de middleware
        $middleware->alias([
            'throttle.api' => \Illuminate\Routing\Middleware\ThrottleRequests::class.':api',
            'verified' => \Illuminate\Auth\Middleware\EnsureEmailIsVerified::class,
            'force.password.change' => \App\Http\Middleware\ForcePasswordChange::class,
        ]);
        
        // CORS - Laravel 12 gère mieux les CORS
        $middleware->validateCsrfTokens(except: [
            'api/*',
            'webhooks/*',
        ]);
        
        // Trust proxies (important pour production)
        $middleware->trustProxies(at: '*', headers: Request::HEADER_X_FORWARDED_FOR | 
            Request::HEADER_X_FORWARDED_HOST |
            Request::HEADER_X_FORWARDED_PORT |
            Request::HEADER_X_FORWARDED_PROTO);
    })
    ->withExceptions(function (Exceptions $exceptions) {
        // Laravel 12 : Gestion améliorée des exceptions
        
        // Validation
        $exceptions->render(function (ValidationException $e, Request $request) {
            if ($request->is('api/*')) {
                return response()->json([
                    'message' => 'Erreur de validation des données.',
                    'errors' => $e->errors(),
                    'status' => 422
                ], 422);
            }
        });
        
        // Model not found
        $exceptions->render(function (NotFoundHttpException $e, Request $request) {
            if ($request->is('api/*')) {
                return response()->json([
                    'message' => 'Ressource introuvable',
                    'status' => 404
                ], 404);
            }
        });
        
        // Erreurs générales API
        $exceptions->render(function (\Throwable $e, Request $request) {
            if ($request->is('api/*') && !($e instanceof ValidationException)) {
                $statusCode = method_exists($e, 'getStatusCode') 
                    ? $e->getStatusCode() 
                    : 500;
                
                return response()->json([
                    'message' => $e->getMessage() ?: 'Une erreur est survenue',
                    'status' => $statusCode,
                    'trace' => config('app.debug') ? $e->getTrace() : null
                ], $statusCode);
            }
        });
    })->create();