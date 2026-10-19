<?php

namespace App\Modules\AccessControl\Services;

use App\Modules\AccessControl\Models\Permission;
use App\Modules\AccessControl\Models\Role;
use Illuminate\Support\Collection;

class RoleService
{
    /**
     * Lista de roles con conteos de permisos y usuarios asignados.
     */
    public function list(): Collection
    {
        return Role::query()
            ->withCount(['permissions', 'userRoles'])
            ->orderBy('name')
            ->get();
    }

    /**
     * Permisos del sistema agrupados por módulo funcional.
     */
    public function permissionsGroupedByModule(): Collection
    {
        return Permission::query()
            ->orderBy('name')
            ->get()
            ->groupBy('module');
    }

    /**
     * Sincroniza los permisos asignados a un rol.
     *
     * @param  array<int, int|string>  $permissionIds
     */
    public function syncPermissions(Role $role, array $permissionIds): void
    {
        $role->permissions()->sync($permissionIds);
    }
}
