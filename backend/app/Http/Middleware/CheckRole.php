<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class CheckRole
{
    public function handle(Request $request, Closure $next, ...$roles)
    {
        Log::info('CheckRole middleware', [
            'roles' => $roles,
            'user' => $request->user(),
            'user_roles' => $request->user() ? $request->user()->roles->pluck('slug') : null
        ]);

        if (!$request->user() || !$request->user()->roles()->whereIn('slug', $roles)->exists()) {
            return response()->json(['message' => 'Action non autorisée'], 403);
        }

        return $next($request);
    }
}