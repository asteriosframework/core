<?php declare(strict_types=1);

namespace Asterios\Core\Dto\Payment\Paypal;

use Asterios\Core\Data;

final class PaypalConfigData extends Data
{
    public function __construct(
        public readonly string $clientId,
        public readonly string $secret,
        public readonly string $baseUrl
    ) {}
}