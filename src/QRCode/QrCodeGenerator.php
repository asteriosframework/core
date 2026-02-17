<?php declare(strict_types=1);

namespace Asterios\Core\QRCode;

use Asterios\Core\Contracts\QrCode\Builder\QrCodeBuilderFactoryInterface;
use Asterios\Core\Contracts\QrCode\QrCodeGeneratorInterface;
use Asterios\Core\Dto\QrCode\QrCodeData;
use Asterios\Core\Exception\QrCodeException;

final class QrCodeGenerator implements QrCodeGeneratorInterface
{
    public function __construct(
        private readonly QrCodeBuilderFactoryInterface $factory
    ) {
    }

    public function generateAsPngString(QrCodeData $data): string
    {
        try
        {
            return $this->factory->build($data)->getString();
        }
        catch (\Throwable $e)
        {
            throw new QrCodeException('Failed to generate qr-code.', 0, $e);
        }
    }

    public function saveToFile(QrCodeData $data, string $filePath): void
    {
        try
        {
            $this->factory->build($data)->saveToFile($filePath);
        }
        catch (\Throwable $e)
        {
            throw new QrCodeException('Failed to save qr code file.', 0, $e);
        }
    }
}
