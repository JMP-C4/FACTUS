<?php

declare(strict_types=1);

namespace App\Modules\Facturacion\Models;

use App\Models\Branch;
use App\Models\Company;
use App\Models\User;
use App\Modules\Clientes\Models\Customer;
use App\Modules\Facturacion\Enums\InvoiceStatus;
use App\Modules\Support\Models\Concerns\BelongsToCompany;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

final class Invoice extends Model
{
    use BelongsToCompany;

    protected $fillable = [
        'company_id',
        'branch_id',
        'customer_id',
        'numbering_range_id',
        'user_id',
        'status',
        'status_dian',
        'external_id',
        'cufe',
        'qr_url',
        'invoice_number',
        'issued_at',
        'subtotal',
        'discount_total',
        'tax_total',
        'total',
        'payment_form_code',
        'payment_method_code',
        'billing_payload',
        'billing_response',
    ];

    protected function casts(): array
    {
        return [
            'status' => InvoiceStatus::class,
            'issued_at' => 'datetime',
            'subtotal' => 'decimal:2',
            'discount_total' => 'decimal:2',
            'tax_total' => 'decimal:2',
            'total' => 'decimal:2',
            'billing_payload' => 'array',
            'billing_response' => 'array',
        ];
    }

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    public function branch(): BelongsTo
    {
        return $this->belongsTo(Branch::class);
    }

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    public function numberingRange(): BelongsTo
    {
        return $this->belongsTo(NumberingRange::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(InvoiceItem::class);
    }

    public function payments(): HasMany
    {
        return $this->hasMany(InvoicePayment::class);
    }

    public function creditNotes(): HasMany
    {
        return $this->hasMany(CreditNote::class);
    }

    public function scopeByStatus($query, string $status)
    {
        return $query->where('status', $status);
    }

    public function isDraft(): bool
    {
        return $this->status === InvoiceStatus::Draft;
    }
}
