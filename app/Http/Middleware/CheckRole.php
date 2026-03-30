<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckRole
{
    /**
     * Verifica se o usuário possui um dos perfis permitidos.
     * Uso: middleware('role:admin_global,admin_cliente')
     */
    public function handle(Request $request, Closure $next, string ...$roles): Response
    {
        $user = $request->user();

        if (!$user || !in_array($user->role, $roles)) {
            if ($request->expectsJson() || $request->header('X-Inertia')) {
                abort(403, 'Acesso não autorizado.');
            }

            return redirect()->route('dashboard')
                ->with('error', 'Você não tem permissão para acessar esta área.');
        }

        return $next($request);
    }
}
