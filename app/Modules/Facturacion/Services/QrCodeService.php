<?php

declare(strict_types=1);

namespace App\Modules\Facturacion\Services;

use Endroid\QrCode\Color\Color;
use Endroid\QrCode\Encoding\Encoding;
use Endroid\QrCode\ErrorCorrectionLevel;
use Endroid\QrCode\QrCode;
use Endroid\QrCode\RoundBlockSizeMode;
use Endroid\QrCode\Writer\PngWriter;
use Throwable;

/**
 * Genera códigos QR como data URI PNG para facturas y comprobantes.
 *
 * Envuelve `endroid/qr-code` para que el QR de verificación pueda incrustarse
 * directamente en vistas Blade y en el PDF generado con dompdf.
 */
final class QrCodeService
{
    private const DEFAULT_SIZE = 220;

    private const DEFAULT_MARGIN = 8;

    /**
     * Devuelve el contenido indicado codificado como QR `data:image/png`.
     *
     * Retorna `null` si el contenido está vacío o si la generación falla
     * (por ejemplo, sin extensión GD), para no romper el documento.
     */
    public function dataUri(string $payload, int $size = self::DEFAULT_SIZE, int $margin = self::DEFAULT_MARGIN): ?string
    {
        $payload = trim($payload);

        if ($payload === '') {
            return null;
        }

        try {
            $qrCode = new QrCode(
                data: $payload,
                encoding: new Encoding('UTF-8'),
                errorCorrectionLevel: ErrorCorrectionLevel::Medium,
                size: $size,
                margin: $margin,
                roundBlockSizeMode: RoundBlockSizeMode::Margin,
                foregroundColor: new Color(17, 17, 17),
                backgroundColor: new Color(255, 255, 255),
            );

            return (new PngWriter)->write($qrCode)->getDataUri();
        } catch (Throwable) {
            return null;
        }
    }
}
