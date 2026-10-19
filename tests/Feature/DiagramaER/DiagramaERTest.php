<?php

declare(strict_types=1);

namespace Tests\Feature\DiagramaER;

use Tests\Support\HasRoles;
use Tests\TestCase;

class DiagramaERTest extends TestCase
{
    use HasRoles;

    public function test_guest_is_redirected_to_login(): void
    {
        $this->get(route('diagrama-er.index'))
            ->assertRedirect(route('login'));
    }

    public function test_regular_admin_is_forbidden_from_accessing_er_diagram(): void
    {
        $company = $this->createCompany();
        $branch = $this->createBranch($company);
        $admin = $this->createAdminUser($company, $branch);

        $this->actingAs($admin)
            ->get(route('diagrama-er.index'))
            ->assertForbidden();

        $this->actingAs($admin)
            ->get(route('diagrama-er.schema'))
            ->assertForbidden();

        $this->actingAs($admin)
            ->get(route('diagrama-er.table.data', ['table' => 'users']))
            ->assertForbidden();
    }

    public function test_super_admin_can_access_er_diagram_page(): void
    {
        $superAdmin = $this->createSuperAdmin();

        $response = $this->actingAs($superAdmin)
            ->get(route('diagrama-er.index'));

        $response->assertOk()
            ->assertSee('Diagrama Entidad - Relación (E-R)')
            ->assertSee('Arquitectura Relacional');
    }

    public function test_super_admin_can_fetch_schema_json(): void
    {
        $superAdmin = $this->createSuperAdmin();

        $response = $this->actingAs($superAdmin)
            ->getJson(route('diagrama-er.schema'));

        $response->assertOk()
            ->assertJsonStructure([
                'success',
                'schema' => [
                    'tables',
                    'relationships',
                    'modules',
                    'stats',
                ],
            ]);
    }

    public function test_super_admin_can_fetch_sanitized_table_data(): void
    {
        $superAdmin = $this->createSuperAdmin();

        $response = $this->actingAs($superAdmin)
            ->getJson(route('diagrama-er.table.data', ['table' => 'users']));

        $response->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonPath('table', 'users');

        $data = $response->json('data');
        $this->assertNotEmpty($data);

        // Verifica que la contraseña esté enmascarada
        foreach ($data as $row) {
            $this->assertEquals('••••••••••••', $row['password']);
        }
    }

    public function test_super_admin_cannot_fetch_invalid_table_data(): void
    {
        $superAdmin = $this->createSuperAdmin();

        $this->actingAs($superAdmin)
            ->getJson(route('diagrama-er.table.data', ['table' => 'non_existent_table']))
            ->assertNotFound();
    }

    public function test_super_admin_sees_er_diagram_in_sidebar_and_dashboard(): void
    {
        $superAdmin = $this->createSuperAdmin();

        $this->actingAs($superAdmin)
            ->get(route('dashboard'))
            ->assertOk()
            ->assertSee(route('diagrama-er.index'));
    }

    public function test_non_super_admin_does_not_see_er_diagram_in_sidebar(): void
    {
        $company = $this->createCompany();
        $branch = $this->createBranch($company);
        $cashier = $this->createCashierUser($company, $branch);

        $this->actingAs($cashier)
            ->get(route('dashboard'))
            ->assertOk()
            ->assertDontSee(route('diagrama-er.index'));
    }
}
