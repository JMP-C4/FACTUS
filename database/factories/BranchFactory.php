<?php

namespace Database\Factories;

use App\Models\Branch;
use App\Models\Company;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Branch>
 */
class BranchFactory extends Factory
{
    protected $model = Branch::class;

    public function definition(): array
    {
        return [
            'company_id' => 1,
            'name' => fake()->company() . ' - Sede',
            'code' => fake()->unique()->bothify('??-###'),
            'address' => fake()->address(),
            'city' => fake()->city(),
            'phone' => fake()->phoneNumber(),
            'pos_prefix' => fake()->bothify('??'),
            'resolution_from' => 1,
            'resolution_to' => 500000,
            'resolution_date' => now(),
            'allows_negative_stock' => false,
            'is_main' => false,
            'is_warehouse' => true,
            'is_active' => true,
        ];
    }

    public function main(): static
    {
        return $this->state(fn () => ['is_main' => true]);
    }

    public function allowsNegativeStock(): static
    {
        return $this->state(fn () => ['allows_negative_stock' => true]);
    }
}
