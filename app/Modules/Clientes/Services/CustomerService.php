<?php

namespace App\Modules\Clientes\Services;

use App\Modules\Clientes\Models\Customer;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;

class CustomerService
{
    public function __construct(
        private readonly CustomerRepository $customerRepository,
    ) {}

    /**
     * Lista clientes paginados con filtros.
     */
    public function list(?int $companyId, array $filters = []): LengthAwarePaginator
    {
        $query = Customer::forCompany($companyId);

        if (! empty($filters['search'])) {
            $query->search($filters['search']);
        }

        if (isset($filters['is_facturable'])) {
            $query->where('is_facturable', $filters['is_facturable']);
        }

        return $query->orderBy('first_name')->paginate(15);
    }

    /**
     * Crea un nuevo cliente.
     */
    public function create(int $companyId, array $data): Customer
    {
        return DB::transaction(function () use ($companyId, $data): Customer {
            $customer = Customer::create(array_merge($data, [
                'company_id' => $companyId,
            ]));

            $customer->syncFacturableStatus();

            return $customer;
        });
    }

    /**
     * Actualiza un cliente existente.
     */
    public function update(Customer $customer, array $data): Customer
    {
        DB::transaction(function () use ($customer, $data): void {
            $customer->update($data);
            $customer->syncFacturableStatus();
        });

        return $customer->fresh();
    }

    /**
     * Elimina un cliente.
     */
    public function delete(Customer $customer): bool
    {
        return $customer->delete();
    }

    /**
     * Expone el repositorio para búsquedas externas.
     */
    public function getRepository(): CustomerRepository
    {
        return $this->customerRepository;
    }
}
