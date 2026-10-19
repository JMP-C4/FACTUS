<?php

namespace App\Models;

use App\Modules\AccessControl\Models\Permission;
use App\Modules\AccessControl\Models\Role;
use App\Modules\AccessControl\Models\UserRole;
use App\Modules\AccessControl\Traits\HasPermissions;
// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

// Campos asignables masivamente
#[Fillable(['name', 'email', 'password', 'company_id', 'branch_id'])]
// Campos ocultos en respuestas
#[Hidden(['password', 'remember_token'])]
class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, HasPermissions, Notifiable;

    // Relación: Un usuario pertenece a una compañía (nullable para super_admins globales)
    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    // Relación: Un usuario pertenece a una sucursal (nullable para usuarios con acceso a todas las sucursales)
    public function branch(): BelongsTo
    {
        return $this->belongsTo(Branch::class);
    }

    public function isGloballyScoped(): bool
    {
        return $this->isSuperAdmin() && is_null($this->company_id);
    }

    public function isCompanyScoped(): bool
    {
        return ! is_null($this->company_id) && is_null($this->branch_id);
    }

    public function isBranchScoped(): bool
    {
        return ! is_null($this->branch_id);
    }

    // Asignación completa de roles (con contexto)
    public function userRoles(): HasMany
    {
        return $this->hasMany(UserRole::class);
    }

    // Roles con scope visible (empresa/sucursal)
    public function roles(): BelongsToMany
    {
        return $this->belongsToMany(
            Role::class,
            'user_roles',
            'user_id',
            'role_id'
        )->withPivot([
            'company_id',
            'branch_id',
            'assigned_by',
        ])->withTimestamps();
    }

    // Permisos extra asignados directamente (solo para este usuario)
    public function directPermissions(): BelongsToMany
    {
        return $this->belongsToMany(
            Permission::class,
            'permission_user',
            'user_id',
            'permission_id'
        );
    }

    // Verifica si tiene un rol
    public function hasRole(string $roleName): bool
    {
        return $this->roles()->where('name', $roleName)->exists();
    }

    // Verifica si tiene al menos uno de los roles indicados
    public function hasAnyRole(array $roleNames): bool
    {
        return $this->roles()->whereIn('name', $roleNames)->exists();
    }

    // ¿Es super admin?
    public function isSuperAdmin(): bool
    {
        return $this->hasRole('super_admin');
    }

    // Convierte tipos especiales
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }
}
