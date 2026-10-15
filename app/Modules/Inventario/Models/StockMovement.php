<?php

namespace App\Modules\Inventario\Models;

use App\Models\User;
use App\Modules\Productos\Models\Product;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class StockMovement extends Model
{
    protected $fillable = [
        'warehouse_id',
        'product_id',
        'movement_type',
        'quantity',
        'reference_type',
        'reference_id',
        'reason',
        'performed_by',
        'meta_json',
    ];

    protected function casts(): array
    {
        return [
            'quantity' => 'integer',
            'meta_json' => 'array',
        ];
    }

    public function warehouse(): BelongsTo
    {
        return $this->belongsTo(Warehouse::class);
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function reference(): MorphTo
    {
        return $this->morphTo();
    }

    public function performer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'performed_by');
    }

    public function scopeForCompany($query, int $companyId)
    {
        return $query->whereHas('warehouse', fn ($q) => $q->where('company_id', $companyId));
    }

    public function scopeByType($query, string $type)
    {
        return $query->where('movement_type', $type);
    }
}
