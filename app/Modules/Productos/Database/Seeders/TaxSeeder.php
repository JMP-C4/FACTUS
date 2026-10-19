<?php

declare(strict_types=1);

namespace App\Modules\Productos\Database\Seeders;

use App\Modules\Productos\Models\Tax;
use Illuminate\Database\Seeder;

/**
 * Catálogo global de tarifas de IVA (Colombia).
 *
 * Los impuestos con `company_id` nulo son compartidos por todas las compañías
 * y sólo los administra el super_admin global.
 */
class TaxSeeder extends Seeder
{
    public function run(): void
    {
        $taxes = [
            [
                'code' => 'IVA19',
                'name' => 'IVA 19% (General)',
                'rate' => 19,
                'type' => Tax::TYPE_VAT,
                'description' => 'Tarifa general aplicable a la mayoría de bienes y servicios.',
            ],
            [
                'code' => 'IVA5',
                'name' => 'IVA 5% (Diferencial)',
                'rate' => 5,
                'type' => Tax::TYPE_VAT,
                'description' => 'Tarifa diferencial para productos de primera necesidad e insumos agrícolas.',
            ],
            [
                'code' => 'IVA0',
                'name' => 'IVA 0% (Exento)',
                'rate' => 0,
                'type' => Tax::TYPE_EXEMPT,
                'description' => 'Bienes exentos de IVA que permiten descontar el IVA de insumos.',
            ],
            [
                'code' => 'EXCLUIDO',
                'name' => 'Excluido de IVA',
                'rate' => 0,
                'type' => Tax::TYPE_EXCLUDED,
                'description' => 'Bienes no gravados que no otorgan derecho a descuento de IVA.',
            ],
        ];

        foreach ($taxes as $tax) {
            Tax::updateOrCreate(
                ['company_id' => null, 'code' => $tax['code']],
                array_merge($tax, ['is_active' => true]),
            );
        }
    }
}
