<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class RoleMiddleware
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next, $role): Response
    {
        // Verifica si el usuario está autenticado
        if (!$request->user()) {
            return response()->json(['message' => 'No autenticado'], 401);
        }

        // Verifica si el usuario tiene el rol requerido
        if ($request->user()->role !== $role) {
            return response()->json(['message' => 'No autorizado: acceso restringido'], 403);
        }

        return $next($request);
    }
}
