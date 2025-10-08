<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class ForceJsonResponse
{
    public function handle(Request $request, Closure $next): Response
    {
        // Forcer Accept à application/json pour les réponses API
        $request->headers->set('Accept', 'application/json');
        
        // Ne forcer Content-Type que si ce n'est pas multipart/form-data
        $contentType = $request->header('Content-Type', '');
        
        if (strpos($contentType, 'multipart/form-data') === false) {
            $request->headers->set('Content-Type', 'application/json');
        }
        
        return $next($request);
    }
}