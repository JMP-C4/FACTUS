<?php

namespace App\Modules\Clientes\Services;

use App\Modules\Clientes\Contracts\CustomerRepositoryContract;
use App\Modules\Clientes\Models\Customer;
use Illuminate\Support\Collection;

class CustomerRepository implements CustomerRepositoryContract
{
    public function search(int $companyId, string $term): Collection
    {
        return Customer::forCompany($companyId)
            ->search($term)
            ->orderBy('first_name')
            ->get();
    }

    public function find(int $id, int $companyId): ?Customer
    {
        return Customer::where('id', $id)
            ->forCompany($companyId)
            ->first();
    }

    public function findByDocument(int $companyId, string $documentNumber): ?Customer
    {
        return Customer::forCompany($companyId)
            ->where('document_number', $documentNumber)
            ->first();
    }

    public function getFacturable(int $companyId): Collection
    {
        return Customer::forCompany($companyId)
            ->facturable()
            ->orderBy('first_name')
            ->get();
    }
}
