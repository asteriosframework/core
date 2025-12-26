<?php declare(strict_types=1);

namespace Asterios\Core\Contracts\QrCode\Builder;

use Asterios\Core\Dto\QrCode\QrCodeData;
use Asterios\Core\Exception\QrCodeException;

interface QrCodeBuilderFactoryInterface
{
    /**
     * @param QrCodeData $data
     * @return QCodeBuilderResultInterface
     * @throws QrCodeException
     */
    public function build(QrCodeData $data): QCodeBuilderResultInterface;
}
