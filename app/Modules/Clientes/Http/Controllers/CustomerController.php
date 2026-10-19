<?php

namespace App\Modules\Clientes\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Clientes\Http\Requests\StoreCustomerRequest;
use App\Modules\Clientes\Http\Requests\UpdateCustomerRequest;
use App\Modules\Clientes\Models\Customer;
use App\Modules\Clientes\Services\CustomerService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class CustomerController extends Controller
{
    public function __construct(
        private readonly CustomerService $customerService,
    ) {}

    /**
     * Listado paginado con filtros.
     */
    public function index(Request $request): View
    {
        $companyId = $request->user()->company_id;

        $customers = $this->customerService->list($companyId, [
            'search' => $request->input('search'),
            'is_facturable' => $request->has('is_facturable') ? $request->boolean('is_facturable') : null,
        ]);

        return view('clientes::index', compact('customers'));
    }

    /**
     * Formulario de creación.
     */
    public function create(): View
    {
        return view('clientes::create');
    }

    /**
     * Almacena un nuevo cliente.
     */
    public function store(StoreCustomerRequest $request): RedirectResponse
    {
        $companyId = $this->currentCompanyId($request);

        if ($companyId === null) {
            return back()->with('error', 'Debes pertenecer a una compañía para crear clientes.');
        }

        $this->customerService->create($companyId, $request->validated());

        return redirect()
            ->route('clientes.index')
            ->with('success', 'Cliente creado exitosamente.');
    }

    /**
     * Muestra un cliente.
     */
    public function show(Request $request, Customer $customer): View
    {
        $this->authorizeCustomer($customer, $request);

        return view('clientes::show', compact('customer'));
    }

    /**
     * Formulario de edición.
     */
    public function edit(Request $request, Customer $customer): View
    {
        $this->authorizeCustomer($customer, $request);

        return view('clientes::edit', compact('customer'));
    }

    /**
     * Actualiza un cliente.
     */
    public function update(UpdateCustomerRequest $request, Customer $customer): RedirectResponse
    {
        $this->authorizeCustomer($customer, $request);

        $this->customerService->update($customer, $request->validated());

        return redirect()
            ->route('clientes.index')
            ->with('success', 'Cliente actualizado exitosamente.');
    }

    /**
     * Elimina un cliente.
     */
    public function destroy(Request $request, Customer $customer): RedirectResponse
    {
        $this->authorizeCustomer($customer, $request);

        $this->customerService->delete($customer);

        return redirect()
            ->route('clientes.index')
            ->with('success', 'Cliente eliminado exitosamente.');
    }

    /**
     * Búsqueda rápida de clientes (JSON) para POS.
     */
    public function search(Request $request): JsonResponse
    {
        $request->validate([
            'term' => ['required', 'string', 'min:2'],
        ]);

        $companyId = $request->user()->company_id;
        $term = $request->input('term');

        $customers = $this->customerService
            ->getRepository()
            ->search($companyId, $term)
            ->map(fn (Customer $c) => [
                'id' => $c->id,
                'display_name' => $c->display_name,
                'document_number' => $c->document_number,
                'is_facturable' => $c->is_facturable,
            ]);

        return response()->json($customers);
    }

    /**
     * Verifica que el cliente pertenece a la compañía del usuario.
     */
    private function authorizeCustomer(Customer $customer, Request $request): void
    {
        $companyId = $request->user()->company_id;

        abort_if(
            $companyId !== null && $customer->company_id !== $companyId,
            403,
            'No tienes acceso a este cliente.'
        );
    }
}
