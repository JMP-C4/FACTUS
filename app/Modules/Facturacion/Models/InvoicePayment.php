<?php

namespace App\Modules\Facturacion\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class InvoicePayment extends Model
{
    protected $fillable = [
        'invoice_id',
        'payment_form_code',
        'payment_method_code',
        'amount',
        'due_date',
        'reference_number',
        'card_last_four',
        'card_approval_code',
        'bank_entity',
        'cash_received',
        'cash_change',
    ];

    protected function casts(): array
    {
        return [
            'amount' => 'decimal:2',
            'due_date' => 'date',
            'cash_received' => 'decimal:2',
            'cash_change' => 'decimal:2',
        ];
    }

    public function invoice(): BelongsTo
    {
        return $this->belongsTo(Invoice::class);
    }
}
