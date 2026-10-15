<?php

namespace Database\Factories;

use App\Models\Company;
use App\Modules\Inventario\Models\Stock;
use App\Modules\Inventario\Models\Warehouse;
use App\Modules\Productos\Models\Product;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Stock>
 */
class StockFactory extends Factory
{
    protected $model = Stock::class;

    public function definition(): array
    {
        return [
            'warehouse_id' => Warehouse::factory(),
            'product_id' => Product::factory(),
            'quantity' => fake()->numberBetween(0, 1000),
            'minimum_stock_snapshot' => fake()->numberBetween(0, 50),
        ];
    }

    public function withQuantity(int $quantity): static
    {
        return $this->state(fn () => ['quantity' => $quantity]);
    }
}
