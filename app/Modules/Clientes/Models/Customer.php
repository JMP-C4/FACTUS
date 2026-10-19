<?php

namespace App\Modules\Clientes\Models;

use App\Models\Company;
use App\Modules\Support\Models\Concerns\BelongsToCompany;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Customer extends Model
{
    use BelongsToCompany;

    protected $fillable = [
        'company_id',
        'identification_document_code',
        'document_number',
        'legal_organization_code',
        'tribute_code',
        'municipality_code',
        'first_name',
        'last_name',
        'company_name',
        'email',
        'phone',
        'address',
        'billing_email',
        'is_facturable',
    ];

    protected function casts(): array
    {
        return [
            'is_facturable' => 'boolean',
        ];
    }

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    public function scopeSearch($query, string $term)
    {
        return $query->where(function ($q) use ($term): void {
            $q->where('first_name', 'like', "%{$term}%")
                ->orWhere('last_name', 'like', "%{$term}%")
                ->orWhere('company_name', 'like', "%{$term}%")
                ->orWhere('document_number', 'like', "%{$term}%")
                ->orWhere('phone', 'like', "%{$term}%")
                ->orWhere('email', 'like', "%{$term}%");
        });
    }

    public function scopeFacturable($query)
    {
        return $query->where('is_facturable', true);
    }

    /**
     * Nombre completo del cliente (persona natural).
     */
    public function getFullNameAttribute(): string
    {
        return trim("{$this->first_name} {$this->last_name}") ?: ($this->company_name ?? 'Sin nombre');
    }

    /**
     * Nombre para mostrar (empresa o persona).
     */
    public function getDisplayNameAttribute(): string
    {
        return $this->company_name ?: $this->full_name;
    }

    /**
     * Verifica si el cliente tiene datos suficientes para facturación.
     */
    public function checkFacturable(): bool
    {
        $required = [
            'identification_document_code',
            'document_number',
            'legal_organization_code',
            'tribute_code',
            'first_name',
            'email',
            'address',
            'municipality_code',
        ];

        foreach ($required as $field) {
            if (empty($this->{$field})) {
                return false;
            }
        }

        return true;
    }

    /**
     * Actualiza el estado de facturabilidad.
     */
    public function syncFacturableStatus(): void
    {
        $this->update(['is_facturable' => $this->checkFacturable()]);
    }
}
