<?php

namespace App\Modules\Clientes\Services;

use App\Modules\Clientes\Contracts\CustomerDataProviderContract;
use App\Modules\Clientes\Models\Customer;
use Illuminate\Support\Collection;

class CustomerDataProvider implements CustomerDataProviderContract
{
    public function search(int $companyId, string $term): Collection
    {
        return Customer::forCompany($companyId)
            ->search($term)
            ->orderBy('first_name')
            ->get();
    }

    public function getForInvoicing(int $customerId, int $companyId): ?object
    {
        $customer = Customer::where('id', $customerId)
            ->forCompany($companyId)
            ->first();

        if (! $customer) {
            return null;
        }

        return (object) $customer->only([
            'id',
            'identification_document_code',
            'document_number',
            'legal_organization_code',
            'tribute_code',
            'municipality_code',
            'first_name',
            'last_name',
            'company_name',
            'email',
            'phone',
            'address',
            'billing_email',
            'is_facturable',
        ]);
    }

    public function getFacturable(int $companyId): Collection
    {
        return Customer::forCompany($companyId)
            ->facturable()
            ->orderBy('first_name')
            ->get();
    }

    public function isFacturable(int $customerId, int $companyId): bool
    {
        $customer = Customer::where('id', $customerId)
            ->forCompany($companyId)
            ->first();

        return $customer?->is_facturable ?? false;
    }
}
