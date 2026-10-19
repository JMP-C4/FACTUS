<?php

namespace App\Modules\Productos\Contracts;

use App\Modules\Productos\Models\Category;
use Illuminate\Support\Collection;

interface CategoryRepositoryContract
{
    /**
     * Obtiene categorías activas de una compañía.
     */
    public function getActive(?int $companyId): Collection;

    /**
     * Obtiene una categoría por ID y compañía.
     */
    public function find(int $id, int $companyId): ?Category;
}
