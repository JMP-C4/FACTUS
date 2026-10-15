<?php

namespace Database\Factories;

use App\Models\User;
use App\Modules\AccessControl\Models\Role;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

/**
 * @extends Factory<User>
 */
class UserFactory extends Factory
{
    protected $model = User::class;

    protected static ?string $password;

    public function definition(): array
    {
        return [
            'name' => fake()->name(),
            'email' => fake()->unique()->safeEmail(),
            'email_verified_at' => now(),
            'password' => static::$password ??= Hash::make('password'),
            'remember_token' => Str::random(10),
            'company_id' => null,
            'branch_id' => null,
        ];
    }

    public function unverified(): static
    {
        return $this->state(fn () => [
            'email_verified_at' => null,
        ]);
    }

    public function superAdmin(): static
    {
        return $this->afterCreating(function (User $user): void {
            $role = Role::firstOrCreate(
                ['name' => 'super_admin'],
                ['scope_type' => 'global', 'description' => 'Super Administrador']
            );
            $user->roles()->attach($role);
        });
    }

    public function admin(): static
    {
        return $this->afterCreating(function (User $user): void {
            $role = Role::firstOrCreate(
                ['name' => 'admin'],
                ['scope_type' => 'company', 'description' => 'Administrador']
            );
            $user->roles()->attach($role, [
                'company_id' => $user->company_id,
            ]);
        });
    }

    public function cashier(): static
    {
        return $this->afterCreating(function (User $user): void {
            $role = Role::firstOrCreate(
                ['name' => 'cashier'],
                ['scope_type' => 'branch', 'description' => 'Cajero']
            );
            $user->roles()->attach($role, [
                'company_id' => $user->company_id,
                'branch_id' => $user->branch_id,
            ]);
        });
    }
}
