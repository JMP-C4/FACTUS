<?php

declare(strict_types=1);

/**
 * Seed de datos para el test de rendimiento.
 *
 * Prepara el esquema actual (DB_SEARCH_PATH) con:
 *  - usuario demo verificado
 *  - numeración y stock ampliados
 *  - 300 facturas emitidas previas (listados/reportes con datos reales)
 *  - 200 usuarios de carga con rol admin (sin bcrypt por usuario)
 *  - 2000 sesiones HTTP pre-generadas (evita logins costosos en la medición)
 *  - meta JSON consumido por load-test.js
 */

require __DIR__.'/../../vendor/autoload.php';

$app = require __DIR__.'/../../bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\Branch;
use App\Models\Company;
use App\Models\User;
use App\Modules\AccessControl\Models\Role;
use App\Modules\Facturacion\Enums\InvoiceStatus;
use App\Modules\Facturacion\Models\Invoice;
use App\Modules\Facturacion\Models\NumberingRange;
use App\Modules\Facturacion\Services\InvoiceService;
use App\Modules\Inventario\Models\Stock;
use App\Modules\Productos\Models\Product;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

$startedAt = microtime(true);

$admin = User::where('email', 'admin@factus.local')->firstOrFail();
$company = Company::query()->firstOrFail();
$branch = Branch::query()->firstOrFail();
$adminRole = Role::where('name', 'admin')->firstOrFail();

$admin->forceFill(['email_verified_at' => $admin->email_verified_at ?? now()])->save();

NumberingRange::query()->update([
    'to_number' => 10_000_000,
    'valid_until' => now()->addYears(3)->toDateString(),
]);

Stock::query()->update(['quantity' => 10_000_000]);

$product = Product::where('sku', 'CAF-001')->firstOrFail();

// --- Usuarios de carga (200) -------------------------------------------------
// Un solo hash bcrypt reutilizado: los usuarios de carga nunca validan
// contraseña por login propio (las sesiones se pre-generan), pero sí permiten
// medir logins rotando entre sus correos.
$perfUsers = User::where('email', 'like', 'perf%@perf.test')->get();

if ($perfUsers->count() < 200) {
    $sharedHash = Hash::make('password');
    $now = now();
    $rows = [];
    for ($i = 0; $i < 200; $i++) {
        $rows[] = [
            'name' => "Perf User {$i}",
            'email' => "perf{$i}@perf.test",
            'password' => $sharedHash,
            'email_verified_at' => $now,
            'company_id' => $company->id,
            'branch_id' => $branch->id,
            'created_at' => $now,
            'updated_at' => $now,
        ];
    }
    User::insert($rows);

    $perfUsers = User::where('email', 'like', 'perf%@perf.test')->get();
    foreach ($perfUsers as $user) {
        $user->userRoles()->create([
            'role_id' => $adminRole->id,
            'company_id' => $company->id,
            'branch_id' => null,
            'assigned_by' => null,
        ]);
    }
}

// --- 300 facturas emitidas previas ------------------------------------------
$issuedBefore = Invoice::where('status', InvoiceStatus::Issued->value)->count();

if ($issuedBefore < 300) {
    $service = app(InvoiceService::class);
    $unitPrice = (float) $product->price;
    $taxRate = (float) $product->tax_rate;
    $total = round($unitPrice * (1 + $taxRate / 100), 2);

    $payload = [
        'payment_form_code' => '1',
        'payment_method_code' => 'cash',
        'items' => [[
            'product_id' => $product->id,
            'description' => $product->name,
            'quantity' => 1,
            'unit_price' => $unitPrice,
            'tax_rate' => $taxRate,
        ]],
        'payments' => [[
            'payment_method_code' => 'cash',
            'amount' => $total,
            'cash_received' => $total,
            'cash_change' => 0,
        ]],
    ];

    $t = microtime(true);
    for ($i = $issuedBefore; $i < 300; $i++) {
        DB::transaction(function () use ($service, $company, $admin, $payload): void {
            $invoice = $service->create($company->id, $admin->id, $payload);
            $service->issue($invoice);
        });
    }
    printf("300 facturas previas: %.1fs\n", microtime(true) - $t);
}

$issued = Invoice::where('status', InvoiceStatus::Issued->value)->count();
$errored = Invoice::where('status', InvoiceStatus::Error->value)->count();
printf("Facturas emitidas=%d error=%d\n", $issued, $errored);

if ($issued < 300) {
    fwrite(STDERR, "ABORT: facturas emitidas insuficientes (revisar billing local).\n");
    exit(1);
}

// --- Sesiones HTTP pre-generadas (2000) --------------------------------------
$store = app('session.store');
$guardKey = 'login_web_'.sha1(Illuminate\Auth\SessionGuard::class);
$csrfToken = Str::random(40);
$allUsers = collect([$admin])->concat($perfUsers->values()->all());

DB::table('sessions')->delete();

$sessions = [];
$cookieName = config('session.cookie');
$encrypter = app('encrypter');
$t = microtime(true);
for ($i = 0; $i < 2000; $i++) {
    $store->flush();
    $store->setId(Str::random(40));
    $owner = $allUsers[$i % $allUsers->count()];
    $store->put($guardKey, $owner->id);
    $store->put('_token', $csrfToken);
    // DatabaseSessionHandler conserva $exists entre llamadas: sin resetearlo,
    // todas las filas salvo la primera harían UPDATE sobre un id inexistente.
    $store->getHandler()->setExists(false);
    $store->save();
    // EncryptCookies exige el valor de la cookie cifrado con APP_KEY + prefijo.
    $sid = $store->getId();
    $sessions[] = [
        'id' => $sid,
        'email' => $owner->email,
        'cookie' => $encrypter->encryptString(
            Illuminate\Cookie\CookieValuePrefix::create($cookieName, $encrypter->getKey()).$sid
        ),
    ];
}
printf("2000 sesiones: %.1fs\n", microtime(true) - $t);

$meta = [
    'cookieName' => config('session.cookie'),
    'csrfToken' => $csrfToken,
    'guardKey' => $guardKey,
    'adminEmail' => $admin->email,
    'adminPassword' => 'password',
    'loginEmails' => $allUsers->pluck('email')->values()->all(),
    'companyId' => $company->id,
    'branchId' => $branch->id,
    'productId' => $product->id,
    'productName' => $product->name,
    'productPrice' => $unitPrice ?? (float) $product->price,
    'productTaxRate' => $taxRate ?? (float) $product->tax_rate,
    'sessions' => $sessions,
    'counts' => [
        'users' => User::count(),
        'invoices' => Invoice::count(),
        'invoices_issued' => $issued,
        'sessions' => (int) DB::table('sessions')->count(),
    ],
    'generatedAt' => now()->toIso8601String(),
];

file_put_contents(
    __DIR__.'/perf-meta.json',
    json_encode($meta, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES),
);

printf(
    "meta escrita: sesiones=%d usuarios=%d facturas=%d total=%.1fs\n",
    count($sessions),
    $meta['counts']['users'],
    $meta['counts']['invoices'],
    microtime(true) - $startedAt,
);
