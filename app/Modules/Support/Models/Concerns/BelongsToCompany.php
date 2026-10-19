<?php

declare(strict_types=1);

namespace App\Modules\Support\Models\Concerns;

use Illuminate\Database\Eloquent\Builder;

/**
 * Centraliza el filtrado multi-tenant por compañía compartido por los modelos
 * de negocio. Un `company_id` nulo representa alcance global (sin filtro), lo
 * que permite que el super admin consulte todas las compañías desde el mismo
 * contrato de repositorio.
 */
trait BelongsToCompany
{
    public function scopeForCompany(Builder $query, ?int $companyId): Builder
    {
        return $query->when(
            $companyId !== null,
            fn (Builder $q): Builder => $q->where('company_id', $companyId),
        );
    }
}
