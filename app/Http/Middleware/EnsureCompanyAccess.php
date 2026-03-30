<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureCompanyAccess
{
    /**
     * Garante que o usuário só acesse dados da sua empresa (multi-tenant).
     * Admin Global tem acesso irrestrito.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if (!$user) {
            abort(403);
        }

        // Admin Global tem acesso total
        if ($user->isAdminGlobal()) {
            return $next($request);
        }

        // Verifica se há company_id na rota ou request
        $companyId = $request->route('company')
            ?? $request->input('company_id')
            ?? $user->company_id;

        if ($companyId && !$user->canAccessCompany((int) $companyId)) {
            abort(403, 'Você não tem acesso aos dados desta empresa.');
        }

        return $next($request);
    }
}
