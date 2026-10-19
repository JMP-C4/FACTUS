<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

abstract class Controller
{
    /**
     * Compañía activa del usuario autenticado.
     * Devuelve null cuando el usuario tiene alcance global (super_admin sin compañía).
     */
    protected function currentCompanyId(Request $request): ?int
    {
        return $request->user()?->company_id;
    }
}
