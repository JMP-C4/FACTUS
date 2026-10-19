<?php

declare(strict_types=1);

namespace Tests\Feature\Facturacion;

use App\Models\Branch;
use App\Models\Company;
use App\Models\User;
use App\Modules\AccessControl\Models\Role;
use Tests\Support\HasRoles;
use Tests\TestCase;

class PosRemovalAuthorizationTest extends TestCase
{
    use HasRoles;

    private Company $company;

    private Branch $branch;

    private User $cashier;

    protected function setUp(): void
    {
        parent::setUp();

        $this->company = $this->createCompany();
        $this->branch = $this->createBranch($this->company);
        $this->cashier = $this->createCashierUser($this->company, $this->branch);
    }

    public function test_cashier_can_authorize_removal_with_configured_code(): void
    {
        config(['pos.authorization_code' => '9999']);

        $this->actingAs($this->cashier)
            ->postJson(route('facturacion.pos.autorizarRetiro'), ['code' => '9999'])
            ->assertOk()
            ->assertJson(['authorized' => true, 'method' => 'code']);
    }

    public function test_cashier_cannot_authorize_with_wrong_code(): void
    {
        config(['pos.authorization_code' => '9999']);

        $this->actingAs($this->cashier)
            ->postJson(route('facturacion.pos.autorizarRetiro'), ['code' => '0000'])
            ->assertForbidden()
            ->assertJson(['authorized' => false]);
    }

    public function test_cashier_can_authorize_with_supervisor_credentials(): void
    {
        $supervisor = $this->createSupervisor();

        $this->actingAs($this->cashier)
            ->postJson(route('facturacion.pos.autorizarRetiro'), [
                'email' => $supervisor->email,
                'password' => 'password',
            ])
            ->assertOk()
            ->assertJson(['authorized' => true, 'method' => 'credentials']);
    }

    public function test_cashier_cannot_authorize_with_non_supervisor_credentials(): void
    {
        $other = $this->createCashierUser($this->company, $this->branch);

        $this->actingAs($this->cashier)
            ->postJson(route('facturacion.pos.autorizarRetiro'), [
                'email' => $other->email,
                'password' => 'password',
            ])
            ->assertForbidden()
            ->assertJson(['authorized' => false]);
    }

    public function test_user_without_pos_permission_cannot_authorize(): void
    {
        $user = User::factory()->create([
            'company_id' => $this->company->id,
            'branch_id' => $this->branch->id,
        ]);

        $this->actingAs($user)
            ->postJson(route('facturacion.pos.autorizarRetiro'), ['code' => '9999'])
            ->assertForbidden();
    }

    private function createSupervisor(): User
    {
        $supervisor = User::factory()->create([
            'company_id' => $this->company->id,
            'branch_id' => $this->branch->id,
        ]);

        $supervisor->roles()->attach(
            Role::where('name', 'supervisor')->firstOrFail(),
            ['company_id' => $this->company->id],
        );

        return $supervisor;
    }
}
