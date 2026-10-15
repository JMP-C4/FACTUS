<?php

namespace App\Modules\Facturacion\Models;

use App\Models\Company;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class NumberingRange extends Model
{
    protected $fillable = [
        'company_id',
        'external_id',
        'prefix',
        'resolution_number',
        'current_number',
        'from_number',
        'to_number',
        'valid_from',
        'valid_until',
        'is_active',
        'raw_payload',
    ];

    protected function casts(): array
    {
        return [
            'current_number' => 'integer',
            'from_number' => 'integer',
            'to_number' => 'integer',
            'valid_from' => 'date',
            'valid_until' => 'date',
            'is_active' => 'boolean',
            'raw_payload' => 'array',
        ];
    }

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    public function scopeForCompany($query, int $companyId)
    {
        return $query->where('company_id', $companyId);
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function isWithinRange(): bool
    {
        return $this->current_number < $this->to_number;
    }

    public function isCurrentlyValid(): bool
    {
        $now = now();

        return $this->is_active && $this->valid_from->lte($now) && $this->valid_until->gte($now);
    }

    public function getNextNumber(): int
    {
        return $this->current_number + 1;
    }

    public function incrementNumber(): void
    {
        $this->increment('current_number');
    }
}
