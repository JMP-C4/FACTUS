<?php

declare(strict_types=1);

namespace App\Modules\Inventario\Contracts;

use Symfony\Component\HttpKernel\Exception\HttpException;

/**
 * Punto de entrada cross-módulo para reflejar ventas en el inventario.
 *
 * Permite que módulos de negocio (por ejemplo Facturación) descuenten stock
 * sin acoplarse a los modelos internos del módulo de Inventario.
 */
interface SaleStockContract
{
    /**
     * Verifica que exista stock suficiente para las líneas de venta indicadas.
     *
     * Sólo se consideran los productos con registro de stock (productos
     * rastreados); las líneas sin producto o sin stock registrado se ignoran.
     *
     * @param  array<int, array{product_id: int|null, quantity: int}>  $lines
     *
     * @throws HttpException si falta stock.
     */
    public function assertAvailable(int $companyId, array $lines): void;

    /**
     * Descuenta del stock las líneas de venta indicadas.
     *
     * @param  array<int, array{product_id: int|null, quantity: int}>  $lines
     */
    public function deductForSale(
        int $companyId,
        array $lines,
        ?string $referenceType = null,
        ?int $referenceId = null,
        ?int $performedBy = null,
    ): void;
}
