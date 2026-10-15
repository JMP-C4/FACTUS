<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class ScopeCompanyBranch
{
    /**
     * Handle an incoming request.
     * Aplica scope global de compañía/sucursal a las consultas de Eloquent
     * basándose en el usuario autenticado.
     *
     * @param  Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Solo aplicar si hay un usuario autenticado
        if (Auth::check()) {
            $user = Auth::user();

            // Si el usuario tiene alcance global (super_admin sin compañía), no aplicar filtros
            if ($user->isGloballyScoped()) {
                return $next($request);
            }

            // Si el usuario tiene alcance a nivel de compañía, filtrar por compañía
            if ($user->isCompanyScoped()) {
                // Agregar scope global a modelos que tengan company_id
                // Esto se implementará en cada modelo que necesite scoping
                // Por ahora, almacenamos en el request para que los modelos lo usen
                $request->attributes->set('scoped_company_id', $user->company_id);
            }

            // Si el usuario tiene alcance a nivel de sucursal, filtrar por sucursal
            if ($user->isBranchScoped()) {
                $request->attributes->set('scoped_branch_id', $user->branch_id);
                $request->attributes->set('scoped_company_id', $user->company_id);
            }
        }

        return $next($request);
    }
}
