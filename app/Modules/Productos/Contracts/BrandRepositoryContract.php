<?php

namespace App\Modules\Productos\Contracts;

use App\Modules\Productos\Models\Brand;
use Illuminate\Support\Collection;

interface BrandRepositoryContract
{
    /**
     * Obtiene marcas activas de una compañía.
     */
    public function getActive(?int $companyId): Collection;

    /**
     * Obtiene una marca por ID y compañía.
     */
    public function find(int $id, int $companyId): ?Brand;
}
