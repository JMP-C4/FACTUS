<?php

namespace App\Modules\Inventario\Models;

use App\Modules\Productos\Models\Product;
use Database\Factories\StockFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Stock extends Model
{
    use HasFactory;

    protected static string $factory = StockFactory::class;

    protected $fillable = [
        'warehouse_id',
        'product_id',
        'quantity',
        'minimum_stock_snapshot',
    ];

    protected function casts(): array
    {
        return [
            'quantity' => 'integer',
            'minimum_stock_snapshot' => 'integer',
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

    public function hasSufficientStock(int $requested): bool
    {
        return $this->quantity >= $requested;
    }
}
