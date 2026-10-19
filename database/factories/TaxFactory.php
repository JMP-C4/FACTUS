<?php

namespace Database\Factories;

use App\Modules\Productos\Models\Tax;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Tax>
 */
class TaxFactory extends Factory
{
    protected $model = Tax::class;

    public function definition(): array
    {
        return [
            'company_id' => null,
            'name' => 'IVA '.fake()->numberBetween(1, 100).'%',
            'code' => 'IVA'.fake()->unique()->numerify('###'),
            'rate' => fake()->randomElement([0, 5, 19]),
            'type' => 'vat',
            'description' => null,
            'is_active' => true,
        ];
    }
}
