<?php declare(strict_types=1);

namespace Asterios\Core\Dto\QrCode;

use Asterios\Core\Data;

final class QrCodeData extends Data
{
    public function __construct(
        public readonly string $data,
        public readonly int $size = 300,
        public readonly int $margin = 10,
        public readonly string $labelText = '',
        public readonly int $labelFontSize = 20,
        public readonly ?string $logoPath = null,
        public readonly int $logoResizeToWidth = 50,
        public readonly bool $logoPunchoutBackground = true,
    ) {
    }
}