<?php

namespace App\Modules\Clientes\Contracts;

use App\Modules\Clientes\Models\Customer;
use Illuminate\Support\Collection;

interface CustomerRepositoryContract
{
    /**
     * Busca clientes por término de búsqueda.
     */
    public function search(?int $companyId, string $term): Collection;

    /**
     * Obtiene un cliente por ID y compañía.
     */
    public function find(int $id, int $companyId): ?Customer;

    /**
     * Obtiene un cliente por número de documento y compañía.
     */
    public function findByDocument(int $companyId, string $documentNumber): ?Customer;

    /**
     * Obtiene clientes facturables de una compañía.
     */
    public function getFacturable(int $companyId): Collection;
}
