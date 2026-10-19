<?php

declare(strict_types=1);

namespace App\Modules\Productos\Models;

use App\Models\Company;
use Database\Factories\TaxFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

final class Tax extends Model
{
    use HasFactory;

    protected static string $factory = TaxFactory::class;

    public const TYPE_VAT = 'vat';

    public const TYPE_EXEMPT = 'exempt';

    public const TYPE_EXCLUDED = 'excluded';

    protected $fillable = [
        'company_id',
        'name',
        'code',
        'rate',
        'type',
        'description',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'rate' => 'decimal:2',
            'is_active' => 'boolean',
        ];
    }

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    public function products(): HasMany
    {
        return $this->hasMany(Product::class);
    }

    /**
     * Impuestos visibles para una compañía: los globales (company_id nulo) más
     * los propios. Un company_id nulo (super_admin global) ve todo.
     */
    public function scopeVisibleForCompany($query, ?int $companyId)
    {
        return $query->when(
            $companyId !== null,
            fn ($q) => $q->where(fn ($inner) => $inner
                ->whereNull('company_id')
                ->orWhere('company_id', $companyId)),
        );
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function isGlobal(): bool
    {
        return $this->company_id === null;
    }

    public function typeLabel(): string
    {
        return match ($this->type) {
            self::TYPE_VAT => 'IVA',
            self::TYPE_EXEMPT => 'Exento',
            self::TYPE_EXCLUDED => 'Excluido',
            default => $this->type,
        };
    }
}
