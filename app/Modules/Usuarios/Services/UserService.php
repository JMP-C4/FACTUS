<?php

namespace App\Modules\Usuarios\Services;

use App\Models\Branch;
use App\Models\Company;
use App\Models\User;
use App\Modules\AccessControl\Models\Permission;
use App\Modules\AccessControl\Models\Role;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class UserService
{
    /**
     * Listado paginado de usuarios con filtros.
     *
     * @param  array<string, mixed>  $filters
     */
    public function list(?int $companyId, array $filters = []): LengthAwarePaginator
    {
        $query = User::query()->with(['company', 'branch', 'roles']);

        if ($companyId !== null) {
            $query->where('company_id', $companyId);
        }

        if (! empty($filters['search'])) {
            $term = $filters['search'];
            $query->where(function ($q) use ($term): void {
                $q->where('name', 'like', "%{$term}%")
                    ->orWhere('email', 'like', "%{$term}%");
            });
        }

        if (! empty($filters['role'])) {
            $query->whereHas('roles', fn ($q) => $q->where('name', $filters['role']));
        }

        return $query->orderBy('name')->paginate(15);
    }

    /**
     * Crea un usuario y le asigna su rol/alcance.
     *
     * @param  array<string, mixed>  $data
     */
    public function create(array $data): User
    {
        return DB::transaction(function () use ($data): User {
            $roleId = $data['role_id'] ?? null;

            $user = User::create([
                'name' => $data['name'],
                'email' => $data['email'],
                'password' => $data['password'],
                'company_id' => $data['company_id'] ?? null,
                'branch_id' => $data['branch_id'] ?? null,
            ]);

            if ($roleId) {
                $this->assignRole($user, (int) $roleId, $data['company_id'] ?? null, $data['branch_id'] ?? null);
            }

            return $user;
        });
    }

    /**
     * Actualiza un usuario y, si aplica, su rol/alcance.
     *
     * @param  array<string, mixed>  $data
     */
    public function update(User $user, array $data): User
    {
        return DB::transaction(function () use ($user, $data): User {
            $attributes = [
                'name' => $data['name'],
                'email' => $data['email'],
                'company_id' => $data['company_id'] ?? null,
                'branch_id' => $data['branch_id'] ?? null,
            ];

            if (! empty($data['password'])) {
                $attributes['password'] = $data['password'];
            }

            $user->update($attributes);

            $this->syncRole($user, $data['role_id'] ?? null, $data['company_id'] ?? null, $data['branch_id'] ?? null);

            return $user->fresh(['company', 'branch', 'roles']);
        });
    }

    /**
     * Elimina un usuario.
     */
    public function delete(User $user): bool
    {
        return $user->delete();
    }

    /**
     * Asigna un rol con alcance a un usuario.
     */
    public function assignRole(User $user, int $roleId, ?int $companyId, ?int $branchId): void
    {
        $user->userRoles()->create([
            'role_id' => $roleId,
            'company_id' => $companyId,
            'branch_id' => $branchId,
            'assigned_by' => Auth::id(),
        ]);
    }

    /**
     * Reemplaza el rol/alcance del usuario por la selección dada.
     */
    public function syncRole(User $user, ?int $roleId, ?int $companyId, ?int $branchId): void
    {
        $user->userRoles()->delete();

        if ($roleId) {
            $this->assignRole($user, $roleId, $companyId, $branchId);
        }
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
     * IDs de permisos heredados de los roles del usuario.
     *
     * @return array<int, int>
     */
    public function rolePermissionIds(User $user): array
    {
        return $user->roles()
            ->with('permissions')
            ->get()
            ->pluck('permissions')
            ->flatten()
            ->pluck('id')
            ->unique()
            ->values()
            ->all();
    }

    /**
     * IDs de permisos asignados directamente al usuario.
     *
     * @return array<int, int>
     */
    public function directPermissionIds(User $user): array
    {
        return $user->directPermissions()->pluck('permissions.id')->all();
    }

    /**
     * Sincroniza los permisos extra asignados directamente al usuario.
     * Sólo afecta a este usuario, nunca al rol ni a otros usuarios.
     *
     * @param  array<int, int|string>  $permissionIds
     */
    public function syncDirectPermissions(User $user, array $permissionIds): void
    {
        $user->directPermissions()->sync($permissionIds);
    }

    /**
     * IDs de permisos que el actor puede conceder a un usuario.
     * Devuelve null cuando no hay restricción (super admin).
     *
     * @return array<int, int>|null
     */
    public function grantablePermissionIds(User $actor, ?User $target = null): ?array
    {
        if ($actor->isSuperAdmin()) {
            return null;
        }

        $ids = $actor->getAllPermissions()->pluck('id')->map(fn ($id) => (int) $id);

        if ($target !== null) {
            $ids = $ids->merge($this->directPermissionIds($target));
        }

        return $ids->unique()->values()->all();
    }

    /**
     * Roles disponibles para asignación.
     */
    public function roles(): Collection
    {
        return Role::query()->orderBy('name')->get();
    }

    /**
     * Compañías disponibles para asignación.
     */
    public function companies(): Collection
    {
        return Company::query()->orderBy('name')->get(['id', 'name']);
    }

    /**
     * Sucursales disponibles para asignación.
     */
    public function branches(): Collection
    {
        return Branch::query()->orderBy('name')->get(['id', 'company_id', 'name']);
    }
}
