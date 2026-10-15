<?php

namespace App\Modules\AccessControl\Traits;

use App\Modules\AccessControl\Models\Permission;
use Illuminate\Auth\Access\AuthorizationException;

/**
 * HasPermissions Trait
 *
 * Proporciona métodos de autorización y verificación de permisos.
 * Uso: hasPermission(), hasAllPermissions(), hasAnyPermission(), authorize(),
 * getAllPermissions(), getAccessibleModules()
 */
trait HasPermissions
{
    // ¿Tengo este permiso? (super_admin = acceso total)
    public function hasPermission(string $permissionName): bool
    {
        // Cortocircuito: super_admin acceso total
        if ($this->isSuperAdmin()) {
            return true;
        }

        // Busca en mis roles si alguno tiene el permiso
        return $this->roles()
            ->whereHas('permissions', function ($query) use ($permissionName) {
                $query->where('name', $permissionName);
            })
            ->exists();
    }

    // ¿Tengo TODOS estos permisos?
    public function hasAllPermissions(array $permissionNames): bool
    {
        // Cortocircuito para super_admin.
        if ($this->isSuperAdmin()) {
            return true;
        }

        // Verifica cada permiso individualmente.
        foreach ($permissionNames as $permissionName) {
            if (! $this->hasPermission($permissionName)) {
                return false;
            }
        }

        return true;
    }

    // ¿Tengo AL MENOS UNO de estos permisos?
    public function hasAnyPermission(array $permissionNames): bool
    {
        // Cortocircuito para super_admin.
        if ($this->isSuperAdmin()) {
            return true;
        }

        // Verifica cada permiso hasta encontrar uno.
        foreach ($permissionNames as $permissionName) {
            if ($this->hasPermission($permissionName)) {
                return true;
            }
        }

        return false;
    }

    // Verifica permiso o lanza excepción
    public function authorize(string $permissionName): bool
    {
        if (! $this->hasPermission($permissionName)) {
            throw new AuthorizationException(
                "No autorizado: {$permissionName}"
            );
        }

        return true;
    }

    // Lista todos mis permisos (útil para auditoría)
    public function getAllPermissions()
    {
        // Si es super_admin → todos los permisos del sistema
        if ($this->isSuperAdmin()) {
            return Permission::all();
        }

        // De lo contrario, retorna permisos de mis roles
        return $this->roles()
            ->with('permissions')
            ->get()
            ->pluck('permissions')
            ->flatten()
            ->unique('id');
    }

    // Obtiene módulos a los que tengo acceso (para menús)
    public function getAccessibleModules(): array
    {
        return $this->getAllPermissions()
            ->pluck('module')
            ->unique()
            ->values()
            ->toArray();
    }
}
