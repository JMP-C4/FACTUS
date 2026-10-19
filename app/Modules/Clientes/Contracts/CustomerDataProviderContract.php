<?php

namespace App\Modules\Clientes\Contracts;

use Illuminate\Support\Collection;

interface CustomerDataProviderContract
{
    /**
     * Busca clientes por término de búsqueda.
     */
    public function search(?int $companyId, string $term): Collection;

    /**
     * Obtiene datos de un cliente para facturación.
     */
    public function getForInvoicing(int $customerId, int $companyId): ?object;

    /**
     * Obtiene clientes facturables de una compañía.
     */
    public function getFacturable(?int $companyId): Collection;

    /**
     * Verifica si un cliente tiene datos completos para facturación.
     */
    public function isFacturable(int $customerId, int $companyId): bool;
}
