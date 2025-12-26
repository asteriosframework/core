<?php declare(strict_types=1);

namespace Asterios\Core\Contracts\QrCode;

use Asterios\Core\Dto\QrCode\QrCodeData;
use Asterios\Core\Exception\QrCodeException;

interface QrCodeGeneratorInterface
{
    /**
     * @param QrCodeData $data
     * @return string
     * @throws QrCodeException
     */
    public function generateAsPngString(QrCodeData $data): string;

    /**
     * @param QrCodeData $data
     * @param string $filePath
     * @throws QrCodeException
     */
    public function saveToFile(QrCodeData $data, string $filePath): void;
}
