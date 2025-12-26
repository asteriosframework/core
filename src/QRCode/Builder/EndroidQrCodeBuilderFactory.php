<?php declare(strict_types=1);

namespace Asterios\Core\QRCode\Builder;

use Asterios\Core\Contracts\QrCode\Builder\QCodeBuilderResultInterface;
use Asterios\Core\Contracts\QrCode\Builder\QrCodeBuilderFactoryInterface;
use Asterios\Core\Dto\QrCode\QrCodeData;
use Asterios\Core\Exception\QrCodeException;
use Endroid\QrCode\Builder\Builder;
use Endroid\QrCode\Encoding\Encoding;
use Endroid\QrCode\ErrorCorrectionLevel;
use Endroid\QrCode\Exception\ValidationException;
use Endroid\QrCode\Label\Font\OpenSans;
use Endroid\QrCode\Label\LabelAlignment;
use Endroid\QrCode\RoundBlockSizeMode;
use Endroid\QrCode\Writer\PngWriter;
use Endroid\QrCode\Writer\Result\ResultInterface;

class EndroidQrCodeBuilderFactory implements QrCodeBuilderFactoryInterface
{
    public function build(QrCodeData $data): QCodeBuilderResultInterface
    {
        $builder = new Builder(
            writer: new PngWriter(),
            writerOptions: [],
            validateResult: false,
            data: $data->data,
            encoding: new Encoding('UTF-8'),
            errorCorrectionLevel: ErrorCorrectionLevel::High,
            size: $data->size,
            margin: $data->margin,
            roundBlockSizeMode: RoundBlockSizeMode::Margin,
            labelText: $data->labelText,
            labelFont: new OpenSans($data->labelFontSize),
            labelAlignment: LabelAlignment::Center,
            logoPath: $data->logoPath,
            logoResizeToWidth: $data->logoResizeToWidth,
            logoPunchoutBackground: $data->logoPunchoutBackground
        );

        try
        {
            $result = $builder->build();

            return new readonly class ($result) implements QCodeBuilderResultInterface {
                public function __construct(private ResultInterface $result)
                {
                }

                public function getString(): string
                {
                    return $this->result->getString();
                }

                public function saveToFile(string $path): void
                {
                    $this->result->saveToFile($path);
                }
            };
        }
        catch (ValidationException $e)
        {
            throw new QrCodeException($e->getMessage());
        }
    }
}
