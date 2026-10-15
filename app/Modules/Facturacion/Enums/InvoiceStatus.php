<?php

declare(strict_types=1);

namespace App\Modules\Facturacion\Enums;

enum InvoiceStatus: string
{
    case Draft = 'draft';
    case Pending = 'pending';
    case Validated = 'validated';
    case Issued = 'issued';
    case Error = 'error';
    case Cancelled = 'cancelled';

    public function label(): string
    {
        return match ($this) {
            self::Draft => 'Borrador',
            self::Pending => 'Pendiente',
            self::Validated => 'Validada',
            self::Issued => 'Emitida',
            self::Error => 'Error',
            self::Cancelled => 'Cancelada',
        };
    }

    public function color(): string
    {
        return match ($this) {
            self::Draft => 'muted',
            self::Pending => 'accent',
            self::Validated => 'primary',
            self::Issued => 'success',
            self::Error => 'danger',
            self::Cancelled => 'danger',
        };
    }

    public function isEmitable(): bool
    {
        return $this === self::Draft;
    }

    public function isCancellable(): bool
    {
        return ! in_array($this, [self::Issued, self::Cancelled]);
    }
}
