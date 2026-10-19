<?php

namespace App\Modules\Facturacion\Models;

use App\Models\Company;
use App\Modules\Support\Models\Concerns\BelongsToCompany;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class BillingLog extends Model
{
    use BelongsToCompany;

    protected $table = 'billing_logs';

    protected $fillable = [
        'company_id',
        'loggable_type',
        'loggable_id',
        'endpoint',
        'http_method',
        'request_body',
        'response_body',
        'http_status',
        'status',
        'error_message',
        'processed_at',
    ];

    protected function casts(): array
    {
        return [
            'request_body' => 'array',
            'response_body' => 'array',
            'processed_at' => 'datetime',
        ];
    }

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    public function loggable(): MorphTo
    {
        return $this->morphTo();
    }

    public function scopeByStatus($query, string $status)
    {
        return $query->where('status', $status);
    }
}
