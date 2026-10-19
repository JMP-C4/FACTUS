<?php

use App\Modules\Facturacion\Http\Controllers\CreditNoteController;
use App\Modules\Facturacion\Http\Controllers\InvoiceController;
use App\Modules\Facturacion\Http\Controllers\PosController;
use Illuminate\Support\Facades\Route;

Route::middleware(['web', 'auth'])
    ->prefix('facturacion')
    ->name('facturacion.')
    ->group(function (): void {
        // POS.
        Route::get('/pos', [PosController::class, 'index'])
            ->middleware('permission:pos.vender')
            ->name('pos');

        Route::get('/pos/productos', [PosController::class, 'searchProducts'])
            ->middleware('permission:pos.vender')
            ->name('pos.productos');

        Route::get('/pos/clientes', [PosController::class, 'searchCustomers'])
            ->middleware('permission:pos.vender')
            ->name('pos.clientes');

        Route::post('/pos/autorizar-retiro', [PosController::class, 'authorizeRemoval'])
            ->middleware('permission:pos.vender')
            ->name('pos.autorizarRetiro');

        Route::post('/pos/checkout', [PosController::class, 'checkout'])
            ->middleware('permission:pos.cobrar')
            ->name('pos.checkout');

        // Facturas.
        Route::get('/facturas', [InvoiceController::class, 'index'])
            ->middleware('permission:facturacion.view')
            ->name('facturas.index');

        Route::get('/facturas/crear', [InvoiceController::class, 'create'])
            ->middleware('permission:facturacion.emitir')
            ->name('facturas.create');

        Route::post('/facturas', [InvoiceController::class, 'store'])
            ->middleware('permission:facturacion.emitir')
            ->name('facturas.store');

        Route::get('/facturas/{invoice}', [InvoiceController::class, 'show'])
            ->middleware('permission:facturacion.view')
            ->name('facturas.show');

        Route::get('/facturas/{invoice}/pdf', [InvoiceController::class, 'downloadPdf'])
            ->middleware('permission:facturacion.view')
            ->name('facturas.pdf');

        Route::post('/facturas/{invoice}/emitir', [InvoiceController::class, 'issue'])
            ->middleware('permission:facturacion.emitir')
            ->name('facturas.issue');

        Route::post('/facturas/{invoice}/anular', [InvoiceController::class, 'cancel'])
            ->middleware('permission:facturacion.anular')
            ->name('facturas.cancel');

        // Notas de crédito.
        Route::get('/notas-credito', [CreditNoteController::class, 'index'])
            ->middleware('permission:facturacion.view')
            ->name('notas.index');

        Route::post('/facturas/{invoice}/notas-credito', [CreditNoteController::class, 'store'])
            ->middleware('permission:facturacion.nota_credito')
            ->name('notas.store');
    });

// Verificación pública de facturas (destino del código QR local).
Route::middleware('web')
    ->get('/facturacion/verificar/{cufe}', [InvoiceController::class, 'verify'])
    ->name('facturacion.verificar');
