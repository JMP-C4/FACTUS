<?php

declare(strict_types=1);

namespace App\Modules\Facturacion\Models;

use App\Models\Company;
use App\Models\User;
use App\Modules\Facturacion\Enums\CreditNoteStatus;
use App\Modules\Support\Models\Concerns\BelongsToCompany;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

final class CreditNote extends Model
{
    use BelongsToCompany;

    protected $fillable = [
        'company_id',
        'invoice_id',
        'user_id',
        'status',
        'status_dian',
        'number',
        'external_id',
        'cufe',
        'qr_url',
        'correction_concept_code',
        'reason',
        'total',
        'billing_payload',
        'billing_response',
        'issued_at',
    ];

    protected function casts(): array
    {
        return [
            'status' => CreditNoteStatus::class,
            'total' => 'decimal:2',
            'billing_payload' => 'array',
            'billing_response' => 'array',
            'issued_at' => 'datetime',
        ];
    }

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    public function invoice(): BelongsTo
    {
        return $this->belongsTo(Invoice::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function billingLogs(): MorphMany
    {
        return $this->morphMany(BillingLog::class, 'loggable');
    }

    public function scopeByStatus($query, string $status)
    {
        return $query->where('status', $status);
    }

    public function scopeApplied($query)
    {
        return $query->whereIn('status', [
            CreditNoteStatus::Issued->value,
            CreditNoteStatus::Validated->value,
        ]);
    }
}
