<?php

namespace Database\Factories;

use App\Models\Company;
use App\Modules\Productos\Models\Product;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Product>
 */
class ProductFactory extends Factory
{
    protected $model = Product::class;

    public function definition(): array
    {
        return [
            'company_id' => Company::factory(),
            'category_id' => null,
            'brand_id' => null,
            'name' => fake()->words(2, true),
            'description' => fake()->sentence(),
            'sku' => fake()->unique()->bothify('???-####'),
            'barcode' => fake()->unique()->numerify('############'),
            'unit_measure_code' => '94',
            'standard_code' => null,
            'price' => fake()->randomFloat(2, 1000, 500000),
            'tax_rate' => fake()->randomElement([0, 5, 8, 16, 19]),
            'minimum_stock' => fake()->numberBetween(0, 50),
            'is_active' => true,
        ];
    }

    public function inactive(): static
    {
        return $this->state(fn () => ['is_active' => false]);
    }

    public function withCategory(\App\Modules\Productos\Models\Category $category): static
    {
        return $this->state(fn () => ['category_id' => $category->id]);
    }

    public function withBrand(\App\Modules\Productos\Models\Brand $brand): static
    {
        return $this->state(fn () => ['brand_id' => $brand->id]);
    }
}
