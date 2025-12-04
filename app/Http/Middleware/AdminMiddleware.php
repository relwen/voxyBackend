<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Facades\Log;

class AdminMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();
        
        // Log pour déboguer
        Log::info('AdminMiddleware - Vérification accès', [
            'path' => $request->path(),
            'user_id' => $user?->id,
            'user_role' => $user?->role,
            'user_email' => $user?->email,
        ]);

        if (!$user || $user->role !== 'admin') {
            return response()->json([
                'success' => false,
                'message' => 'Accès refusé. Droits d\'administrateur requis.'
            ], 403);
        }

        return $next($request);
    }
}
