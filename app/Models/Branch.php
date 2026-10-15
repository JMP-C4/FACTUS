<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Branch extends Model
{
    use HasFactory, SoftDeletes;

    /**
     * Los atributos que son asignables masivamente.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'company_id',
        'name',
        'code',
        'address',
        'city',
        'phone',
        'pos_prefix',
        'resolution_from',
        'resolution_to',
        'resolution_date',
        'allows_negative_stock',
        'is_main',
        'is_warehouse',
        'is_active',
    ];

    /**
     * Los atributos que deben ser convertidos.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'allows_negative_stock' => 'boolean',
            'is_main' => 'boolean',
            'is_warehouse' => 'boolean',
            'is_active' => 'boolean',
            'resolution_date' => 'date',
        ];
    }

    /**
     * Relación: Una sucursal pertenece a una compañía.
     */
    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    /**
     * Relación: Una sucursal tiene muchos usuarios.
     */
    public function users(): HasMany
    {
        return $this->hasMany(User::class);
    }

    /**
     * Scope: Filtrar solo sucursales activas.
     *
     * @param  Builder  $query
     * @return Builder
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope: Filtrar solo sucursales principales.
     *
     * @param  Builder  $query
     * @return Builder
     */
    public function scopeMain($query)
    {
        return $query->where('is_main', true);
    }

    /**
     * Scope: Filtrar solo bodegas (sucursales de inventario).
     *
     * @param  Builder  $query
     * @return Builder
     */
    public function scopeWarehouse($query)
    {
        return $query->where('is_warehouse', true);
    }

    /**
     * Scope: Filtrar sucursales que son puntos de venta (no bodegas).
     *
     * @param  Builder  $query
     * @return Builder
     */
    public function scopePos($query)
    {
        return $query->where('is_warehouse', false);
    }

    /**
     * Verificar si esta sucursal permite stock negativo.
     */
    public function allowsNegativeStock(): bool
    {
        return $this->allows_negative_stock;
    }

    /**
     * Obtener el código completo de numeración de facturas.
     * Combina el prefijo con el número consecutivo.
     */
    public function getFullInvoiceNumber(int $consecutive): string
    {
        return $this->pos_prefix.str_pad($consecutive, 9, '0', STR_PAD_LEFT);
    }

    /**
     * Verificar si un número de factura está dentro del rango permitido.
     */
    public function isInvoiceNumberValid(int $number): bool
    {
        return $number >= $this->resolution_from && $number <= $this->resolution_to;
    }
}
