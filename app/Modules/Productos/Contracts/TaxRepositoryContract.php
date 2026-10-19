<?php

declare(strict_types=1);

namespace App\Modules\Productos\Contracts;

use App\Modules\Productos\Models\Tax;
use Illuminate\Support\Collection;

interface TaxRepositoryContract
{
    /**
     * Obtiene impuestos activos visibles para una compañía (globales + propios).
     */
    public function getActive(?int $companyId): Collection;

    /**
     * Obtiene un impuesto por ID dentro del alcance de una compañía.
     */
    public function find(int $id, ?int $companyId): ?Tax;
}
