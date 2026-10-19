<?php

declare(strict_types=1);

namespace App\Modules\Facturacion\Services;

use Picqer\Barcode\BarcodeGeneratorPNG;
use Throwable;

/**
 * Genera códigos de barra 1D (estándar CODE 128) como data URI PNG.
 *
 * Envuelve `picqer/php-barcode-generator`, la librería de códigos de barra
 * más usada en PHP, para producir imágenes embebibles en HTML y PDF sin
 * depender de archivos temporales.
 */
final class BarcodeService
{
    private const DEFAULT_WIDTH_FACTOR = 2;

    private const DEFAULT_HEIGHT = 60;

    /**
     * Devuelve la representación CODE 128 del valor como `data:image/png`.
     *
     * Retorna `null` si el valor está vacío o si el generador falla (por
     * ejemplo, sin extensión GD), para no romper la generación del documento.
     */
    public function dataUri(
        string $value,
        string $type = BarcodeGeneratorPNG::TYPE_CODE_128,
        int $widthFactor = self::DEFAULT_WIDTH_FACTOR,
        int $height = self::DEFAULT_HEIGHT,
    ): ?string {
        $value = trim($value);

        if ($value === '') {
            return null;
        }

        try {
            $generator = new BarcodeGeneratorPNG;
            $png = $generator->getBarcode($value, $type, $widthFactor, $height);
        } catch (Throwable) {
            return null;
        }

        return 'data:image/png;base64,'.base64_encode($png);
    }
}
