<?php

namespace Tests\Feature\Ui;

use Tests\Support\HasRoles;
use Tests\TestCase;

class DashboardAutomationTest extends TestCase
{
    use HasRoles;

    public function test_super_admin_sees_automation_link_on_dashboard(): void
    {
        $superAdmin = $this->createSuperAdmin();

        $this->actingAs($superAdmin)
            ->get(route('dashboard'))
            ->assertOk()
            ->assertSee(route('automatizacion.index'));
    }

    public function test_admin_sees_automation_link_on_dashboard(): void
    {
        $company = $this->createCompany();
        $branch = $this->createBranch($company);
        $admin = $this->createAdminUser($company, $branch);

        $this->actingAs($admin)
            ->get(route('dashboard'))
            ->assertOk()
            ->assertSee(route('automatizacion.index'));
    }

    public function test_cashier_does_not_see_automation_link_on_dashboard(): void
    {
        $company = $this->createCompany();
        $branch = $this->createBranch($company);
        $cashier = $this->createCashierUser($company, $branch);

        $this->actingAs($cashier)
            ->get(route('dashboard'))
            ->assertOk()
            ->assertDontSee(route('automatizacion.index'));
    }

    public function test_user_without_permission_cannot_access_automation_panel(): void
    {
        $company = $this->createCompany();
        $branch = $this->createBranch($company);
        $cashier = $this->createCashierUser($company, $branch);

        $this->actingAs($cashier)
            ->get(route('automatizacion.index'))
            ->assertForbidden();
    }
}
