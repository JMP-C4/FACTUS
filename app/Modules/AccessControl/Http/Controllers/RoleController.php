<?php

namespace App\Modules\AccessControl\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\AccessControl\Http\Requests\UpdateRolePermissionsRequest;
use App\Modules\AccessControl\Models\Role;
use App\Modules\AccessControl\Services\RoleService;
use App\Modules\Auditoria\Contracts\AuditLoggerContract;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class RoleController extends Controller
{
    public function __construct(
        private readonly RoleService $roleService,
        private readonly AuditLoggerContract $auditLogger,
    ) {}

    public function index(): View
    {
        return view('accesscontrol::roles.index', [
            'roles' => $this->roleService->list(),
        ]);
    }

    public function show(Role $role): View
    {
        $role->load('permissions');

        return view('accesscontrol::roles.show', [
            'role' => $role,
            'permissionsByModule' => $this->roleService->permissionsGroupedByModule(),
        ]);
    }

    public function edit(Role $role): View
    {
        $role->load('permissions');

        return view('accesscontrol::roles.edit', [
            'role' => $role,
            'permissionsByModule' => $this->roleService->permissionsGroupedByModule(),
        ]);
    }

    public function update(UpdateRolePermissionsRequest $request, Role $role): RedirectResponse
    {
        $this->roleService->syncPermissions($role, $request->input('permissions', []));

        $this->auditLogger->log('updated', 'access_control', "Permisos actualizados para rol: {$role->name}", $role);

        return redirect()
            ->route('roles.index')
            ->with('success', 'Permisos del rol actualizados exitosamente.');
    }
}
