<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Issuer Data
    |--------------------------------------------------------------------------
    |
    | Datos usados por el emisor local para numeración y sellos simulados.
    |
    */
    'prefix' => env('BILLING_PREFIX', 'FAC'),
    'issuer_name' => env('BILLING_ISSUER_NAME', 'FACTUS'),
    'environment' => env('BILLING_ENVIRONMENT', 'local'),

    /*
    |--------------------------------------------------------------------------
    | Métodos de pago
    |--------------------------------------------------------------------------
    |
    | Catálogo único de métodos de pago aceptados por el POS y la emisión de
    | facturas. La clave viaja en las peticiones (payment_method_code) y el
    | valor es la etiqueta legible usada en reportes y pantallas.
    |
    */
    'payment_methods' => [
        'cash' => 'Efectivo',
        'debit_card' => 'Tarjeta débito',
        'credit_card' => 'Tarjeta crédito',
        'transfer' => 'Transferencia',
    ],

    /*
    |--------------------------------------------------------------------------
    | Método de pago por defecto
    |--------------------------------------------------------------------------
    */
    'default_payment_method' => 'cash',

    /*
    |--------------------------------------------------------------------------
    | Códigos DIAN de documentos
    |--------------------------------------------------------------------------
    |
    | Ver: https://www.dian.gov.co (anexo técnico de factura electrónica).
    |
    */
    'document_codes' => [
        'invoice' => '01',
        'credit_note' => '91',
    ],

    /*
    |--------------------------------------------------------------------------
    | Código de medida unitaria por defecto (UND - unidad)
    |--------------------------------------------------------------------------
    */
    'default_unit_measure_code' => '94',

    /*
    |--------------------------------------------------------------------------
    | Roles autorizados para retirar ítems del carrito del POS
    |--------------------------------------------------------------------------
    */
    'pos_removal_roles' => ['super_admin', 'admin', 'supervisor'],
];
