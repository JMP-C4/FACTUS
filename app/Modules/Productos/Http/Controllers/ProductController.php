<?php

namespace App\Modules\Productos\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Productos\Http\Requests\StoreProductRequest;
use App\Modules\Productos\Http\Requests\UpdateProductRequest;
use App\Modules\Productos\Models\Product;
use App\Modules\Productos\Services\ProductService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ProductController extends Controller
{
    public function __construct(
        private readonly ProductService $productService,
    ) {}

    /**
     * Listado paginado con filtros.
     */
    public function index(Request $request): View
    {
        $companyId = $request->user()->company_id;

        $products = $this->productService->list($companyId, [
            'search' => $request->input('search'),
            'category_id' => $request->input('category_id'),
            'brand_id' => $request->input('brand_id'),
            'is_active' => $request->boolean('is_active'),
        ]);

        $categories = $this->productService->getCategories($companyId);
        $brands = $this->productService->getBrands($companyId);

        return view('productos::index', compact('products', 'categories', 'brands'));
    }

    /**
     * Formulario de creación.
     */
    public function create(Request $request): View
    {
        $companyId = $request->user()->company_id;
        $categories = $this->productService->getCategories($companyId);
        $brands = $this->productService->getBrands($companyId);

        return view('productos::create', compact('categories', 'brands'));
    }

    /**
     * Almacena un nuevo producto.
     */
    public function store(StoreProductRequest $request): RedirectResponse
    {
        $companyId = $request->user()->company_id;

        $this->productService->create($companyId, $request->validated());

        return redirect()
            ->route('productos.index')
            ->with('success', 'Producto creado exitosamente.');
    }

    /**
     * Muestra un producto.
     */
    public function show(Request $request, Product $product): View
    {
        $this->authorizeProduct($product, $request);

        $product->load(['category', 'brand']);

        return view('productos::show', compact('product'));
    }

    /**
     * Formulario de edición.
     */
    public function edit(Request $request, Product $product): View
    {
        $this->authorizeProduct($product, $request);

        $companyId = $request->user()->company_id;
        $categories = $this->productService->getCategories($companyId);
        $brands = $this->productService->getBrands($companyId);

        return view('productos::edit', compact('product', 'categories', 'brands'));
    }

    /**
     * Actualiza un producto.
     */
    public function update(UpdateProductRequest $request, Product $product): RedirectResponse
    {
        $this->authorizeProduct($product, $request);

        $this->productService->update($product, $request->validated());

        return redirect()
            ->route('productos.index')
            ->with('success', 'Producto actualizado exitosamente.');
    }

    /**
     * Cambia el estado de activación.
     */
    public function toggleActive(Request $request, Product $product): RedirectResponse
    {
        $this->authorizeProduct($product, $request);

        $this->productService->toggleActive($product);

        $status = $product->fresh()->is_active ? 'activado' : 'desactivado';

        return redirect()
            ->route('productos.index')
            ->with('success', "Producto {$status} exitosamente.");
    }

    /**
     * Elimina un producto.
     */
    public function destroy(Request $request, Product $product): RedirectResponse
    {
        $this->authorizeProduct($product, $request);

        $this->productService->delete($product);

        return redirect()
            ->route('productos.index')
            ->with('success', 'Producto eliminado exitosamente.');
    }

    /**
     * Búsqueda por código de barras (JSON).
     */
    public function findByBarcode(Request $request): JsonResponse
    {
        $request->validate([
            'barcode' => ['required', 'string', 'max:50'],
        ]);

        $companyId = $request->user()->company_id;
        $barcode = $request->input('barcode');

        $product = $this->productService
            ->getCatalogContract()
            ->findByBarcode($companyId, $barcode);

        if (! $product) {
            return response()->json([
                'message' => 'Producto no encontrado.',
            ], 404);
        }

        return response()->json($product);
    }

    /**
     * Verifica que el producto pertenece a la compañía del usuario.
     */
    private function authorizeProduct(Product $product, Request $request): void
    {
        abort_if(
            $product->company_id !== $request->user()->company_id,
            403,
            'No tienes acceso a este producto.'
        );
    }
}
