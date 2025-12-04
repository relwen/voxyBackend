<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Facades\Log;

class MaestroMiddleware
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
        Log::info('MaestroMiddleware - Vérification accès', [
            'path' => $request->path(),
            'user_id' => $user?->id,
            'user_role' => $user?->role,
            'user_email' => $user?->email,
            'expects_json' => $request->expectsJson(),
            'wants_json' => $request->wantsJson(),
            'ajax' => $request->ajax(),
        ]);

        if (!$user) {
            if ($request->expectsJson() || $request->wantsJson() || $request->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Accès refusé. Vous devez être connecté.'
                ], 401);
            }
            
            return redirect('/login')->withErrors([
                'email' => 'Accès refusé. Vous devez être connecté.'
            ]);
        }

        if ($user->role !== 'maestro' && $user->role !== 'admin') {
            if ($request->expectsJson() || $request->wantsJson() || $request->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Accès refusé. Droits de maestro ou administrateur requis. (Rôle actuel: ' . ($user->role ?? 'non défini') . ')'
                ], 403);
            }
            
            return redirect('/login')->withErrors([
                'email' => 'Accès refusé. Droits de maestro ou administrateur requis.'
            ]);
        }

        return $next($request);
    }
}
