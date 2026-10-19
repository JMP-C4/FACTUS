<?php

namespace App\Modules\Productos\Contracts;

use Illuminate\Support\Collection;

interface ProductCatalogContract
{
    /**
     * Busca productos activos por término de búsqueda.
     */
    public function search(?int $companyId, string $term): Collection;

    /**
     * Busca un producto por código de barras.
     */
    public function findByBarcode(?int $companyId, string $barcode): ?object;

    /**
     * Obtiene datos de un producto para facturación.
     */
    public function getForInvoicing(int $productId, int $companyId): ?object;

    /**
     * Obtiene productos activos de una compañía.
     */
    public function getActive(?int $companyId): Collection;
}
