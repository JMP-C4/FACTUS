<?php

namespace App\Modules\AccessControl\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Role extends Model
{
    // Campos asignables (name, scope_type, description)
    protected $fillable = [
        'name',
        'scope_type',
        'description',
    ];

    // ¿Qué permisos tiene este rol?
    public function permissions(): BelongsToMany
    {
        return $this->belongsToMany(
            Permission::class,
            'permission_role',
            'role_id',
            'permission_id'
        );
    }

    // ¿Qué usuarios tienen este rol asignado?
    public function userRoles(): HasMany
    {
        return $this->hasMany(UserRole::class);
    }
}
