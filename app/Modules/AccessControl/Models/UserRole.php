<?php

namespace App\Modules\AccessControl\Models;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class UserRole extends Model
{
    // Tabla explícita: user_roles
    protected $table = 'user_roles';

    // Campos: usuario, rol, scope (company/branch) y quién asignó
    protected $fillable = [
        'user_id',
        'role_id',
        'company_id',
        'branch_id',
        'assigned_by',
    ];

    // ¿A quién se le asignó este rol?
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    // ¿Cuál es el rol asignado?
    public function role(): BelongsTo
    {
        return $this->belongsTo(Role::class);
    }

    // ¿Quién hizo la asignación? (para auditoría)
    public function assignedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_by');
    }
}
