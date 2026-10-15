<?php

namespace Tests\Support;

use App\Models\Branch;
use App\Models\Company;
use App\Models\User;
use App\Modules\AccessControl\Database\Seeders\PermissionSeeder;
use App\Modules\AccessControl\Database\Seeders\RoleSeeder;

trait HasRoles
{
    protected function seedRolesAndPermissions(): void
    {
        $this->seed(RoleSeeder::class);
        $this->seed(PermissionSeeder::class);
    }

    protected function createCompany(): Company
    {
        return Company::factory()->create();
    }

    protected function createBranch(Company $company): Branch
    {
        return Branch::factory()->state(['company_id' => $company->id])->create();
    }

    protected function createSuperAdmin(): User
    {
        $this->seedRolesAndPermissions();

        return User::factory()->superAdmin()->create();
    }

    protected function createAdminUser(Company $company, Branch $branch): User
    {
        $this->seedRolesAndPermissions();

        return User::factory()
            ->state([
                'company_id' => $company->id,
                'branch_id' => $branch->id,
            ])
            ->admin()
            ->create();
    }

    protected function createCashierUser(Company $company, Branch $branch): User
    {
        $this->seedRolesAndPermissions();

        return User::factory()
            ->state([
                'company_id' => $company->id,
                'branch_id' => $branch->id,
            ])
            ->cashier()
            ->create();
    }
}
