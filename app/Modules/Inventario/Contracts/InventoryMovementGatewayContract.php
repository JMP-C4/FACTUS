<?php

namespace App\Modules\Inventario\Contracts;

interface InventoryMovementGatewayContract
{
    /**
     * Registra una entrada de stock.
     */
    public function entry(int $warehouseId, int $productId, int $quantity, ?string $reason = null, ?int $performedBy = null): void;

    /**
     * Registra una salida de stock.
     *
     * `$referenceType` / `$referenceId` permiten enlazar el movimiento con el
     * origen de negocio (por ejemplo, una factura).
     */
    public function exit(
        int $warehouseId,
        int $productId,
        int $quantity,
        ?string $reason = null,
        ?int $performedBy = null,
        ?string $referenceType = null,
        ?int $referenceId = null,
    ): void;

    /**
     * Registra un ajuste de stock.
     */
    public function adjustment(int $warehouseId, int $productId, int $quantity, string $reason, ?int $performedBy = null): void;

    /**
     * Verifica si hay stock suficiente.
     */
    public function hasSufficientStock(int $warehouseId, int $productId, int $quantity): bool;

    /**
     * Obtiene el stock actual de un producto en un almacén.
     */
    public function getStock(int $warehouseId, int $productId): int;
}
