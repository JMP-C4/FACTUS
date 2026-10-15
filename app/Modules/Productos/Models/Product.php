<?php

namespace App\Modules\Productos\Models;

use App\Models\Company;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Product extends Model
{
    use HasFactory;

    protected static string $factory = \Database\Factories\ProductFactory::class;
    protected $fillable = [
        'company_id',
        'category_id',
        'brand_id',
        'name',
        'description',
        'sku',
        'barcode',
        'unit_measure_code',
        'standard_code',
        'price',
        'tax_rate',
        'minimum_stock',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'price' => 'decimal:2',
            'tax_rate' => 'decimal:2',
            'minimum_stock' => 'integer',
            'is_active' => 'boolean',
        ];
    }

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    public function brand(): BelongsTo
    {
        return $this->belongsTo(Brand::class);
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeForCompany($query, int $companyId)
    {
        return $query->where('company_id', $companyId);
    }

    public function scopeSearch($query, string $term)
    {
        return $query->where(function ($q) use ($term): void {
            $q->where('name', 'like', "%{$term}%")
                ->orWhere('sku', 'like', "%{$term}%")
                ->orWhere('description', 'like', "%{$term}%");
        });
    }

    public function scopeFindByBarcode($query, string $barcode)
    {
        return $query->where('barcode', $barcode);
    }

    public function hasStock(): bool
    {
        return $this->minimum_stock > 0;
    }

    public function getPriceWithTax(): float
    {
        return $this->price + ($this->price * $this->tax_rate / 100);
    }
}
